<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use LogicException;
use OswisOrg\OswisAddressBookBundle\Entity\AddressBook\AddressBook;

class AddressBookRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     *
     * @throws LogicException
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AddressBook::class);
    }

    public function findOneBy(array $criteria, array $orderBy = null): ?AddressBook
    {
        $addressBook = parent::findOneBy($criteria, $orderBy);

        return $addressBook instanceof AddressBook ? $addressBook : null;
    }
}
