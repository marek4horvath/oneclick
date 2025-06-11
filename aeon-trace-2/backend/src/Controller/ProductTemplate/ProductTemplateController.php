<?php

declare(strict_types=1);

namespace App\Controller\ProductTemplate;

use App\Entity\Product\ProductTemplate;
use App\Repository\Product\ProductTemplateRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class ProductTemplateController extends AbstractController
{
    /** @var ProductTemplateRepository<ProductTemplate> */
    private ProductTemplateRepository $productTemplateRepository;

    private SerializerInterface $serializer;

    /**
     * @param ProductTemplateRepository<ProductTemplate> $productTemplateRepository
     */
    public function __construct(ProductTemplateRepository $productTemplateRepository, SerializerInterface $serializer)
    {
        $this->productTemplateRepository = $productTemplateRepository;
        $this->serializer = $serializer;
    }

    public function __invoke(Request $request): Response
    {
        $idsJson = (string) $request->query->get('ids', '');

        if ($idsJson === '') {
            return new Response(
                json_encode(['error' => 'Invalid request. Missing or empty IDs parameter.']) ?: '{}',
                Response::HTTP_BAD_REQUEST,
                ['Content-Type' => 'application/json']
            );
        }

        /** @var string[]|null $ids */
        $ids = json_decode($idsJson, true);

        if (!is_array($ids)) {
            return new Response(
                json_encode(['error' => 'Invalid request. Expected an array of IDs.']) ?: '{}',
                Response::HTTP_BAD_REQUEST,
                ['Content-Type' => 'application/json']
            );
        }

        $productTemplates = [];

        foreach ($ids as $id) {
            $product = $this->productTemplateRepository->find($id);
            if ($product instanceof ProductTemplate) {
                $steps = [];

                if ($product->getStepsTemplate()) {
                    foreach ($product->getStepsTemplate()->getSteps() as $step) {
                        $steps[] = [
                            'name' => $step->getName(),
                            'id' => $step->getId(),
                        ];
                    }
                }

                $productTemplates[] = [
                    'name' => $product->getName(),
                    'id' => $product->getId(),
                    'steps' => $steps,
                ];
            }
        }

        if (empty($productTemplates)) {
            return new Response(
                json_encode(['error' => 'No product templates found for the provided IDs.']) ?: '{}',
                Response::HTTP_NOT_FOUND,
                ['Content-Type' => 'application/json']
            );
        }

        $jsonResponse = $this->serializer->serialize(
            ['product_templates' => $productTemplates],
            'json'
        );

        return new Response(
            $jsonResponse ?: '{}',
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }
}
