<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 * @noinspection PhpUnused
 */

namespace OswisOrg\OswisAddressBookBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use LogicException;
use OswisOrg\OswisAddressBookBundle\Entity\ContactDetailType;

class ContactDetailTypeRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     *
     * @throws LogicException
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContactDetailType::class);
    }

    public function findOneBy(array $criteria, array $orderBy = null): ?ContactDetailType
    {
        $contactDetailType = parent::findOneBy($criteria, $orderBy);

        return $contactDetailType instanceof ContactDetailType ? $contactDetailType : null;
    }
}
