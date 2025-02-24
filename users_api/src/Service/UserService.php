<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Exception\NotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private AuditService $auditService
    ) {}

    /* ------------------------
    Endpoint handlers
    ------------------------ */

    /* Get User object or formatted response */
    public function showUser(array $params): User|array
    {
        $repo = $this->em->getRepository(User::class);

        if (isset($params['id'])) {
            $user = $repo->find($params['id']);
        } elseif (isset($params['email'])) {
            $user = $repo->findOneBy(['email' => $params['email']]);
        } else {
            throw new NotFoundException('User not found.');
        }

        return $this->formatUser($user);
    }

    /* List users with optional filters */
    public function listUsers(array $filters): array
    {
        $repo = $this->em->getRepository(User::class);
        $qb = $repo->createQueryBuilder('u');

        if ($filters['role']) {
            $qb->andWhere('u.role = :role')
                ->setParameter('role', $filters['role']);
        }

        if ($filters['createdAfter']) {
            $qb->andWhere('u.createdAt > :after')
                ->setParameter('after', $filters['createdAfter']);
        }

        if ($filters['createdBefore']) {
            $qb->andWhere('u.createdAt < :before')
                ->setParameter('before', $filters['createdBefore']);
        }

        if ($filters['onlyDeleted']) {
            $qb->andWhere('u.deletedAt IS NOT NULL');
        } elseif (!$filters['includeDeleted']) {
            $qb->andWhere('u.deletedAt IS NULL'); // Include only active users by default
        }

        // Return an array of user entities and format each user by short arrow function
        return array_map(fn($user) => $this->formatUser($user), $qb->getQuery()->getResult());
    }

    public function createUser(array $data, ?User $performedBy): array
    {
        $user = new User();

        $user
            ->setName($data['name'])
            ->setEmail($data['email'])
            ->setRole($data['role']);

        // Hash password
        $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        $this->em->persist($user);
        $this->em->flush();

        // Log creation
        $this->auditService->log('create', $performedBy, $user->getId(), null, [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role']
        ]);

        return $this->formatUser($user);
    }

    public function updateUser(array $updates, int $id, User $performedBy): array
    {
        $user = $this->em->getRepository(User::class)->find($id)
            ?? throw new NotFoundException("User not found.");

        $oldData = $this->formatUser($user);

        foreach ($updates as $field => $value) {

            match ($field) {
                'password' => $user->setPassword($this->passwordHasher->hashPassword($user, $value)),
                'name'     => $user->setName($value),
                'email'    => $user->setEmail($value),
                'role'     => $user->setRole($value),
            };
        }

        $this->em->flush();

        // Log changes
        $this->auditService->log('update', $performedBy, $user->getId(), $oldData, $updates);

        return $this->formatUser($user);
    }


    /* Delete user (soft or hard) */
    public function deleteUser(int $id, bool $hardDelete, User $performedBy): void
    {
        $user = $this->em->getRepository(User::class)->find($id)
            ?? throw new NotFoundException("User not found.");

        $oldData = $this->formatUser($user);
        $action = $hardDelete ? 'hard_delete' : 'soft_delete';

        if ($hardDelete) {
            $this->em->remove($user);
        } else {
            if ($user->getDeletedAt() === null) {
                $user->setDeletedAt(new \DateTimeImmutable());
            } else {
                throw new \RuntimeException("User is already soft-deleted.");
            }
        }

        $this->em->flush();

        // Log
        $this->auditService->log($action, $performedBy, $id, $oldData, null);
    }

    /* ------------------------
    Helper functions
    ------------------------ */

    /** Format user object to array */
    public function formatUser(User|UserInterface $user): array
    {
        return [
            'id'         => $user->getId(),
            'name'       => $user->getName(),
            'email'      => $user->getEmail(),
            'role'       => $user->getRole()?->value, // For enum
            'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $user->getUpdatedAt()?->format('Y-m-d H:i:s'),
            'deleted_at' => $user->getDeletedAt()?->format('Y-m-d H:i:s'),
        ];
    }
}
