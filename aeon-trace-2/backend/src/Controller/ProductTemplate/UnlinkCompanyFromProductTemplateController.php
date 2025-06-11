<?php

declare(strict_types=1);

namespace App\Controller\ProductTemplate;

use App\Entity\Product\ProductTemplate;
use App\Repository\Product\ProductTemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;

class UnlinkCompanyFromProductTemplateController extends AbstractController
{
    /** @var ProductTemplateRepository<ProductTemplate> */
    private ProductTemplateRepository $productTemplateRepository;
    private SerializerInterface $serializer;
    private EntityManagerInterface $em;

    /**
     * @param ProductTemplateRepository<ProductTemplate> $productTemplateRepository
     */
    public function __construct(
        ProductTemplateRepository $productTemplateRepository,
        SerializerInterface $serializer,
        EntityManagerInterface $em
    ) {
        $this->productTemplateRepository = $productTemplateRepository;
        $this->serializer = $serializer;
        $this->em = $em;
    }

    public function __invoke(Request $request): Response
    {
        $productTemplateId = $request->attributes->get('id');

        // Decode the request content and validate it as an array
        $data = json_decode($request->getContent(), true);

        if (!is_array($data) || !isset($data['ids']) || !is_array($data['ids'])) {
            throw new BadRequestHttpException('Invalid or missing "ids" parameter.');
        }

        $iris = $data['ids'];

        if (!$productTemplateId || empty($iris)) {
            throw new BadRequestHttpException('Missing "id" or "ids" parameter.');
        }

        // Find the ProductTemplate
        $productTemplate = $this->productTemplateRepository->find($productTemplateId);
        if (!$productTemplate) {
            throw new NotFoundHttpException('ProductTemplate not found.');
        }

        // Loop through the provided IRI list and unlink the companies
        foreach ($iris as $companyIri) {
            $companies = $productTemplate->getCompanies();
            $matchedCompany = null;

            // Look for the matching company by comparing IRI
            foreach ($companies as $company) {
                if (sprintf('/api/companies/%s', $company->getId()) === $companyIri) {
                    $matchedCompany = $company;
                    break;
                }
            }

            // If company is not found, throw an exception
            if (!$matchedCompany) {
                throw new NotFoundHttpException("Company not found in ProductTemplate: {$companyIri}");
            }

            // Remove the matched company from the product template
            $productTemplate->removeCompany($matchedCompany);
        }

        // Persist changes
        $this->em->persist($productTemplate);
        $this->em->flush();

        // Serialize the updated product template
        $json = $this->serializer->serialize($productTemplate, 'json', ['groups' => ['product-template-read']]);

        return new Response($json, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }
}
