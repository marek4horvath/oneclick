<?php declare(strict_types=1);

namespace App\Api\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Dpp\Dpp;
use App\Entity\Product\ProductInput;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\PropertyInfo\Type;

class DppProductInputsLengthOrderFilter extends AbstractFilter
{
    /**
     * @param mixed $value
     */
    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        if ($property !== 'order' || !is_array($value) || !isset($value['numberOfInputs']) || $resourceClass !== Dpp::class) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $inputAlias = $queryNameGenerator->generateJoinAlias('productInputs');

        $queryBuilder->leftJoin("{$rootAlias}.productInputs", $inputAlias)
            ->addSelect($inputAlias);

        $subQuery = $queryBuilder->getEntityManager()->createQueryBuilder()
            ->select('COUNT(pi.id)')
            ->from(ProductInput::class, 'pi')
            ->where("pi.dpp = {$rootAlias}")
            ->getDQL();

        $sortDirection = strtolower($value['numberOfInputs']) === 'desc' ? 'DESC' : 'ASC';
        $queryBuilder->addSelect("({$subQuery}) as HIDDEN productInputsCount");
        $queryBuilder->orderBy('productInputsCount', $sortDirection);
    }

    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }
        return array_map(function ($strategy) {
            return [
                'property' => 'numberOfInputs',
                'type' => Type::BUILTIN_TYPE_STRING,
                'required' => false,
                'description' => 'Sort by the number of product inputs.',
                'schema' => [
                    'type' => 'string',
                    'enum' => ['asc', 'desc'],
                ],
                'openapi' => [
                    'allowReserved' => false,
                    'allowEmptyValue' => true,
                    'explode' => false,
                ],
            ];
        }, $this->properties);
    }
}
