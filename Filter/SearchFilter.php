<?php
/** @noinspection PhpComposerExtensionStubsInspection */

namespace Zakjakub\OswisAddressBookBundle\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\QueryBuilder;

final class SearchFilter extends AbstractContextAwareFilter
{

    /**
     * @param string $resourceClass
     *
     * @return array
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function getDescription(string $resourceClass): array
    {
        $reader = new AnnotationReader();
        $annotation = $reader->getClassAnnotation(
            new \ReflectionClass(new $resourceClass),
            SearchAnnotation::class
        );

        /** @noinspection NullPointerExceptionInspection */
        $description['search'] = [
            'property' => 'search',
            'type'     => 'string',
            'required' => false,
            'swagger'  => ['description' => 'FullTextFilter on '.implode(', ', $annotation->fields)],
        ];

        return $description;
    }

    /**
     * @param string                      $property
     * @param                             $value
     * @param QueryBuilder                $queryBuilder
     * @param QueryNameGeneratorInterface $queryNameGenerator
     * @param string                      $resourceClass
     * @param string|null                 $operationName
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     * @throws \HttpInvalidParamException
     */
    public function filterProperty(
        string $property,
        /** @noinspection MissingParameterTypeDeclarationInspection */
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ): void {
        if ($property === 'search') {
            $this->logger->info('Search for: '.$value);
        } else {
            return;
        }

        $reader = new AnnotationReader();
        $annotation = $reader->getClassAnnotation(
            new \ReflectionClass(new $resourceClass),
            SearchAnnotation::class
        );

        if (!$annotation) {
            throw new \HttpInvalidParamException('No Search implemented.');
        }

        $parameterName = $queryNameGenerator->generateParameterName($property);
        $search = [];
        $mappedJoins = [];

        foreach ($annotation->fields as $field) {
            $joins = explode('.', $field);
            for ($lastAlias = 'o', $i = 0, $num = count($joins); $i < $num; $i++) {
                $currentAlias = $joins[$i];
                if ($i === $num - 1) {
                    $search[] = "LOWER({$lastAlias}.{$currentAlias}) LIKE LOWER(:{$parameterName})";
                } else {
                    $join = "{$lastAlias}.{$currentAlias}";
                    if (!in_array($join, $mappedJoins, true)) {
                        $queryBuilder->leftJoin($join, $currentAlias);
                        $mappedJoins[] = $join;
                    }
                }

                $lastAlias = $currentAlias;
            }
        }

        $queryBuilder->andWhere(implode(' OR ', $search));
        $queryBuilder->setParameter($parameterName, '%'.$value.'%');
    }
}
