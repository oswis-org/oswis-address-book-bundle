<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 * @noinspection PhpUnused
 */

namespace Zakjakub\OswisAddressBookBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Zakjakub\OswisAddressBookBundle\Entity\AddressBook\AddressBook;

class PersonRepository extends EntityRepository
{
    public function findOneBy(array $criteria, array $orderBy = null): ?AddressBook
    {
        $addressBook = parent::findOneBy($criteria, $orderBy);

        return $addressBook instanceof AddressBook ? $addressBook : null;
    }
}
