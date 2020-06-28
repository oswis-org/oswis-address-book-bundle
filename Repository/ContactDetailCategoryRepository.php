<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 * @noinspection PhpUnused
 */

namespace OswisOrg\OswisAddressBookBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use LogicException;
use OswisOrg\OswisAddressBookBundle\Entity\ContactDetailCategory;

class ContactDetailCategoryRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     *
     * @throws LogicException
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContactDetailCategory::class);
    }

    public function findOneBy(array $criteria, array $orderBy = null): ?ContactDetailCategory
    {
        $contactDetailType = parent::findOneBy($criteria, $orderBy);

        return $contactDetailType instanceof ContactDetailCategory ? $contactDetailType : null;
    }
}
