<?php

declare(strict_types=1);

namespace App\Controller\Dpp;

use App\Entity\Dpp\Dpp;
use App\Entity\Dpp\DppConnector;
use App\Entity\Logistics\Logistics;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class JsonExportDppController extends AbstractController
{
    public function __invoke(Request $request, Dpp $dpp): Response
    {
        $jsonDpp = new stdClass();
        $jsonDpp->id = $dpp->getId();
        $jsonDpp->node = $dpp->getNode() ? $dpp->getNode()->getId() : null;
        $jsonDpp->company = $dpp->getCompany() ? $dpp->getCompany()->getId() : null;

        /** @var Logistics $logistic */
        foreach ($dpp->getMaterialsReceivedFrom() as $logistic) {
            $jsonDpp->logistics[] = $logistic->getId();
        }

        /** @var DppConnector $connector */
        foreach ($dpp->getSourceDppConnectors() as $connector) {
            $jsonDpp->fromDpps[] = $connector->getSourceDpp()->getId();
        }

        foreach ($dpp->getProductSteps() as $sequenceIndex => $productStep) {
            $jsonProductStep = new stdClass();
            $jsonProductStep->id = $productStep->getId();
            $jsonProductStep->sequenceIndex = $sequenceIndex + 1;
            $jsonProductStep->quantityIndex = $productStep->getQuantityIndex();
            $jsonProductStep->name = $productStep->getName();
            $productTemplate = $productStep->getProduct() ? $productStep->getProduct()->getProductTemplate() : null;
            $productTemplateName = $productTemplate ? $productTemplate->getName() : null;
            $jsonProductStep->productTemplateName = $productTemplateName;

            if (count($productStep->getProductInputs()) == 0) {
                    $jsonProductStep->verified = false;
                    $jsonProductStep->confirm = null;
                    foreach ($productStep->getStepTemplateReference()->getInputs() as $input) {
                        $jsonNewInput = new stdClass();
                        $jsonNewInput->name = $input->getName();
                        $jsonNewInput->type = $input->getType();

                        switch ($input->getType()) {
                            case 'numerical':
                                $jsonNewInput->numericalValue = null;
                                break;

                            case 'text':
                                $jsonNewInput->textValue = null;
                                break;

                            case 'textarea':
                                $jsonNewInput->textAreaValue = null;
                                break;

                            case 'datetime':
                                $jsonNewInput->dateTimeFrom = null;
                                $jsonNewInput->dateTimeTo = null;
                                break;

                            case 'datetimerange':
                                $jsonNewInput->dateTimeFrom = null;
                                $jsonNewInput->dateTimeTo = null;
                                break;

                            case 'coordinates':
                                $jsonNewInput->latitudeValue = null;
                                $jsonNewInput->longitudeValue = null;
                                break;

                            case 'radio':
                                $jsonNewInput->radioValue = null;
                                break;

                            case 'checkbox':
                                $jsonNewInput->checkboxValue = null;
                                break;
                        }

                        $jsonProductStep->inputs[] = $jsonNewInput;
                    }
            } else {
                foreach ($productStep->getProductInputs() as $productInput) {
                    $jsonProductStep->verified = true;
                    $jsonProductStep->confirm = false;
                    $jsonInput = new stdClass();
                    $jsonInput->name = $productInput->getName();
                    $jsonInput->type = $productInput->getType();

                    switch ($productInput->getType()) {
                        case 'numerical':
                            $jsonInput->numericalValue = $productInput->getNumericalValue();
                            break;

                        case 'text':
                            $jsonInput->textValue = $productInput->getTextValue();
                            break;

                        case 'textarea':
                            $jsonInput->textAreaValue = $productInput->getTextAreaValue();
                            break;

                        case 'datetime':
                            $jsonInput->dateTimeFrom = $productInput->getDateTimeFrom();
                            break;

                        case 'datetimerange':
                            $jsonInput->dateTimeFrom = $productInput->getDateTimeFrom();
                            $jsonInput->dateTimeTo = $productInput->getDateTimeTo();
                            break;

                        case 'coordinates':
                            $jsonInput->latitudeValue = $productInput->getLatitudeValue();
                            $jsonInput->longitudeValue = $productInput->getLongitudeValue();
                            break;

                        case 'radio':
                            $jsonInput->radioValue = $productInput->getRadioValue();
                            break;

                        case 'checkbox':
                            $jsonInput->checkboxValue = $productInput->getCheckboxValue();
                            break;
                    }

                    $jsonProductStep->inputs[] = $jsonInput;
                }
            }

            $jsonDpp->steps[] = $jsonProductStep;
        }

        $jsonResponse = json_encode(['dpp' => $jsonDpp]);
        $responseCode = Response::HTTP_OK;

        if ($jsonResponse === false) {
            throw new \RuntimeException('Failed to encode JSON: ' . json_last_error_msg());
        }

        return new Response(
            $jsonResponse,
            $responseCode,
            ['Content-Type' => 'application/json']
        );
    }
}
