<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Fleet\Dto;

use App\Application\Fleet\Command\FormFleet as FormFleetCommand;
use App\Domain\Fleet\Fleet;
use App\Domain\Fleet\FleetId;
use App\Domain\Fleet\FleetName;
use App\Domain\Ship\ShipId;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class FormFleet
{
    #[Assert\NotBlank]
    #[Assert\Length(max: FleetName::MAX_LENGTH)]
    public ?string $name = null;

    /**
     * @var list<string>
     */
    #[Assert\Count(min: Fleet::MINIMUM_SHIPS_TO_FORM_A_FLEET)]
    #[Assert\Unique]
    public array $shipIds = [];

    #[Assert\NotBlank]
    public ?string $flagshipId = null;

    public function toCommand(): FormFleetCommand
    {
        return new FormFleetCommand(
            FleetId::generate(),
            FleetName::create($this->name),
            array_map(ShipId::fromString(...), $this->shipIds),
            ShipId::fromString($this->flagshipId)
        );
    }

    #[Assert\Callback]
    public function validateFlagship(ExecutionContextInterface $context): void
    {
        if (null === $this->flagshipId) {
            return;
        }

        if (!in_array($this->flagshipId, $this->shipIds, true)) {
            $context->buildViolation('fleet.form.flagship_not_enlisted')
                ->atPath('flagshipId')
                ->addViolation();
        }
    }
}
