<?php

declare(strict_types=1);

namespace App\Services\Qr;

use App\Entity\Dpp\Dpp;
use App\Entity\Logistics\Logistics;
use App\Entity\Step\ProductStep;
use App\Entity\Step\Step;
use App\Entity\SupplyChain\Node;
use App\Kernel;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;
use Vich\UploaderBundle\FileAbstraction\ReplacingFile;

readonly class QrService
{
    private const PLATFORM = 'qrfy';
    private const API_URL = 'https://qrfy.com';
    private const API_KEY = 'QRFY_API_KEY';
    private const DEFAULT_FOLDER_ID = 'QRFY_DEFAULT_FOLDER_ID';
    private const BASE_URL = 'BASE_URL';
    private const STATIC_LOGO = '/assets/images/logo.jpg';

    private string|bool $apiKey;
    private string|bool $baseUrl;
    private string|bool $defaultFolderId;

    public function __construct(
        private LoggerInterface $logger,
        private HttpClientInterface $httpClient,
        private Kernel $kernel,
        private EntityManagerInterface $entityManager,
        private ParameterBagInterface $parameterBag,
    ) {
        $apiKey = $this->parameterBag->get(self::API_KEY);
        $this->apiKey = is_array($apiKey) ? false : strval($apiKey);

        $baseUrl = $this->parameterBag->get(self::BASE_URL);
        $this->baseUrl = is_array($baseUrl) ? false : strval($baseUrl);

        $defaultFolderId = $this->parameterBag->get(self::DEFAULT_FOLDER_ID);
        $this->defaultFolderId = is_array($defaultFolderId) ? false : strval($defaultFolderId);
    }

    public function generateQrCode(Dpp|ProductStep|Step|Node|Logistics $entity): File
    {
        $logo = '';
        $logoPath = $this->kernel->getProjectDir() . '/public' . self::STATIC_LOGO;
        $folderId = $this->resolveFolderId($entity);

        $type = pathinfo($logoPath, PATHINFO_EXTENSION);
        $logoData = file_get_contents($logoPath);
        if ($logoData) {
            $logo = 'data:image/' . $type . ';base64,' . base64_encode($logoData);
        }

        $payload = [
            'folder' => $folderId,
            'qrs' => [[
                'name' => $this->getEntityName($entity),
                'type' => 'url-static',
                'data' => ['url' => $this->getEntityUrl($entity)],
            ]],
            'style' => ['image' => $logo],
        ];

        try {
            $response = $this->httpClient->request('POST', self::API_URL . '/api/public/qrs', [
                'headers' => [
                    'accept' => '*/*',
                    'API-KEY' => $this->apiKey,
                ],
                'json' => $payload,
            ]);

            if ($response->getStatusCode() !== Response::HTTP_OK) {
                throw new Exception('QR API returned status: ' . $response->getStatusCode());
            }

            $qrIds = json_decode($response->getContent(), true);

            $qrId = null;
            if (is_array($qrIds) && isset($qrIds['ids']) && is_array($qrIds['ids']) && isset($qrIds['ids'][0])) {
                $qrId = $qrIds['ids'][0];
            }

            if (!$qrId) {
                throw new Exception('QR ID not returned in response');
            }

            $imageResponse = $this->httpClient->request('GET', self::API_URL . '/api/public/qrs/' . $qrId . '/png', [
                'headers' => [
                    'accept' => '*/*',
                    'API-KEY' => $this->apiKey,
                ],
            ]);

            if ($imageResponse->getStatusCode() !== Response::HTTP_OK) {
                throw new Exception('QR image fetch failed with status: ' . $imageResponse->getStatusCode());
            }

            $filePath = $this->getTmpDir() . '/' . self::PLATFORM . '-' . $entity->getId() . '-' . time() . '.png';
            file_put_contents($filePath, $imageResponse->getContent());

            return new ReplacingFile($filePath);
        } catch (Throwable $e) {
            $this->logger->error($e);

            $type = pathinfo($logoPath, PATHINFO_EXTENSION);
            $logoData = file_get_contents($logoPath);
            if ($logoData) {
                $logo = 'data:image/' . $type . ';base64,' . base64_encode($logoData);
            }

            return $this->generateNativeQrCode($entity, $logo);
        }
    }

    protected function generateNativeQrCode(Dpp|ProductStep|Step|Node|Logistics $entity, ?string $logo): File
    {
        $writer = new PngWriter();
        $qrCode = QrCode::create($this->getEntityUrl($entity))
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::High)
            ->setSize(300)
            ->setMargin(10)
            ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));

        $qrLogo = null;
        if ($logo) {
            $qrLogo = Logo::create($logo)
                ->setResizeToWidth(100)
                ->setPunchoutBackground(true);
        }

        $result = $writer->write($qrCode, $qrLogo);
        $filePath = $this->getTmpDir() . '/' . $entity->getId() . '-' . time() . '.png';
        $result->saveToFile($filePath);

        return new ReplacingFile($filePath);
    }

    protected function getTmpDir(): string
    {
        $tmpDir = $this->kernel->getProjectDir() . '/public/media/tmp';
        if (!is_dir($tmpDir)) {
            if (!mkdir($tmpDir, 0755, true)) {
                return sys_get_temp_dir();
            }
        }

        return $tmpDir;
    }

    public function generateQrId(Dpp|ProductStep|Step|Node|Logistics $entity, EntityManagerInterface $entityManager): ?int
    {
        if ($entity->getQrId() === 0 || $entity->getQrId() === null) {
            return $this->getMaxQrId($entity, $entityManager) + 1;
        }

        if ($this->checkIfQrIdExists($entity, $entityManager)) {
            return null;
        }

        return $entity->getQrId();
    }

    protected function getMaxQrId(Dpp|ProductStep|Step|Node|Logistics $entity, EntityManagerInterface $entityManager): int
    {
        $queryBuilder = $entityManager->getRepository(get_class($entity))->createQueryBuilder('e')
            ->select('MAX(e.qrId) AS maxId');

        if (method_exists($entity, 'getCompany') && $entity->getCompany() !== null) {
            $queryBuilder
                ->andWhere('e.company = :companyId')
                ->setParameter('companyId', $entity->getCompany()->getId(), UuidType::NAME);
        } elseif (method_exists($entity, 'getCompany') && $entity->getCompany() === null) {
            $queryBuilder
                ->andWhere('e.company IS NULL');
        }

        $result = $queryBuilder->getQuery()->getSingleResult();

        if (is_array($result)) {
            $maxId = $result['maxId'];
            if ($maxId === null) {
                return 0;
            }
            return $maxId;
        }

        return 0;
    }

    protected function checkIfQrIdExists(Dpp|ProductStep|Step|Node|Logistics $entity, EntityManagerInterface $entityManager): bool
    {
        if (method_exists($entity, 'getCompany')) {
            /** @var Dpp|ProductStep|Step|Node|Logistics|null $result */
            $result = $entityManager->getRepository(get_class($entity))->findOneBy([
                'qrId' => $entity->getQrId(),
                'company' => $entity->getCompany(),
            ]);
        } else {
            /** @var Dpp|ProductStep|Step|Node|Logistics|null $result */
            $result = $entityManager->getRepository(get_class($entity))->findOneBy([
                'qrId' => $entity->getQrId(),
            ]);
        }

        if ($result === null || $result->getId() === $entity->getId()) {
            return false;
        }

        return true;
    }

    protected function getEntityUrl(Dpp|ProductStep|Step|Node|Logistics $entity): string
    {
        $slugger = new AsciiSlugger();
        $entitySlug = ($entity->getName()) ? $slugger->slug($entity->getName())->lower()->toString() : '';

        if (method_exists($entity, 'getCompany') && $entity->getCompany() !== null) {
            return $this->baseUrl . '/detail-of-dpp/company/' . $entity->getCompany()->getSlug() . '/' . $entity::URL_PATH . '/' . $entitySlug . '/' . $entity->getQrId();
        }

        return $this->baseUrl . '/detail-of-dpp/' . $entity::URL_PATH . '/' . $entitySlug . '/' . $entity->getQrId();
    }

    protected function getEntityName(Dpp|ProductStep|Step|Node|Logistics $entity): string
    {
        $name = $entity->getName() . ' [' . $entity->getQrId() . ']';
        if (method_exists($entity, 'getCompany')) {
            $name = $entity->getCompany()?->getName() . ' - ' . $name;
        }

        return $name;
    }

    private function resolveFolderId(Dpp|ProductStep|Step|Node|Logistics $entity): int
    {
        $folderId = $this->defaultFolderId;

        if ($entity instanceof Dpp) {
            $supplyChainTemplate = $entity->getSupplyChainTemplate();
            if ($supplyChainTemplate !== null) {
                $existingId = $supplyChainTemplate->getQrFolderId();
                if ($existingId !== null) {
                    return $existingId;
                }

                try {
                    $response = $this->httpClient->request('POST', self::API_URL . '/api/public/folders', [
                        'headers' => [
                            'accept' => '*/*',
                            'API-KEY' => $this->apiKey,
                        ],
                        'json' => ['name' => $supplyChainTemplate->getName()],
                    ]);

                    if ($response->getStatusCode() === Response::HTTP_OK) {
                        $data = json_decode($response->getContent(), true);

                        $newId = 0;
                        if (is_array($data) && isset($data['id'])) {
                            $newId = intval($data['id']);
                        }

                        if ($newId > 0) {
                            $supplyChainTemplate->setQrFolderId($newId);
                            $this->entityManager->persist($supplyChainTemplate);
                            $this->entityManager->flush();
                            return $newId;
                        }
                    }
                } catch (Throwable $e) {
                    $this->logger->error('Error creating folder: ' . $e->getMessage());
                }
            }
        }

        return (int) $folderId;
    }
}
