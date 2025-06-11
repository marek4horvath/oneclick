<?php

declare(strict_types=1);

namespace App\Controller\Dpp;

use App\DataTransferObjects\Dpp\CreateFromJsonInput;
use App\Entity\Company\Company;
use App\Entity\Dpp\Dpp;
use App\Entity\Product\Product;
use App\Entity\Product\ProductInput;
use App\Entity\Step\ProductStep;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MapEnaleiaJsonToDppController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(CreateFromJsonInput $input): Dpp
    {
        $json = file_get_contents($input->json);

        if (!$json) {
            throw new InvalidArgumentException('Invalid json file.');
        }

        $dJson = json_decode($json, true);

        if (!is_array($dJson)) {
            throw new InvalidArgumentException('Decoded JSON is not an array.');
        }

        // Check required fields in $dJson and validate types
        if (
            !isset($dJson['reference'], $dJson['model'], $dJson['model_id'], $dJson['id'], $dJson['process'], $dJson['quality_control']) ||
            !is_string($dJson['reference']) ||
            !is_string($dJson['model']) ||
            !is_string($dJson['model_id']) ||
            !is_array($dJson['process']) ||
            !is_array($dJson['quality_control']) ||
            !is_int($dJson['id'])
        ) {
            throw new InvalidArgumentException('Invalid data in JSON structure.');
        }

        $company = $this->entityManager->getRepository(Company::class)->findOneBy(['name' => $dJson['reference']]);

        $dpp = new Dpp();
        $product = new Product();

        $product->setName($dJson['model']);
        $product->setModelId($dJson['model_id']);

        // TODO: Handle images if necessary
        // $product->setProductImage($dJson['images'][0]);

        // Create "Process" step
        $processStep = new ProductStep();
        $processStep->setProduct($product);
        $processStep->setName('Process');

        foreach ($dJson['process'] as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            $newInput = new ProductInput();
            $newInput->setProductStep($processStep);
            $newInput->setName($key);

            if (is_string($value)) {
                $newInput->setTextValue($value);
            } elseif (is_int($value) || is_float($value)) {
                $newInput->setNumericalValue($value);
            }

            $this->entityManager->persist($newInput);
        }

        $this->entityManager->persist($processStep);

        $processStep->setDppName($processStep->getName() . ':' . $processStep->getId()->toRfc4122());

        $this->entityManager->persist($processStep);

        // TODO: Handle "Materials" step when requirements are clear.

        // Create "Quality Control" step
        $qualityControlStep = new ProductStep();
        $qualityControlStep->setProduct($product);
        $qualityControlStep->setName('Quality Control');

        foreach ($dJson['quality_control'] as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            $newInput = new ProductInput();
            $newInput->setProductStep($qualityControlStep);
            $newInput->setName($key);

            if (is_string($value)) {
                $newInput->setTextValue($value);
            } elseif (is_int($value) || is_float($value)) {
                $newInput->setNumericalValue($value);
            }

            $this->entityManager->persist($newInput);
        }

        $this->entityManager->persist($qualityControlStep);

        $qualityControlStep->setDppName($qualityControlStep->getName() . ':' . $qualityControlStep->getId()->toRfc4122());

        $this->entityManager->persist($qualityControlStep);
        $this->entityManager->persist($product);

        $dpp->setQrId($dJson['id']);
        $dpp->addProduct($product);
        $dpp->setName($dJson['model']);

        if ($company !== null) {
            $dpp->setCompany($company);
        }

        $dpp->setImported(true);

        $this->entityManager->persist($dpp);
        $this->entityManager->flush();

        return $dpp;
    }
}
