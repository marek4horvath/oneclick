<?php

declare(strict_types=1);

namespace App\Controller\Dpp;

use App\DataTransferObjects\Dpp\InputJsonWrapper;
use App\Entity\Product\ProductInput;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UpdateJsonInputsController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(InputJsonWrapper $data): JsonResponse
    {
        foreach ($data->getInputs() as $input) {

            /** @var ProductInput|null $productInput */
            $productInput = $this->entityManager->getRepository(ProductInput::class)->findInputById($input->inputId);

            if ($productInput === null) {
                $this->logger->warning("Product Input '{$input->inputId}' not found");
                continue;
            }

            $type = $productInput->getType();

            if (!$productInput->isUpdatable()) {
                continue;
            }

            switch ($type) {
                case 'text':
                    $productInput->setTextValue($input->text);
                    break;

                case 'textarea':
                    $productInput->setTextAreaValue($input->textArea);
                    break;

                case 'numerical':
                    if ($input->number !== null) {
                        $productInput->setNumericalValue($input->number);
                    }
                    break;

                case 'dateTime':
                    $productInput->setDateTimeTo($input->date);
                    break;

                case 'dateTimeRange':
                    $productInput->setDateTimeFrom($input->dateFrom);
                    $productInput->setDateTimeTo($input->dateTo);
                    break;

                case 'coordinates':
                    if ($input->lat !== null) {
                        $productInput->setLatitudeValue($input->lat);
                    }

                    if ($input->lng !== null) {
                        $productInput->setLongitudeValue($input->lng);
                    }
                    break;

                case 'checkboxList':
                    $productInput->setCheckboxValue($input->checkbox);
                    break;

                case 'radioList':

                    $productInput->setRadioValue($input->radio);
                    break;
            }

            if ($input->updatable !== null) {
                $productInput->setUpdatable($input->updatable);
            }

            $this->entityManager->persist($productInput);
        }

        $this->entityManager->flush();

        return new JsonResponse('Inputs updated successfully.', Response::HTTP_OK);
    }
}
