<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Equipment;
use App\Entity\Character;
use Symfony\Component\HttpFoundation\Request;


#[Route('/api/character', name: 'api_character_')]
final class ApiCharacterController extends AbstractController
{
        // Get a list of all characters //

   #[Route('', name: 'list', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        $characters = $em->getRepository('App\Entity\Character')->findAll();

        $data = [];
        foreach ($characters as $character) {
            $data[] = [
                'id' => $character->getId(),
                'name' => $character->getName(),
                'genre' => $character->getGenre(),
                'skinColor' => $character->getSkinColor(),
                'eyesColor' => $character->getEyesColor(),
                'hairColor' => $character->getHairColor(),
                'face' => $character->getFace(),
                'hair' => $character->getHair(),
                'equipment' => $character->getEquipment()->map(function (Equipment $equip) {
                    return [
                        'id' => $equip->getId(),
                        'name' => $equip->getName(),
                        'type' => $equip->getType(),
                    ];
                })->toArray(),
            ];
        }

        return new JsonResponse($data);
    }

        // Get a single character by ID //

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get(EntityManagerInterface $em, int $id): JsonResponse
    {
        $character = $em->getRepository('App\Entity\Character')->find($id);

        if (!$character) {
            return new JsonResponse(['error' => 'No character for this ID founded'], 404);
        }

        $data = [
            'id' => $character->getId(),
            'name' => $character->getName(),
            'genre' => $character->getGenre(),
            'skinColor' => $character->getSkinColor(),
            'eyesColor' => $character->getEyesColor(),
            'hairColor' => $character->getHairColor(),
            'face' => $character->getFace(),
            'hair' => $character->getHair(),
            'equipment' => $character->getEquipment()->map(function (Equipment $equip) {
                return [
                    'id' => $equip->getId(),
                    'name' => $equip->getName(),
                    'type' => $equip->getType(),
                ];
            })->toArray(),
        ];

        return new JsonResponse($data);
    }

        // Create a new character //

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(EntityManagerInterface $em, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $character = new Character();

        $character->setName($data['name']);
        $character->setGenre($data['genre']);
        $character->setSkinColor($data['skinColor']);
        $character->setEyesColor($data['eyesColor']);
        $character->setHairColor($data['hairColor']);
        $character->setFace($data['face']);
        $character->setHair($data['hair']);

            // Add equipment if provided //

        $invalidEquipmentIds = [];
        if (isset($data['equipmentIds']) && is_array($data['equipmentIds'])) {
            foreach ($data['equipmentIds'] as $equipId) {
                $equipment = $em->getRepository(Equipment::class)->find($equipId);
                if ($equipment) {
                    $character->addEquipment($equipment);
                } else {
                    $invalidEquipmentIds[] = $equipId;
                }
            }
        }

        $em->persist($character);
        $em->flush();

        $response = ['status' => 'Character created successfully'];
        if ($invalidEquipmentIds) {
            $response['invalidEquipmentIds'] = $invalidEquipmentIds;
        }

        return new JsonResponse(['status' => 'Character created successfully'], 201);

    }

        // Update an existing character //

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(EntityManagerInterface $em, Request $request, int $id): JsonResponse
    {
        $character = $em->getRepository(Character::class)->find($id);

        if (!$character) {
            return new JsonResponse(['error' => 'No character for this ID founded'], 404);
        }

        $data = json_decode($request->getContent(), true);

        $character->setName($data['name'] ?? $character->getName());
        $character->setGenre($data['genre'] ?? $character->getGenre());
        $character->setSkinColor($data['skinColor'] ?? $character->getSkinColor());
        $character->setEyesColor($data['eyesColor'] ?? $character->getEyesColor());
        $character->setHairColor($data['hairColor'] ?? $character->getHairColor());
        $character->setFace($data['face'] ?? $character->getFace());
        $character->setHair($data['hair'] ?? $character->getHair());

            // Update equipment if provided //

        $invalidEquipmentIds = []; // Create an array to track invalid equipment IDs //
        if (isset($data['equipmentIds']) && is_array($data['equipmentIds'])) {
            $character->clearEquipment();
            foreach ($data['equipmentIds'] as $equipId) {
                $equipment = $em->getRepository(Equipment::class)->find($equipId);
                if ($equipment) {
                    $character->addEquipment($equipment);
                } else {
                    $invalidEquipmentIds[] = $equipId;
                }
            }
        }

        $em->flush();

        $response = ['status' => 'Character updated successfully'];
        if ($invalidEquipmentIds) {
            $response['invalidEquipmentIds'] = $invalidEquipmentIds;
        }

        return new JsonResponse(['status' => 'Character updated successfully']);
    }

        // Delete a character //

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $em, int $id): JsonResponse
    {
        $character = $em->getRepository(Character::class)->find($id);

        if (!$character) {
            return new JsonResponse(['error' => 'No character for this ID founded'], 404);
        }

        $em->remove($character);
        $em->flush();

        return new JsonResponse(['status' => 'Character deleted successfully']);

    }
}
