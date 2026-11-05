<?php

declare(strict_types=1);

namespace App\Domain\Component;

final class ComponentModel
{
    private bool $discontinued = false;

    private function __construct(
        private readonly ComponentModelId $id,
        private readonly ComponentType $type,
    ) {}

    public static function release(ComponentModelId $id, ComponentType $type): self
    {
        return new self($id, $type);
    }

    public function discontinue(): void
    {
        $this->discontinued = true;
    }

    public function isDiscontinued(): bool
    {
        return $this->discontinued;
    }

    public function getType(): ComponentType
    {
        return $this->type;
    }
}
