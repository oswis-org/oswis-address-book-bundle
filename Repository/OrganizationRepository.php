<?php
/**
 * @noinspection PhpUnused
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\Persistence\ManagerRegistry;
use LogicException;
use OswisOrg\OswisAddressBookBundle\Entity\Organization;

class OrganizationRepository extends ServiceEntityRepository
{
    /**
     * @param  ManagerRegistry  $registry
     *
     * @throws LogicException
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Organization::class);
    }

    public function getFacultiesOfUniversity(?string $universitySlug = null): array
    {
        $qb = $this->createQueryBuilder('o')->where('o.type = faculty');
        if ($universitySlug) {
            $qb->andWhere('o.parent.type = university')->andWhere('o.parent.slug = :slug')->setParameter('slug', $universitySlug);
        }
        $result = $qb->getQuery()->execute([], AbstractQuery::HYDRATE_OBJECT);
        assert(is_array($result));

        return $result;
    }

    public function findOneBy(array $criteria, ?array $orderBy = null): ?Organization
    {
        $result = parent::findOneBy($criteria, $orderBy);

        return $result instanceof Organization ? $result : null;
    }
}
