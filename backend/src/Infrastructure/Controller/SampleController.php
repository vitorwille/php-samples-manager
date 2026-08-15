<?php

namespace App\Infrastructure\Controller;

use App\Application\UseCase\CreateSample;
use App\Application\UseCase\FindSampleByCode;
use App\Application\UseCase\GetAllSamples;
use App\Application\UseCase\GetSamplesByType;
use App\Application\UseCase\SearchSamplesByCode;
use App\Application\UseCase\UpdateSampleStatus;
use App\Domain\Entity\SampleStatus;
use App\Domain\Entity\SampleType;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use DateTime;

final class SampleController
{
    public function __construct(
        private readonly GetAllSamples $getAllSamples,
        private readonly GetSamplesByType $getSamplesByType,
        private readonly FindSampleByCode $findSampleByCode,
        private readonly SearchSamplesByCode $searchSamplesByCode,
        private readonly CreateSample $createSample,
        private readonly UpdateSampleStatus $updateSampleStatus,
    ) {}

    public function index(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();

        if (isset($params['code'])) {
            return $this->findBySampleCode($request, $response, ['sampleCode' => $params['code']]);
        }

        if (isset($params['search'])) {
            return $this->searchBySampleCode($request, $response);
        }

        if (isset($params['type'])) {
            try {
                $sampleType = $this->parseType((string) $params['type']);
            } catch (\ValueError) {
                $response->getBody()->write(json_encode(['error' => 'Invalid sampleType.'], JSON_PRETTY_PRINT));

                return $response->withStatus(400);
            }

            $samples = [];
            foreach ($this->getSamplesByType->handle($sampleType) as $sample) {
                $samples[] = $sample->toArray();
            }

            $response->getBody()->write(json_encode($samples, JSON_PRETTY_PRINT));

            return $response;
        }

        $samples = [];
        foreach ($this->getAllSamples->handle() as $sample) {
            $samples[] = $sample->toArray();
        }

        $response->getBody()->write(json_encode($samples, JSON_PRETTY_PRINT));

        return $response;
    }

    public function findBySampleCode(Request $request, Response $response, array $args): Response
    {
        $sample = $this->findSampleByCode->handle($args['sampleCode']);

        if (!$sample) {
            $response->getBody()->write(json_encode(['error' => 'Sample not found'], JSON_PRETTY_PRINT));

            return $response->withStatus(404);
        }

        $response->getBody()->write(json_encode($sample->toArray(), JSON_PRETTY_PRINT));

        return $response;
    }

    public function searchBySampleCode(Request $request, Response $response): Response
    {
        $query = (string) ($request->getQueryParams()['search'] ?? '');
        $samples = [];
        foreach ($this->searchSamplesByCode->handle($query) as $sample) {
            $samples[] = $sample->toArray();
        }

        $response->getBody()->write(json_encode($samples, JSON_PRETTY_PRINT));

        return $response;
    }

    public function create(Request $request, Response $response): Response
    {
        $body = json_decode((string) $request->getBody(), true) ?? [];

        try {
            $sample = $this->createSample->handle(
                (string) ($body['sampleCode'] ?? ''),
                $this->parseType($body['sampleType'] ?? ''),
                $this->parseStatus('recebida'),
                (string) ($body['sampleTechnician'] ?? ''),
                new DateTime((string) ($body['sampleReceivalDate'] ?? '')),
                null
            );
        } catch (\InvalidArgumentException|\ValueError $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT));

            return $response->withStatus(400);
        }

        $response->getBody()->write(json_encode($sample->toArray(), JSON_PRETTY_PRINT));

        return $response->withStatus(201);
    }

    public function update(Request $request, Response $response): Response
    {
        $body = json_decode((string) $request->getBody(), true) ?? [];

        $sampleCode = (string) ($body['sampleCode'] ?? '');
        $sampleStatus = (string) ($body['sampleStatus'] ?? '');
        $sampleConclusionDate = $body['sampleConclusionDate'] ?? null;
        $sampleConclusionDate = $sampleConclusionDate ? new DateTime($sampleConclusionDate) : null;

        if ($sampleCode === '') {
            $response->getBody()->write(json_encode(['error' => 'Missing required field: "sampleCode".'], JSON_PRETTY_PRINT));

            return $response->withStatus(400);
        }

        if ($sampleStatus === '') {
            $response->getBody()->write(json_encode(['error' => 'Missing required field: "sampleStatus".'], JSON_PRETTY_PRINT));

            return $response->withStatus(400);
        }

        try {
            $sample = $this->updateSampleStatus->handle(
                $sampleCode,
                $this->parseStatus($sampleStatus),
                $sampleConclusionDate,
            );
        } catch (\InvalidArgumentException|\ValueError $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT));

            return $response->withStatus(400);
        }

        $response->getBody()->write(json_encode($sample->toArray(), JSON_PRETTY_PRINT));

        return $response;
    }

    private function parseStatus(string $status): SampleStatus
    {
        return SampleStatus::from(strtolower(trim($status)));
    }

    private function parseType(string $type): SampleType
    {
        return SampleType::from(strtolower(trim($type)));
    }
}
