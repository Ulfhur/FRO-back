<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Equipment;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/equipment', name: 'api_equipment_')]
final class ApiEquipmentController extends AbstractController
{
        // Get a list of all equipment //

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        $equipment = $em->getRepository('App\Entity\Equipment')->findAll();

        $data = [];
        foreach ($equipment as $item) {
            $data[] = [
                'id' => $item->getId(),
                'name' => $item->getName(),
                'type' => $item->getType(),
                'slot' => $item->getSlot(),
                'zIndex' => $item->getZIndex(),
                'image' => $item->getImage() ? [
                    'id' => $item->getImage()->getId(),
                    'name' => $item->getImage()->getName(),
                    'source' => $item->getImage()->getSource(),
                ] : null,
            ];
        }

        return new JsonResponse($data);
    }

        // Get details of a specific equipment by ID //

    #[Route('/{id}', name: 'detail', methods: ['GET'])]
    public function detail(int $id, EntityManagerInterface $em): JsonResponse
    {
        $item = $em->getRepository('App\Entity\Equipment')->find($id);

        if (!$item) {
            return new JsonResponse(['error' => 'Equipment not found'], 404);
        }

        $data = [
            'id' => $item->getId(),
            'name' => $item->getName(),
            'type' => $item->getType(),
            'slot' => $item->getSlot(),
            'zIndex' => $item->getZIndex(),
            'image' => $item->getImage() ? [
                'id' => $item->getImage()->getId(),
                'name' => $item->getImage()->getName(),
                'source' => $item->getImage()->getSource(),
            ] : null,
        ];

        return new JsonResponse($data);
    }
    
        // Create a new equipment //

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(EntityManagerInterface $em, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $equipment = new Equipment();
        
        $equipment->setName($data['name']);
        $equipment->setType($data['type']);
        $equipment->setSlot($data['slot']);
        $equipment->setZIndex($data['zIndex']);

            // check if imageId is provided and valid //

        $invalidImageId = [];
        if (isset($data['imageId']) && is_array($data['imageId'])) {
            foreach ($data['imageId'] as $imageId) {
                $image = $em->getRepository('App\Entity\Image')->find($imageId);
                if ($image) {
                    $equipment->setImage($image);
                } else {
                    $invalidImageId[] = $imageId;
                }
            }
        }
        
        $em->persist($equipment);
        $em->flush();

        $response = ['status' => 'Equipment created', 201];
        if ($invalidImageId) {
            $response['invalidImageId'] = $invalidImageId;
        }

        return new JsonResponse(['status' => 'Equipment created'], 201);
    }

        // Update an existing equipment //

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, EntityManagerInterface $em, Request $request): JsonResponse
    {
        $equipment = $em->getRepository('App\Entity\Equipment')->find($id);

        if (!$equipment) {
            return new JsonResponse(['error' => 'Equipment not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        $equipment->setName($data['name'] ?? $equipment->getName());
        $equipment->setType($data['type'] ?? $equipment->getType());
        $equipment->setSlot($data['slot'] ?? $equipment->getSlot());
        $equipment->setZIndex($data['zIndex'] ?? $equipment->getZIndex());

            // check if imageId is provided and valid //

        $invalidImageId = [];
        if (isset($data['imageId']) && is_array($data['imageId'])) {
            foreach ($data['imageId'] as $imageId) {
                $image = $em->getRepository('App\Entity\Image')->find($imageId);
                if ($image) {
                    $equipment->setImage($image);
                } else {
                    $invalidImageId[] = $imageId;
                }
            }
        }

        $em->flush();

        $response = ['message' => 'Equipment updated'];
        if ($invalidImageId) {
            $response['invalidImageId'] = $invalidImageId;
        }

        return new JsonResponse(['status' => 'Equipment updated']);
    }

            // Delete an equipment //

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $em): JsonResponse
    {
        $equipment = $em->getRepository('App\Entity\Equipment')->find($id);

        if (!$equipment) {
            return new JsonResponse(['error' => 'Equipment not found'], 404);
        }

        $em->remove($equipment);
        $em->flush();

        return new JsonResponse(['status' => 'Equipment deleted']);
    }
}

