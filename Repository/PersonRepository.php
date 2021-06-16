<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use LogicException;
use OswisOrg\OswisAddressBookBundle\Entity\Person;

class PersonRepository extends ServiceEntityRepository
{
    /**
     * @param  ManagerRegistry  $registry
     *
     * @throws LogicException
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Person::class);
    }

    public function findOneBy(array $criteria, array $orderBy = null): ?Person
    {
        $result = parent::findOneBy($criteria, $orderBy);

        return $result instanceof Person ? $result : null;
    }
}
