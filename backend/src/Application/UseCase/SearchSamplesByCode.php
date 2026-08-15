<?php

namespace App\Application\UseCase;

use App\Domain\Entity\Sample;
use App\Domain\Repository\SampleRepositoryInterface;

final class SearchSamplesByCode
{
    public function __construct(
        private readonly SampleRepositoryInterface $samples
    ) {}

    /** @return list<Sample> */
    public function handle(string $searchQuery): array
    {
        return $this->samples->searchBySampleCode($searchQuery);
    }
}
