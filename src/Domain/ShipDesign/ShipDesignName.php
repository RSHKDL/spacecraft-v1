<?php

declare(strict_types=1);

namespace App\Domain\ShipDesign;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final readonly class ShipDesignName
{
    public const int MAX_LENGTH = 30;

    #[ORM\Column(name: 'name', type: Types::TEXT)]
    private string $value;

    private function __construct(
        string $value,
    ) {
        $value = trim($value);

        if ($value === '') {
            throw new \InvalidArgumentException('A ship design name must not be empty');
        }

        if (mb_strlen($value) > self::MAX_LENGTH) {
            throw new \InvalidArgumentException(
                sprintf('A ship design name must not exceed %d characters', self::MAX_LENGTH)
            );
        }

        $this->value = $value;
    }

    public static function create(string $name): self
    {
        return new self($name);
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
