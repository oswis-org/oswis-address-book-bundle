<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 * @noinspection PhpUnused
 */

namespace Zakjakub\OswisAddressBookBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class OrganizationRepository extends EntityRepository
{
    public function getFacultiesOfUniversity(?string $universitySlug = null): array
    {
        $qb = $this->createQueryBuilder('o')->where('o.type = faculty');
        if ($universitySlug) {
            $qb->andWhere('o.parent.type = university')->andWhere('o.parent.slug = :slug')->setParameter('slug', $universitySlug);
        }

        return $qb->getQuery()->execute([], Query::HYDRATE_OBJECT);
    }
}
