<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Task;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TaskController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/projects/{id}/tasks', name: 'list_tasks', methods: ['GET'])]
    public function list(int $id): JsonResponse
    {
        $tasks = $this->entityManager
            ->getRepository(Task::class)
            ->findBy(['deleted_at' => null, 'project_id' => $id]);

        return $this->json($tasks, 200);
    }

    #[Route('/tasks/{id}', name: 'show_task', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $task =
            $this->entityManager->getRepository(Task::class)->findBy([
                'deleted_at' => null,
                'id' => $id,
            ])[0] ?? null;

        if (!$task) {
            return new JsonResponse(['message' => 'Task not found'], 404);
        }

        return $this->json(
            [
                'id' => $task->getId(),
                'name' => $task->getName(),
                'project_id' => $task->getProject()->getId(),
            ],
            200
        );
    }

    #[Route('/projects/{id}/tasks', name: 'create_task', methods: ['POST'])]
    public function create(
        int $id,
        Request $request,
        ValidatorInterface $validator
    ): JsonResponse {
        $project =
            $this->entityManager->getRepository(Project::class)->findBy([
                'id' => $id,
                'deleted_at' => null,
            ])[0] ?? null;

        if (!$project) {
            return new JsonResponse(
                [
                    'code' => 404,
                    'message' => 'Project not found',
                ],
                200
            );
        }

        $data = json_decode($request->getContent(), true);

        $constraints = new Assert\Collection([
            'name' => new Assert\NotBlank(),
            'guid' => [new Assert\NotBlank(), new Assert\Uuid()],
        ]);

        $errors = $validator->validate($data, $constraints);

        if (count($errors) > 0) {
            return new JsonResponse(
                ['errors' => (string) $errors, 'code' => 400],
                200
            );
        }

        $task = new Task();
        $task->setName($data['name']);
        $task->setProject($project);
        $task->setGuid($data['guid']);

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return $this->json(
            [
                'message' => 'Task created successfully',
            ],
            201
        );
    }

    #[Route('/tasks/{id}', name: 'update', methods: ['PATCH'])]
    public function update(
        int $id,
        Request $request,
        ValidatorInterface $validator,
        SerializerInterface $serializer
    ): JsonResponse {
        $task =
            $this->entityManager->getRepository(Task::class)->findBy([
                'id' => $id,
                'deleted_at' => null,
            ])[0] ?? null;

        if (!$task) {
            return new JsonResponse(['message' => 'Task not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        $constraints = new Assert\Collection([
            'name' => new Assert\Optional(new Assert\NotBlank()),
            'guid' => new Assert\Optional([
                new Assert\NotBlank(),
                new Assert\Uuid(),
            ]),
        ]);

        $violations = $validator->validate($data, $constraints);

        if (count($violations) > 0) {
            return new JsonResponse(['errors' => (string) $violations], 400);
        }

        $task->setName($data['name']);
        $task->setGuid($data['guid']);

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return $this->json($task, 200, [], ['groups' => ['task:read']]);
    }

    #[Route('/tasks/{id}', name: 'update', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $task = $this->entityManager->getRepository(Task::class)->find($id);

        if (!$task || $task->getDeletedAt() !== null) {
            return new JsonResponse(['message' => 'Task not found'], 404);
        }

        $task->setDeletedAt(new DateTimeImmutable());
        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Task deleted successfully']);
    }
}
