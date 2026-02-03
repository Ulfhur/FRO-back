<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/image', name: 'api_image_')]
final class ApiImageController extends AbstractController
{
        // Get a list of all images //

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
    {

        $images = $em->getRepository('App\Entity\Image')->findAll();

        $data = [];
        foreach ($images as $image) {
            $data[] = [
                'id' => $image->getId(),
                'name' => $image->getName(),
                'source' => $image->getSource(),
                'size' => $image->getSize(),
                'extension' => $image->getExtension(),
                'type' => $image->getType(),
                'width' => $image->getWidth(),
                'height' => $image->getHeight(),
                'createdAt' => $image->getCreatedAt()?->format('Y-m-d H:i:s'),
                'updatedAt' => $image->getUpdatedAt()?->format('Y-m-d H:i:s'),
            ];
        }

        return new JsonResponse($data);
    }

        // Get details of a specific image by ID //

    #[Route('/{id}', name: 'detail', methods: ['GET'])]
    public function detail(int $id, EntityManagerInterface $em): JsonResponse
    {
        $image = $em->getRepository('App\Entity\Image')->find($id);

        if (!$image) {
            return new JsonResponse(['error' => 'Image not found'], 404);
        }

        $data = [
            'id' => $image->getId(),
            'name' => $image->getName(),
            'source' => $image->getSource(),
            'size' => $image->getSize(),
            'extension' => $image->getExtension(),
            'type' => $image->getType(),
            'width' => $image->getWidth(),
            'height' => $image->getHeight(),
            'createdAt' => $image->getCreatedAt()?->format('Y-m-d H:i:s'),
            'updatedAt' => $image->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ];

        return new JsonResponse($data);
    }

        // Create a new image //
        
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(EntityManagerInterface $em, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $image = new \App\Entity\Image();
        $image->setName($data['name'] ?? '');
        $image->setSource($data['source'] ?? '');
        $image->setSize($data['size'] ?? 0);
        $image->setExtension($data['extension'] ?? '');
        $image->setType($data['type'] ?? '');
        $image->setWidth($data['width'] ?? 0);
        $image->setHeight($data['height'] ?? 0);
        $image->setCreatedAt(new \DateTimeImmutable());

        $em->persist($image);
        $em->flush();

        return new JsonResponse(['message' => 'Image created', 'id' => $image->getId()], 201);
    }

        // Update an existing image //

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, EntityManagerInterface $em, Request $request): JsonResponse
    {
        $image = $em->getRepository('App\Entity\Image')->find($id);

        if (!$image) {
            return new JsonResponse(['error' => 'Image not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        $image->setName($data['name'] ?? $image->getName());
        $image->setSource($data['source'] ?? $image->getSource());
        $image->setSize($data['size'] ?? $image->getSize());
        $image->setExtension($data['extension'] ?? $image->getExtension());
        $image->setType($data['type'] ?? $image->getType());
        $image->setWidth($data['width'] ?? $image->getWidth());
        $image->setHeight($data['height'] ?? $image->getHeight());
        $image->setUpdatedAt(new \DateTimeImmutable());

        $em->flush();

        return new JsonResponse(['message' => 'Image updated']);
    }

        // Delete an image //

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $em): JsonResponse
    {
        $image = $em->getRepository('App\Entity\Image')->find($id);

        if (!$image) {
            return new JsonResponse(['error' => 'Image not found'], 404);
        }

        $em->remove($image);
        $em->flush();

        return new JsonResponse(['message' => 'Image deleted']);
    }
}
        
