<?php declare(strict_types=1);

namespace App\Api\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Logistics\LogisticsTemplate;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\PropertyInfo\Type;

class LogisticsTemplateTransportTypeNameOrderFilter extends AbstractFilter
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
        if ($property !== 'order' || !is_array($value) || !isset($value['typeOfTransport']) || $resourceClass !== LogisticsTemplate::class) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $transportTypeAlias = $queryNameGenerator->generateJoinAlias('typeOfTransport');

        $queryBuilder->leftJoin("{$rootAlias}.typeOfTransport", $transportTypeAlias)
            ->addSelect($transportTypeAlias);

        $sortDirection = strtolower($value['typeOfTransport']) === 'desc' ? 'DESC' : 'ASC';

        $queryBuilder->orderBy("{$transportTypeAlias}.name", $sortDirection);
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'order[typeOfTransport]' => [
                'property' => 'typeOfTransport',
                'type' => Type::BUILTIN_TYPE_STRING,
                'required' => false,
                'description' => 'Sort logistics templates by transport type name.',
                'schema' => ['type' => 'string', 'enum' => ['asc', 'desc']],
                'openapi' => ['allowReserved' => false, 'allowEmptyValue' => true, 'explode' => false],
            ],
        ];
    }
}
