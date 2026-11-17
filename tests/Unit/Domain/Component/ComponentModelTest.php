<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Component;

use App\Domain\Component\ComponentModel;
use App\Domain\Component\ComponentModelId;
use App\Domain\Component\ComponentType;
use App\Domain\Ship\ShipClass;
use PHPUnit\Framework\TestCase;

final class ComponentModelTest extends TestCase
{
    public function testAReleasedComponentModelIsNotDiscontinued(): void
    {
        $componentModel = self::releaseModel(ComponentType::Reactor, ShipClass::cases());

        self::assertFalse($componentModel->isDiscontinued());
    }

    public function testDiscontinuingAComponentModelMakesItDiscontinued(): void
    {
        $componentModel = self::releaseModel(ComponentType::Reactor, ShipClass::cases());
        $componentModel->discontinue();

        self::assertTrue($componentModel->isDiscontinued());
    }

    public function testDiscontinuingAnAlreadyDiscontinuedComponentModelIsANoOp(): void
    {
        $componentModel = self::releaseModel(ComponentType::Reactor, ShipClass::cases());
        $componentModel->discontinue();
        $componentModel->discontinue();

        self::assertTrue($componentModel->isDiscontinued());
    }

    public function testAComponentModelSupportingNoShipClassIsRejected(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageIs('A component model must support at least one ship class');

        self::releaseModel(ComponentType::Shield, []);
    }

    /**
     * @param list<ShipClass> $shipClasses
     */
    private static function releaseModel(ComponentType $type, array $shipClasses): ComponentModel
    {
        return ComponentModel::release(ComponentModelId::generate(), $type, $shipClasses);
    }
}
