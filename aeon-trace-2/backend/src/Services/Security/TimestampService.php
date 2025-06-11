<?php

declare(strict_types=1);

namespace App\Services\Security;

use App\Entity\Dpp\Dpp;
use App\Entity\Logistics\Logistics;
use App\Entity\Product\ProductInput;
use App\Entity\Step\ProductStep;
use App\Kernel;
use DateTime;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class TimestampService
{
    private const HOST = 'tsa.swisssign.net';
    private const URL = 'http://tsa.swisssign.net';
    private const CA_PATH = 'public/cert/SwissSign_Signature_Services_Root_2020_-_1.ca';
    private const TS_DIR = 'public/media/timestamps';
    private const FP_ENTITY = '.json';
    private const FP_REQUEST = '.tsq';
    private const FP_RESPONSE = '.tsr';
    private const VERIFICATION_OK_OUTPUT = 'Verification: OK';
    private const TIMESTAMP_OUTPUT = 'Time stamp: ';

    private string $tsDirPath;

    public function __construct(
        private LoggerInterface $logger,
        private HttpClientInterface $httpClient,
        private Kernel $kernel,
        private SerializerInterface $serializer
    ) {
        $this->tsDirPath = $this->kernel->getProjectDir() . '/' . self::TS_DIR;
        if (!is_dir($this->tsDirPath)) {
            mkdir($this->tsDirPath);
        }
    }

    public function createTimestamp(Dpp|Logistics|ProductStep $entity): ?DateTime
    {
        $entityPath = $this->getSerializedEntityPath($entity);
        $requestPath = $this->getRequestFilePath($entity);
        $responsePath = $this->getResponseFilePath($entity);

        if ($this->serializeEntity($entity, $entityPath)) {
            $requestProcess = Process::fromShellCommandline('openssl ts -query -data ' . $entityPath . ' -cert -sha256 -no_nonce -out ' . $requestPath);
            $requestProcess->run();

            if ($requestProcess->isSuccessful()) {
                try {
                    $body = fopen($requestPath, 'r');
                    $response = $this->httpClient->request('POST', self::URL, [
                        'headers' => [
                            'accept' => '*/*',
                            'Content-Type' => 'application/timestamp-query',
                            'Host' => self::HOST,
                        ],
                        'body' => $body,
                    ]);
                    try {
                        if (file_exists($responsePath)) {
                            unlink($responsePath);
                        }
                        file_put_contents($responsePath, $response->getContent());
                        unlink($requestPath);

                        $projectDir = $this->kernel->getProjectDir();
                        $publicPrefix = $projectDir . '/public';
                        $relativePath = str_replace($publicPrefix, '', $responsePath);

                        if (method_exists($entity, 'setTimestampPath')) {
                            $entity->setTimestampPath($relativePath);
                        }
                    } catch (ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface $e) {
                        $this->logger->error($e);
                    }
                } catch (TransportExceptionInterface $e) {
                    $this->logger->error($e);
                }
            } else {
                $this->logger->error($requestProcess->getErrorOutput());
            }
            unlink($entityPath);

            foreach ($this->getTimestampInfo($entity) as $item) {
                if (str_contains($item, self::TIMESTAMP_OUTPUT)) {
                    $dateTimeTime = strtotime(substr($item, strlen(self::TIMESTAMP_OUTPUT)));
                    $dateTime = new DateTime();
                    if ($dateTimeTime !== false) {
                        $dateTime->setTimestamp($dateTimeTime);
                    }
                    return $dateTime;
                }
            }
        }

        return null;
    }

    public function verify(Dpp|Logistics|ProductStep $entity): bool
    {
        $entityPath = $this->getSerializedEntityPath($entity);
        $responsePath = $this->getResponseFilePath($entity);
        $caPath = $this->getCAFilePath();

        if ($this->serializeEntity($entity, $entityPath)) {
            $verifyProcess = Process::fromShellCommandline('openssl ts -verify -in ' . $responsePath . ' -data ' . $entityPath . ' -CAfile ' . $caPath);
            $verifyProcess->run();
            unlink($entityPath);
            return str_contains($verifyProcess->getOutput(), self::VERIFICATION_OK_OUTPUT);
        }

        return false;
    }

    /**
     * @return array<string>
     */
    public function getTimestampInfo(Dpp|Logistics|ProductStep $entity): array
    {
        $responsePath = $this->getResponseFilePath($entity);
        $infoProcess = Process::fromShellCommandline('openssl ts -reply -in ' . $responsePath . ' -text');
        $infoProcess->run();
        $data = explode(PHP_EOL, $infoProcess->getOutput());
        return array_values(array_filter($data, fn ($value) => $value !== null && $value !== ''));
    }

    protected function getEntityId(Dpp|Logistics|ProductStep $entity): string
    {
        $entityType = strtolower(str_replace('\\', '-', $entity::class));
        return $entityType . '-' . $entity->getId();
    }

    protected function getSerializedEntityPath(Dpp|Logistics|ProductStep $entity): string
    {
        return $this->tsDirPath . '/' . $this->getEntityId($entity) . uniqid() . self::FP_ENTITY;
    }

    protected function getRequestFilePath(Dpp|Logistics|ProductStep $entity): string
    {
        return $this->tsDirPath . '/' . $this->getEntityId($entity) . self::FP_REQUEST;
    }

    protected function getResponseFilePath(Dpp|Logistics|ProductStep $entity): string
    {
        return $this->tsDirPath . '/' . $this->getEntityId($entity) . self::FP_RESPONSE;
    }

    protected function getCAFilePath(): string
    {
        return $this->kernel->getProjectDir() . '/' . self::CA_PATH;
    }

    protected function serializeEntity(Dpp|Logistics|ProductStep $entity, string $path): bool
    {
        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups([Dpp::SERIALIZABLE, ProductInput::DPP_PRODUCT_INPUT_READ])
            ->toArray();

        if (file_put_contents($path, $this->serializer->serialize($entity, 'json', $context))) {
            return true;
        }

        $this->logger->error('Couldn\'t create serialized entity json.');
        return false;

    }
}
