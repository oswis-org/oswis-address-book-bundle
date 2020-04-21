<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 * @noinspection PhpUnused
 */

namespace OswisOrg\OswisAddressBookBundle\Repository;

use Doctrine\ORM\EntityRepository;
use OswisOrg\OswisAddressBookBundle\Entity\AddressBook\AddressBook;

class AddressBookRepository extends EntityRepository
{
    public function findOneBy(array $criteria, array $orderBy = null): ?AddressBook
    {
        $addressBook = parent::findOneBy($criteria, $orderBy);

        return $addressBook instanceof AddressBook ? $addressBook : null;
    }
}
