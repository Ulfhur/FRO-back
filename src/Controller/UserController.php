<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\SecurityBundle\Security;


#[Route('/api/user', name: 'api_user_')]
final class UserController extends AbstractController
{  

    // Get info of a connected user (for Profile page) //

    #[Route('/me', name: 'me', methods: ['GET'])]
    public function me(Security $security): JsonResponse
    {
        /** @var User $user */
        $user = $security->getUser();

        if (!$user instanceof \App\Entity\User) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé'], 401);
        }

        return new JsonResponse([

            'username' => $user->getUsername(),
            'mail'     => $user->getMail(),
        ]);
    }

        // Get a list of all users //
    
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(EntityManagerInterface $em) : JsonResponse
    {
        $users = $em->getRepository(User::class)->findAll();
        
        $data = [];
        foreach ($users as $user) {
            $data[] = [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'mail' => $user->getMail(),
                'suspended' => $user->isSuspended(),
                'changePassword' => $user->isChangePassword(),
            ];
        }

        return new JsonResponse($data);
    }

        // Get a single user by ID //

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function detail(EntityManagerInterface $em, int $id) : JsonResponse
    {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $data = [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'mail' => $user->getMail(),
            'suspended' => $user->getSuspended(),
            'changePassword' => $user->getChangePassword(),
        ];
    
        return new JsonResponse($data);
    }

        // Create a new user //

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        EntityManagerInterface $em,
        Request $request,
        UserPasswordHasherInterface $passwordHasher
        ): JsonResponse {
    
        $data = json_decode($request->getContent(), true);

        if (!isset($data['username'], $data['mail'], $data['password'])) {
            return new JsonResponse(['error' => 'Missing required fields'], 400);
        }

        $user = new User();

        $user->setUsername($data['username']);
        $user->setMail($data['mail']);
        $user->setSuspended($data['suspended'] ?? false);
        $user->setChangePassword($data['changePassword'] ?? false);

        $hashPassword = $passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashPassword);

        $em->persist($user);
        $em->flush();

        return new JsonResponse(['message' => 'User created successfully', 'id' => $user->getId()], 201);
    }

        // Update an existing user //

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(
        EntityManagerInterface $em,
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        int $id
        ): JsonResponse {

        $user = $em->getRepository(User::class)->find($id);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (isset($data['username'])) {
            $user->setUsername($data['username']);
        }
        if (isset($data['mail'])) {
            $user->setMail($data['mail']);
        }
        if (isset($data['password'])) {
            $hashPassword = $passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashPassword);
            $user->setChangePassword(false);
        }
        if (isset($data['suspended'])) {
            $user->setSuspended($data['suspended']);
        }
        if (isset($data['changePassword'])) {
            $user->setChangePassword($data['changePassword']);
        }

        $em->flush();

        return new JsonResponse(['message' => 'User updated successfully']);
    }

        // Delete a user //

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $em, int $id) : JsonResponse
    {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $em->remove($user);
        $em->flush();

        return new JsonResponse(['message' => 'User deleted successfully']);
    }
}

    