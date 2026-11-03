<?php

declare(strict_types=1);

namespace App\Domain\Fleet;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;

#[Embeddable]
final readonly class FleetName
{
    public const int MAX_LENGTH = 70;

    #[Column(type: Types::TEXT)]
    private string $value;

    private function __construct(
        string $value,
    ) {
        $value = trim($value);

        if ($value === '') {
            throw new \InvalidArgumentException('A fleet name must not be empty.');
        }

        if (mb_strlen($value) > self::MAX_LENGTH) {
            throw new \InvalidArgumentException(
                sprintf('A fleet name must not exceed %d characters.', self::MAX_LENGTH)
            );
        }

        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public static function create(string $name): self
    {
        return new self($name);
    }
}
