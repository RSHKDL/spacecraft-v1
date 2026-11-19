<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ShipDesign;

use App\Domain\Component\ComponentType;
use App\Domain\Component\PowerConsumption;
use App\Domain\Component\PowerOutput;
use App\Domain\ShipDesign\Component;
use App\Domain\ShipDesign\PowerBalance;
use App\Tests\Unit\Domain\ComponentModelTestHelper;
use PHPUnit\Framework\TestCase;

final class PowerBalanceTest extends TestCase
{
    use ComponentModelTestHelper;

    public function testABalanceIsTheReactorOutputMinusOtherComponentsConsumption(): void
    {
        $components = [
            self::createComponent(type: ComponentType::Reactor, power: PowerOutput::create(75)),
            self::createComponent(type: ComponentType::Weapon, power: PowerConsumption::create(10)),
            self::createComponent(type: ComponentType::Shield, power: PowerConsumption::create(15)),
        ];

        $powerBalance = PowerBalance::of($components);

        self::assertSame(50, $powerBalance->getValue());
    }

    public function testABalanceWithoutConsumerEqualsTheReactorOutput(): void
    {
        $components = [
            self::createComponent(type: ComponentType::Reactor, power: PowerOutput::create(75)),
        ];

        $powerBalance = PowerBalance::of($components);

        self::assertSame(75, $powerBalance->getValue());
    }

    public function testABalanceCanBeNegative(): void
    {
        $components = [
            self::createComponent(type: ComponentType::Reactor, power: PowerOutput::create(50)),
            self::createComponent(type: ComponentType::Weapon, power: PowerConsumption::create(51)),
        ];

        $powerBalance = PowerBalance::of($components);

        self::assertSame(-1, $powerBalance->getValue());
    }
}
