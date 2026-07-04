<?php

namespace App\DTOs;

readonly class NetworkAggregatesDTO
{
    public function __construct(
        public int $doctorId,
        public string $specialization,
        public ?int $minYoe = null,
        public ?int $maxYoe = null,
    ) {}
}
