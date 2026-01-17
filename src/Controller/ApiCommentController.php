<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Comment;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/comment', name: 'api_comment_')]
final class ApiCommentController extends AbstractController
{
        // Get a list of all comments //

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        $comments = $em->getRepository('App\Entity\Comment')->findAll();

        $data = [];
        foreach ($comments as $comment) {
            $data[] = [
                'id' => $comment->getId(),
                'note' => $comment->getNote(),
                'content' => $comment->getContent(),
                'dateComment' => $comment->getDateComment()->format('Y-m-d'),
                'status' => $comment->getStatus(),
                'articleId' => $comment->getArticle()->getId(),
                'authorId' => $comment->getAuthor()->getId(),
            ];
        }

        return new JsonResponse($data);
    }

        // Get a single comment by ID //

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get(EntityManagerInterface $em, int $id): JsonResponse
    {
        $comment = $em->getRepository('App\Entity\Comment')->find($id);

        if (!$comment) {
            return new JsonResponse(['error' => 'No comment for this ID founded'], 404);
        }

        $data = [
            'id' => $comment->getId(),
            'note' => $comment->getNote(),
            'content' => $comment->getContent(),
            'dateComment' => $comment->getDateComment()->format('Y-m-d'),
            'status' => $comment->getStatus(),
            'articleId' => $comment->getArticle()->getId(),
            'authorId' => $comment->getAuthor()->getId(),
        ];

        return new JsonResponse($data);
    }

        // Create a new comment //

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(EntityManagerInterface $em, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $comment = new Comment();
        $comment->setNote($data['note']);
        $comment->setContent($data['content']);
        $comment->setDateComment(new \DateTime($data['dateComment']));
        $comment->setStatus($data['status']);

        $article = $em->getRepository('App\Entity\Article')->find($data['articleId']);
        $author = $em->getRepository('App\Entity\User')->find($data['authorId']);

        if (!$article || !$author) {
            return new JsonResponse(['error' => 'Invalid article or author ID'], 400);
        }

        $comment->setArticle($article);
        $comment->setAuthor($author);

        $em->persist($comment);
        $em->flush();

        return new JsonResponse(['message' => 'Comment created successfully', 'id' => $comment->getId()], 201);
    }

        // Update an existing comment //

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(EntityManagerInterface $em, Request $request, int $id): JsonResponse
    {
        $comment = $em->getRepository('App\Entity\Comment')->find($id); 

        if (!$comment) {
            return new JsonResponse(['error' => 'No comment for this ID founded'], 404);
        }

        $data = json_decode($request->getContent(), true);

        $comment->setNote($data['note'] ?? $comment->getNote());
        $comment->setContent($data['content'] ?? $comment->getContent());

        if(isset($data['status'])) {
            $comment->setStatus($data['status']);
        }

        $em->flush();

        return new JsonResponse(['message' => 'Comment updated successfully']);
    }

        // Delete a comment //

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $em, int $id): JsonResponse
    {
        $comment = $em->getRepository('App\Entity\Comment')->find($id);

        if (!$comment) {
            return new JsonResponse(['error' => 'No comment for this ID founded'], 404);
        }

        $em->remove($comment);
        $em->flush();

        return new JsonResponse(['message' => 'Comment deleted successfully']);
    }
}

    
