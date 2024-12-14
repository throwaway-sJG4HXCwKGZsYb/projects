<?php

namespace App\Enums;

enum ProjectStatusesEnum: int
{
    case INACTIVE = 0;
    case ACTIVE = 1;

    public function text(): string
    {
        return match ($this) {
            ProjectStatusesEnum::INACTIVE => 'inactive',
            ProjectStatusesEnum::ACTIVE => 'active',
        };
    }
}
