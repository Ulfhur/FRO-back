<?php

namespace App\Controller;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

final class ApiRegisterController extends AbstractController
{
            // Registration endpoint  //

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {

        $data = json_decode($request->getContent(), true);

        $username = $data['username'] ?? null;
        $mail = $data['mail'] ?? null;
        $plainPassword = $data['password'] ?? null;
        $suspended = $data['suspended'] ?? false;
        $changePassword = $data['changePassword'] ?? false;

        if (!$username || !$mail || !$plainPassword) {
            return new JsonResponse(['error' => 'Missing required fields'], 400);
        }

            // Check if user with the same mail already exists //

        $existingUser = $em->getRepository(User::class)->findOneBy(['mail' => $mail]);
        if ($existingUser) {
            return new JsonResponse(['error' => 'Email already in use'], 400);
        }

        $user = new User();

        $user->setUsername($username);
        $user->setMail($mail);

        $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $user->setSuspended($suspended);
        $user->setChangePassword($changePassword);

            // Give default role //
        $user->setRoles(['ROLE_USER']);

        $em->persist($user);
        $em->flush();

            // Generate JWT token for the newly registered user //

        $token = $jwtManager->create($user);

        return $this->json([
            'message' => 'Utilisateur créé avec succès',
            'token' => $token,
            'user' => [
                'id' => $user->getId(),
                'pseudo' => $user->getUsername(),
                'email' => $user->getMail(),
                'roles' => $user->getRoles(),
            ]
        ], Response::HTTP_CREATED);
    }
}
