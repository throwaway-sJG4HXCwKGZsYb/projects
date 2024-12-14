<?php

namespace App\Controller;

use App\Actions\StoreProjectAction;
use App\Exceptions\ValidationException;
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
}
