<?php

declare(strict_types=1);

namespace App\Infrastructure\Twig;

final readonly class MenuItem
{
    public function __construct(
        public ?string $route,
        public string $label,
        public string $icon,
    ) {}
}
