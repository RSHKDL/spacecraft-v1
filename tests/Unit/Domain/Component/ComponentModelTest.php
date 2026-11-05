<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Component;

use App\Domain\Component\ComponentModel;
use App\Domain\Component\ComponentModelId;
use App\Domain\Component\ComponentType;
use PHPUnit\Framework\TestCase;

final class ComponentModelTest extends TestCase
{
    public function testAReleasedComponentModelIsNotDiscontinued(): void
    {
        $componentModel = ComponentModel::release(ComponentModelId::generate(), ComponentType::Reactor);

        self::assertFalse($componentModel->isDiscontinued());
    }

    public function testDiscontinuingAComponentModelMakesItDiscontinued(): void
    {
        $componentModel = ComponentModel::release(ComponentModelId::generate(), ComponentType::Reactor);
        $componentModel->discontinue();

        self::assertTrue($componentModel->isDiscontinued());
    }

    public function testDiscontinuingAnAlreadyDiscontinuedComponentModelIsANoOp(): void
    {
        $componentModel = ComponentModel::release(ComponentModelId::generate(), ComponentType::Reactor);
        $componentModel->discontinue();
        $componentModel->discontinue();

        self::assertTrue($componentModel->isDiscontinued());
    }
}
