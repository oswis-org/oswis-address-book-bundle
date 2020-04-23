<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 * @noinspection PhpUnused
 */

namespace OswisOrg\OswisAddressBookBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use OswisOrg\OswisAddressBookBundle\Entity\Organization;

class OrganizationRepository extends EntityRepository
{
    public function getFacultiesOfUniversity(?string $universitySlug = null): array
    {
        $qb = $this->createQueryBuilder('o')
            ->where('o.type = faculty');
        if ($universitySlug) {
            $qb->andWhere('o.parent.type = university')
                ->andWhere('o.parent.slug = :slug')
                ->setParameter('slug', $universitySlug);
        }

        return $qb->getQuery()
            ->execute([], Query::HYDRATE_OBJECT);
    }

    public function findOneBy(array $criteria, array $orderBy = null): ?Organization
    {
        $result = parent::findOneBy($criteria, $orderBy);

        return $result instanceof Organization ? $result : null;
    }
}
