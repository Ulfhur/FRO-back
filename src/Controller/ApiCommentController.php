<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\CharacterRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

// ApiCommentController.php

#[Route('/api/comment', name: 'api_comment_')]
final class ApiCommentController extends AbstractController
{
    #[Route('/character/{id}', name: 'list_by_character', methods: ['GET'])]
    public function listByCharacter(int $id, CharacterRepository $charRepo, CommentRepository $commentRepo): JsonResponse
    {
        $character = $charRepo->find($id);
        if (!$character) return new JsonResponse(['error' => 'Personnage non trouvé'], 404);

        // On cherche par 'character' et non plus 'article'
        $comments = $commentRepo->findBy(['character' => $character], ['dateComment' => 'DESC']);
        
        $data = array_map(fn($c) => [
            'id' => $c->getId(),
            'content' => $c->getContent(),
            'note' => $c->getNote(),
            'dateComment' => $c->getDateComment()->format('d/m/Y'),
            'authorName' => $c->getAuthor()->getUsername()
        ], $comments);

        return new JsonResponse($data);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(EntityManagerInterface $em, Request $request, CharacterRepository $charRepo): JsonResponse 
    {
        $user = $this->getUser();
        if (!$user) return new JsonResponse(['error' => 'Non connecté'], 401);

        $data = json_decode($request->getContent(), true);
        
        $character = $charRepo->find($data['characterId'] ?? 0);
        if (!$character) return new JsonResponse(['error' => 'Héros introuvable'], 404);

        $comment = new Comment();
        $comment->setContent($data['content']);
        $comment->setNote((int)$data['note']);
        $comment->setDateComment(new \DateTime());
        $comment->setStatus(1);
        $comment->setCharacter($character); // Liaison directe
        $comment->setAuthor($user);

        $em->persist($comment);
        $em->flush();

        return new JsonResponse(['message' => 'Rumeur consignée !'], 201);
    }

    // DELETE http://localhost:8000/api/comment/{id}
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Comment $comment, EntityManagerInterface $em): JsonResponse
    {
        if ($comment->getAuthor() !== $this->getUser()) {
             return new JsonResponse(['error' => 'Action non autorisée'], 403);
        }

        $em->remove($comment);
        $em->flush();

        return new JsonResponse(['message' => 'Rumeur effacée.']);
    }
}
