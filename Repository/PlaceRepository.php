<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use OswisOrg\OswisAddressBookBundle\Entity\Place;

class PlaceRepository extends ServiceEntityRepository
{
    /**
     * @param  ManagerRegistry  $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Place::class);
    }

    public function findOneBy(array $criteria, ?array $orderBy = null): ?Place
    {
        $result = parent::findOneBy($criteria, $orderBy);

        return $result instanceof Place ? $result : null;
    }
}
