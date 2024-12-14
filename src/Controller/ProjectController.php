<?php

namespace App\Controller;

use App\Actions\StoreProjectAction;
use App\Actions\UpdateProjectAction;
use App\Entity\Project;
use App\Exceptions\ValidationException;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProjectController extends AbstractController
{
    #[Route('/projects', name: 'create_project', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        try {
            (new StoreProjectAction(
                $entityManager,
                $validator,
                $data
            ))->execute();
        } catch (ValidationException $exception) {
            return new JsonResponse(
                ['code' => 400, 'message' => $exception->getMessage()],
                200
            );
        } catch (Exception $exception) {
            return new JsonResponse(
                ['code' => 400, 'message' => 'Something went wrong'],
                200
            );
        }

        return new JsonResponse(
            ['code' => 200, 'message' => 'Project created successfully'],
            200
        );
    }

    #[Route('/projects/{id}', name: 'delete_project', methods: ['DELETE'])]
    public function delete(
        int $id,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $project = $entityManager->getRepository(Project::class)->find($id);

        if (!$project) {
            return new JsonResponse(
                [
                    'code' => 404,
                    'message' => 'Project not found',
                ],
                200
            );
        }

        $project->setDeletedAt(new DateTimeImmutable());

        $entityManager->persist($project);
        $entityManager->flush();

        return new JsonResponse(
            [
                'code' => 200,
                'message' => 'Project deleted successfully',
            ],
            200
        );
    }

    #[Route('/projects', name: 'list_projects', methods: ['GET'])]
    public function list(EntityManagerInterface $entityManager): JsonResponse
    {
        $projects = $entityManager
            ->getRepository(Project::class)
            ->findActiveProjects();

        $projectData = array_map(function (Project $project) {
            return [
                'id' => $project->getId(),
                'guid' => $project->getGuid(),
                'title' => $project->getTitle(),
                'description' => $project->getDescription(),
                'status' => $project->getStatus(),
                'duration' => $project->getDuration(),
                'client' => $project->getClient(),
                'company' => $project->getCompany(),
                'deleted_at' => $project->getDeletedAt(),
            ];
        }, $projects);

        return new JsonResponse(
            [
                'code' => 200,
                'data' => $projectData,
            ],
            200
        );
    }

    #[Route('/projects/{id}', name: 'update_project', methods: ['PATCH'])]
    public function update(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        $project = $entityManager->getRepository(Project::class)->find($id);

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

        try {
            (new UpdateProjectAction(
                $project,
                $entityManager,
                $data,
                $validator
            ))->execute();
        } catch (ValidationException $exception) {
            return new JsonResponse(
                ['code' => 400, 'message' => $exception->getMessage()],
                200
            );
        } catch (Exception $exception) {
            return new JsonResponse(
                ['code' => 400, 'message' => 'Something went wrong'],
                200
            ); // TODO: global exception handling
        }

        return new JsonResponse(
            [
                'code' => 200,
                'message' => 'Project updated successfully',
                'data' => [
                    'id' => $project->getId(),
                    'title' => $project->getTitle(),
                    'description' => $project->getDescription(),
                    'status' => $project->getStatus(),
                    'duration' => $project->getDuration(),
                    'client' => $project->getClient(),
                    'company' => $project->getCompany(),
                    'deleted_at' => $project->getDeletedAt(),
                ],
            ],
            200
        );
    }
}
