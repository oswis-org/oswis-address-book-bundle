<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Repository;

use Doctrine\ORM\EntityRepository;
use OswisOrg\OswisAddressBookBundle\Entity\Person;

class PersonRepository extends EntityRepository
{
    public function findOneBy(array $criteria, array $orderBy = null): ?Person
    {
        $result = parent::findOneBy($criteria, $orderBy);

        return $result instanceof Person ? $result : null;
    }
}
