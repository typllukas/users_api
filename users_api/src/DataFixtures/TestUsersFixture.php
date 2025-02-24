<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Enum\UserRole;

class TestUsersFixture extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager)
    {
        // Test user 1
        $user1 = new User();
        $user1->setEmail('testuser1@example.com');
        $user1->setName('Test User 1');
        $user1->setRole(UserRole::USER); 
        $user1->setPassword($this->passwordHasher->hashPassword($user1, 'testUser1*'));

        // Test admin user 1
        $user2 = new User();
        $user2->setEmail('testadmin1@example.com');
        $user2->setName('Test Admin 1');
        $user2->setRole(UserRole::ADMIN); 
        $user2->setPassword($this->passwordHasher->hashPassword($user2, 'testAdmin1*'));

        $manager->persist($user1);
        $manager->persist($user2);
        $manager->flush();
    }
}
