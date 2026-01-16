<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Article;

#[Route('/api/article', name: 'api_article_')]
final class ApiArticleController extends AbstractController
{
    // Get a list of all articles //

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        $articles = $em->getRepository('App\Entity\Article')->findAll();

        $data = [];
        foreach ($articles as $article) {
            $data[] = [
                'name' => $article->getName(),
                'type' => $article->getType(),
                'author' => $article->getAuthor()->getPseudo(),
                'createdAt' => $article->getCreatedAt()->format('Y-m-d H:i:s'),
                'active' => $article->isActive(),
                'characterRel' => $article->getCharacterRel()->getName(),
            ];
        }

        return new JsonResponse($data);
    }

        // Get a single article by ID //
    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get(EntityManagerInterface $em, int $id): JsonResponse
    {
        $article = $em->getRepository(Article::class)->find($id);

        if (!$article) {
            return new JsonResponse(['error' => 'No article for this ID founded'], 404);
        }

        $data = [
            'name' => $article->getName(),
            'type' => $article->getType(),
            'author' => $article->getAuthor()->getPseudo(),
            'createdAt' => $article->getCreatedAt()->format('Y-m-d H:i:s'),
            'active' => $article->isActive(),
            'characterRel' => $article->getCharacterRel()->getName(),
        ];

        return new JsonResponse($data);
    }

        // Create a new article //
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(EntityManagerInterface $em, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $article = new Article();
        $article->setName($data['name']);
        $article->setType($data['type']);
        $article->setActive($data['active']);
        $article->setAuthor($em->getRepository('App\Entity\User')->find($data['authorId']));
        $article->setCharacterRel($em->getRepository('App\Entity\Character')->find($data['characterRelId']));
        $article->setCreatedAt(new \DateTimeImmutable());

        $em->persist($article);
        $em->flush();

        return new JsonResponse(['status' => 'Article created'], 201);
    }

        // Update an existing article //
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(EntityManagerInterface $em, Request $request, int $id): JsonResponse
    {
        $article = $em->getRepository(Article::class)->find($id);

        if (!$article) {
            return new JsonResponse(['error' => 'No article for this ID founded'], 404);
        }

        $data = json_decode($request->getContent(), true);

        $article->setName($data['name'] ?? $article->getName());
        $article->setType($data['type'] ?? $article->getType());
        $article->setActive($data['active'] ?? $article->isActive());
        if (isset($data['authorId'])) {
            $article->setAuthor($em->getRepository('App\Entity\User')->find($data['authorId']));
        }
        if (isset($data['characterRelId'])) {
            $article->setCharacterRel($em->getRepository('App\Entity\Character')->find($data['characterRelId']));
        }
        $article->setUpdatedAt(new \DateTimeImmutable());

        $em->flush();

        return new JsonResponse(['status' => 'Article updated']);
    }

        // Delete an article //
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $em, int $id): JsonResponse
    {
        $article = $em->getRepository(Article::class)->find($id);

        if (!$article) {
            return new JsonResponse(['error' => 'No article for this ID founded'], 404);
        }

        $em->remove($article);
        $em->flush();

        return new JsonResponse(['status' => 'Article deleted']);
    }
}