<?php

namespace App\Actions;

use App\Entity\Project;
use App\Exceptions\ValidationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateProjectAction
{
    public function __construct(
        protected Project $project,
        protected EntityManagerInterface $entityManager,
        protected array $data,
        protected ValidatorInterface $validator
    ) {
    }

    /**
     * @throws ValidationException
     */
    public function execute(): void
    {
        $this->project->setTitle($this->data['title'] ?? null);
        $this->project->setGuid($this->data['guid'] ?? null);
        $this->project->setDescription($this->data['description'] ?? null);
        $this->project->setStatus($this->data['status'] ?? null);
        $this->project->setDuration($this->data['duration'] ?? null);
        $this->project->setClient($this->data['client'] ?? null);
        $this->project->setCompany($this->data['company'] ?? null);

        $this->validate();

        $this->entityManager->persist($this->project);
        $this->entityManager->flush();
    }

    /**
     * @throws ValidationException
     */
    private function validate(): void
    {
        if (empty($this->data['client']) && empty($this->data['company'])) {
            throw new ValidationException(
                'Either client or company must be provided'
            );
        }

        $errors = $this->validator->validate($this->project);
        if (count($errors) > 0) {
            throw new ValidationException((string) $errors);
        }
    }
}
