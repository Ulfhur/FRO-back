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
        // List of all messages //

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        $messages = $em->getRepository('App\Entity\Message')->findAll();

        $data = [];
        foreach ($messages as $message) {
            $data[] = [
                'id' => $message->getId(),
                'title' => $message->getTitle(),
                'content' => $message->getContent(),
                'senderId' => $message->getSender()->getId(),
                'recipientId' => $message->getRecipient()->getId(),
                'isRead' => $message->isRead(),
                'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }

        return new JsonResponse($data);
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
            'senderId' => $message->getSender()->getId(),
            'recipientId' => $message->getRecipient()->getId(),
            'isRead' => $message->isRead(),
            'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s'),
        ];

        return new JsonResponse($data);
    }

        // Create a new message //

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(EntityManagerInterface $em, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $title = $data['title'] ?? null;
        $content = $data['content'] ?? null;
        $senderId = $data['senderId'] ?? null;
        $recipientId = $data['recipientId'] ?? null;

        if (!$title || !$content || !$senderId || !$recipientId) {
            return new JsonResponse(['error' => 'Missing required fields'], 400);
        }

        $sender = $em->getRepository('App\Entity\User')->find($senderId);
        $recipient = $em->getRepository('App\Entity\User')->find($recipientId);

        if (!$sender || !$recipient) {
            return new JsonResponse(['error' => 'Invalid sender or recipient ID'], 400);
        }

        $message = new Message();
        $message->setTitle($title);
        $message->setContent($content);
        $message->setSender($sender);
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
