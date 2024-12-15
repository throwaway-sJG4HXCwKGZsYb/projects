<?php

namespace App\Doctrine\Filter;

use App\Entity\Project;
use App\Entity\Task;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class SoftDeleteFilter extends SQLFilter
{
    const SOFT_DELETABLE_ENTITIES = [Task::class, Project::class];

    public function addFilterConstraint(
        ClassMetadata $targetEntity,
        string $targetTableAlias
    ): string {
        if (in_array($targetEntity->getName(), self::SOFT_DELETABLE_ENTITIES)) {
            return "$targetTableAlias.deleted_at IS NULL";
        }

        return '';
    }
}
