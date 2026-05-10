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
        $qb = $this->createQueryBuilder('o')
            ->where('o.type = :facultyType')
            ->setParameter('facultyType', 'faculty');
        if ($universitySlug) {
            $qb->innerJoin('o.parentOrganization', 'parent')
                ->andWhere('parent.type = :universityType')
                ->andWhere('parent.slug = :slug')
                ->setParameter('universityType', 'university')
                ->setParameter('slug', $universitySlug);
        }
        $result = $qb->getQuery()->getResult(AbstractQuery::HYDRATE_OBJECT);
        assert(is_array($result));

        return $result;
    }

    public function findOneBy(array $criteria, ?array $orderBy = null): ?Organization
    {
        $result = parent::findOneBy($criteria, $orderBy);

        return $result instanceof Organization ? $result : null;
    }
}
