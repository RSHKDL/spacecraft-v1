<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Component;

use App\Domain\Component\ComponentType;
use App\Domain\Component\PowerConsumption;
use App\Domain\Component\PowerOutput;
use App\Domain\Ship\ShipClass;
use App\Tests\Unit\Domain\ComponentModelTestHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ComponentModelTest extends TestCase
{
    use ComponentModelTestHelper;

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

    #[DataProvider('mismatchPowerProvider')]
    public function testAComponentModelWhosePowerDoesNotMatchItsTypeIsRejected(
        ComponentType $type,
        PowerOutput|PowerConsumption $power,
    ): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageIs('A component model power must match its type');

        self::releaseModel($type, [ShipClass::Corvette], $power);
    }

    /**
     * @return iterable<string, array{ComponentType, PowerOutput|PowerConsumption}>
     */
    public static function mismatchPowerProvider(): iterable
    {
        yield 'reactor with power consumption' => [ComponentType::Reactor, PowerConsumption::create(1)];
        yield 'weapon with power output' => [ComponentType::Weapon, PowerOutput::create(1)];
    }
}
