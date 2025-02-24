<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Repository\UserRepository;
use App\Validator\UserValidator;

#[Route('/api', name: 'api_')]
class AuthController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private JWTTokenManagerInterface $jwtManager,
        private UserValidator $userValidator
    ) {}

    /* User login */
    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {

        $data = $this->userValidator->validateLoginParams($request->getContent());

        // Find user by email
        $user = $this->userRepository->findOneBy(['email' => $data['email']]);

        // Verify password
        if (
            !$user ||
            !isset($data['password']) ||
            !is_string($data['password']) ||
            !$this->passwordHasher->isPasswordValid($user, $data['password'])
        ) {
            return $this->json(['error' => 'Invalid credentials'], 401);
        }

        // Generate JWT token
        $token = $this->jwtManager->create($user);

        // Return token
        return $this->json(['token' => $token]);
    }
}

// Delegate code to service if grows in future

