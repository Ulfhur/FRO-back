<?php

namespace App\Controller;

use Dom\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Character;
use App\Entity\Equipment;
use App\Entity\Comment;
use App\Repository\CommentRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]

#[Route('/api/admin', name: 'api_admin_')]
final class ApiAdminController extends AbstractController
{
    // Admin endpoint to GET all Characters //
    #[Route('/characters', name: 'characters', methods: ['GET'])]
    public function getAllCharacters(EntityManagerInterface $em): JsonResponse
    {
        $characters = $em->getRepository(Character::class)->findAll();

        $data = [];
        foreach ($characters as $character) {
            $data[] = [
                'id' => $character->getId(),
                'name' => $character->getName(),
                'equipment' => $character->getEquipment()->map(function (Equipment $equip) {
                return [
                    'id' => $equip->getId(),
                    'name' => $equip->getName(),
                    'type' => $equip->getType(),
                    ];
                })->toArray(),
                    'user' => $character->getUser() ? $character->getUser()->getUsername() : 'Anonyme',
                    'isShared' => $character->isShared(),
            ];
        }

        return new JsonResponse($data);
    }

    // Admin endpoint to GET a specific character //
    #[Route('/characters/{id}', name: 'character', methods: ['GET'])]
    public function getCharacterById(EntityManagerInterface $em, int $id): JsonResponse
    {
        $character = $em->getRepository(Character::class)->find($id);

        if (!$character) {
            return new JsonResponse(['error' => 'Character not found'], 404);
        }

        $data = [
            'id' => $character->getId(),
            'name' => $character->getName(),
            'equipment' => $character->getEquipment()->map(function (Equipment $equip) {
                return [
                    'id' => $equip->getId(),
                    'name' => $equip->getName(),
                    'type' => $equip->getType(),
                ];
            })->toArray(),
            'user' => $character->getUser() ? $character->getUser()->getUsername() : 'Anonyme',
            'isShared' => $character->isShared(),
        ];

        return new JsonResponse($data);
    }

    // Admin endpoint to DELETE any character //
    #[Route('/characters/{id}', name: 'delete_character', methods: ['DELETE'])]
    public function adminDeleteCharacter(EntityManagerInterface $em, int $id): JsonResponse
    {
        $character = $em->getRepository(Character::class)->find($id);

        if (!$character) {
            return new JsonResponse(['error' => 'Character not found'], 404);
        }

        $em->remove($character);
        $em->flush();

        return new JsonResponse(['message' => 'Character deleted by administrator']);
    }

    // Admin endpoint to GET all comments //
    #[Route('/comments', name: 'comments_list', methods: ['GET'])]
    public function getAllComments(CommentRepository $commentRepo): JsonResponse
    {
        $comments = $commentRepo->findAll();

        $data = array_map(function($comment) {
            return [
                'id' => $comment->getId(),
                'content' => $comment->getContent(),
                'author' => $comment->getAuthor() ? $comment->getAuthor()->getUsername() : 'Anonyme',
                'characterName' => $comment->getCharacter() ? $comment->getCharacter()->getName() : 'Héros supprimé',
                'createdAt' => $comment->getDateComment()->format('d/m/Y'),
            ];
        }, $comments);

        return new JsonResponse($data);
    }

    // Admin endpoint to DELETE a comment //
    #[Route('/comment/{id}', name: 'delete_comment', methods: ['DELETE'])]
    public function deleteComment(EntityManagerInterface $em, int $id): JsonResponse
    {
        $comment = $em->getRepository(Comment::class)->find($id);
        if (!$comment) return new JsonResponse(['error' => 'Comment not found'], 404);

        $em->remove($comment);
        $em->flush();

        return new JsonResponse(['message' => 'Comment deleted']);
    }
                
    
}

