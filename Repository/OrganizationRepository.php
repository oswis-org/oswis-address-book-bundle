<?php

namespace Zakjakub\OswisAddressBookBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class OrganizationRepository extends EntityRepository
{
    final public function getFacultiesOfUniversity(?string $slug = null): array
    {
        if ($slug) {
            return $this->createQueryBuilder('organization')
                ->where('organization.type = :faculty')
                ->andWhere('organization.parent.type = :university')
                ->andWhere('organization.parent.slug = :slug')
                ->setParameter('faculty', 'faculty')
                ->setParameter('university', 'university')
                ->setParameter('slug', $slug)
                ->getQuery()
                ->execute([], Query::HYDRATE_OBJECT);
        }

        return $this->createQueryBuilder('organization')
            ->where('organization.type = :type')
            ->setParameter('type', 'faculty')
            ->getQuery()
            ->execute([], Query::HYDRATE_OBJECT);
    }
}
