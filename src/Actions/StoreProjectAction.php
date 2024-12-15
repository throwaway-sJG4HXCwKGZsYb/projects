<?php

namespace App\Actions;

use App\Entity\Project;
use App\Exceptions\ValidationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class StoreProjectAction
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected ValidatorInterface $validator,
        protected array $data,
    ) {
    }

    /**
     * @throws ValidationException
     */
    public function execute(): Project
    {
        $project = new Project();
        $project->setTitle($this->data['title'] ?? null);
        $project->setGuid($this->data['guid'] ?? null);
        $project->setDescription($this->data['description'] ?? null);
        $project->setStatus($this->data['status'] ?? null);
        $project->setDuration($this->data['duration'] ?? null);
        $project->setClient($this->data['client'] ?? null);
        $project->setCompany($this->data['company'] ?? null);

        $this->validate($project);

        $this->entityManager->persist($project);
        $this->entityManager->flush();

        return $project;
    }

    /**
     * @throws ValidationException
     */
    private function validate(Project $project): void
    {
        if (empty($this->data['client']) && empty($this->data['company'])) {
            throw new ValidationException(
                'Either client or company must be provided'
            );
        }

        $errors = $this->validator->validate($project);
        if (count($errors) > 0) {
            throw new ValidationException((string) $errors);
        }
    }
}
