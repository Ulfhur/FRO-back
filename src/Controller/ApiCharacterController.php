<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Equipment;
use App\Repository\EquipmentRepository;
use App\Entity\Character;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\SecurityBundle\Security;
use App\Repository\CommentRepository;


#[Route('/api/character', name: 'api_character_')]
final class ApiCharacterController extends AbstractController
{

    // Get characters created from the community //

    #[Route('/community', name: 'api_characters_community', methods: ['GET'])]
    public function getCommunityCharacters(EntityManagerInterface $em, Security $security, \App\Repository\CommentRepository $commentRepo): JsonResponse
    {
        try {
            $currentUser = $security->getUser();
            $allCharacters = $em->getRepository(Character::class)->findAll();
            
            $data = [];
            foreach ($allCharacters as $char) {
                $owner = $char->getUser();

                if (!$owner || ($currentUser && $owner->getId() === $currentUser->getId())) {
                    continue;
                }

                $equipmentImages = [];
                foreach ($char->getEquipment() as $item) {
                    if (method_exists($item, 'getImage')) {
                        $equipmentImages[] = $item->getImage();
                    }
                }

                // REMPLACEMENT ICI : On utilise le repo pour compter les commentaires
                $commentCount = $commentRepo->count(['character' => $char]);

                $data[] = [
                    'id' => $char->getId(),
                    'name' => $char->getName(),
                    'owner' => $owner->getUsername(),
                    'equipments' => $equipmentImages,
                    'commentCount' => $commentCount
                ];
            }
            return new JsonResponse($data);

        } catch (\Throwable $e) {
            // Cela te permettra de voir l'erreur exacte dans l'onglet "Response" du navigateur
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
        // Get a list of all characters //

   #[Route('', name: 'list', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
{
    $user = $this->getUser();

    if (!$user) {
        return new JsonResponse(['error' => 'Non authentifié'], 401);
    }

    $characters = $em->getRepository(Character::class)->findBy(['user' => $user]);

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

    #[Route('', name: 'api_character_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, EquipmentRepository $equipRepo): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        $data = json_decode($request->getContent(), true);

        $character = new Character();
        $character->setName($data['name']);
        $character->setGenre($data['genre'] ?? 'male');
        $character->setSkinColor($data['skinColor'] ?? 'pale');
        $character->setEyesColor($data['eyesColor'] ?? 'black');
        $character->setHairColor($data['hairColor'] ?? 'black');
        $character->setFace($data['face'] ?? 'standard');
        $character->setHair($data['hair'] ?? 'short');

        $character->setUser($user);

        if (isset($data['equipmentIds']) && is_array($data['equipmentIds'])) {
            foreach ($data['equipmentIds'] as $id) {
                $item = $equipRepo->find($id);
                if ($item) {
                    $character->addEquipment($item);
                }
            }
        }

        $em->persist($character);
        $em->flush();

        return $this->json([
            'message' => 'Personnage créé avec succès !',
            'id' => $character->getId()
        ], 201);
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

        $invalidEquipmentIds = [];
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

        // Function to get all character of a single User //

    #[Route('/list', name: 'listUser', methods: ['GET'])]
    public function listUser(EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
       
        $characters = $em->getRepository(Character::class)->findBy(['user' => $user]);

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
}
