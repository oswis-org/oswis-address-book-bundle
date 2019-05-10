<?php

namespace Zakjakub\OswisAddressBookBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class OrganizationRepository extends EntityRepository
{
    final public function getFacultiesOfUniversity(?string $universitySlug = null): array
    {
        if ($universitySlug) {
            // Returning faculties of one university defined by slug.
            return $this->createQueryBuilder('organization')
                ->where('organization.type = :faculty')
                ->andWhere('organization.parent.type = :university')
                ->andWhere('organization.parent.slug = :slug')
                ->setParameter('faculty', 'faculty')
                ->setParameter('university', 'university')
                ->setParameter('slug', $universitySlug)
                ->getQuery()
                ->execute([], Query::HYDRATE_OBJECT);
        }

        // Returning faculties of one university defined by slug.
        return $this->createQueryBuilder('organization')
            ->where('organization.type = :type')
            ->setParameter('type', 'faculty')
            ->getQuery()
            ->execute([], Query::HYDRATE_OBJECT);
    }
}
