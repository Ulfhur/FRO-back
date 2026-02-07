<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Message;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/message', name: 'api_message_')]
final class ApiMessageController extends AbstractController
{
     #[Route('', name: 'list', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
    {
    /** @var \App\Entity\User $user */
    $user = $this->getUser();

    if (!$user) {
        return new JsonResponse(['error' => 'Non autorisé'], 401);
    }

    try {
        
        $repository = $em->getRepository(Message::class);

        $messages = $repository->createQueryBuilder('m')
            ->where('m.sender = :user')
            ->orWhere('m.recipient = :user')
            ->setParameter('user', $user)
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        $data = [];
        foreach ($messages as $message) {
            $data[] = [
                'id' => $message->getId(),
                'title' => $message->getTitle(),
                'content' => $message->getContent(),
                // On renvoie les pseudos pour que le JS puisse comparer
                'senderUsername' => $message->getSender()->getUsername(),
                'recipientUsername' => $message->getRecipient()->getUsername(),
                'createdAt' => $message->getCreatedAt()->format('c'), // Format ISO 8601
            ];
        }

        return new JsonResponse($data);

    } catch (\Exception $e) {
        // En cas d'erreur, on renvoie le message d'erreur pour debugger
        return new JsonResponse(['error' => $e->getMessage()], 500);
    }
}

        // Get a single message by ID //

    #[Route('/{id}', name: 'detail', methods: ['GET'])]
    public function detail(EntityManagerInterface $em, int $id): JsonResponse
    {
        $message = $em->getRepository('App\Entity\Message')->find($id);

        if (!$message) {
            return new JsonResponse(['error' => 'No message for this ID founded'], 404);
        }

        $data = [
            'id' => $message->getId(),
            'title' => $message->getTitle(),
            'content' => $message->getContent(),
            'senderId' => $message->getSender()->getUsername(),
            'recipientId' => $message->getRecipient()->getUsername(),
            'isRead' => $message->isRead(),
            'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s'),
        ];

        return new JsonResponse($data);
    }

        // Create a new message //

   #[Route('', name: 'create', methods: ['POST'])]
    public function create(EntityManagerInterface $em, Request $request): JsonResponse
    {
    
    $sender = $this->getUser(); 

    if (!$sender) {
        return new JsonResponse(['error' => 'Vous devez être connecté'], 401);
    }

    $data = json_decode($request->getContent(), true);

    $title = $data['title'] ?? null;
    $content = $data['content'] ?? null;
    $recipientUsername = $data['recipientUsername'] ?? null;

    if (!$title || !$content || !$recipientUsername) {
        return new JsonResponse(['error' => 'Missing required fields'], 400);
    }

    $recipient = $em->getRepository('App\Entity\User')->findOneBy(['username' => $recipientUsername]);

    if (!$recipient) {
        return new JsonResponse(['error' => 'Destinataire introuvable'], 404);
    }

    // 4. Création du message
    $message = new Message();
    $message->setTitle($title);
    $message->setContent($content);
    $message->setSender($sender); // On utilise l'objet $sender récupéré plus haut
    $message->setRecipient($recipient);
    $message->setIsRead(false);
    $message->setCreatedAt(new \DateTimeImmutable());

    $em->persist($message);
    $em->flush();

    return new JsonResponse(['status' => 'Message created'], 201);
}

        // Update a message by ID // 

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(EntityManagerInterface $em, Request $request, int $id): JsonResponse
    {
        $message = $em->getRepository('App\Entity\Message')->find($id);

        if (!$message) {
            return new JsonResponse(['error' => 'No message for this ID founded'], 404);
        }

        $data = json_decode($request->getContent(), true);

        $message->setTitle($data['title'] ?? $message->getTitle());
        $message->setContent($data['content'] ?? $message->getContent());
        if (isset($data['isRead'])) {
            $message->setIsRead($data['isRead']);
        }

        $em->flush();

        return new JsonResponse(['status' => 'Message updated']);
    }

        // Delete a message by ID //

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $em, int $id): JsonResponse
    {
        $message = $em->getRepository('App\Entity\Message')->find($id);

        if (!$message) {
            return new JsonResponse(['error' => 'No message for this ID founded'], 404);
        }

        $em->remove($message);
        $em->flush();

        return new JsonResponse(['status' => 'Message deleted']);
    }
}
