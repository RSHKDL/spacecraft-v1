<?php

declare(strict_types=1);

namespace App\Domain\Component;

interface Power
{
    public function getValue(): int;
}
