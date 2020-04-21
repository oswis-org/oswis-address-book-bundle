<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 * @noinspection PhpUnused
 */

namespace OswisOrg\OswisAddressBookBundle\Repository;

use Doctrine\ORM\EntityRepository;
use OswisOrg\OswisAddressBookBundle\Entity\ContactDetailType;

class ContactDetailTypeRepository extends EntityRepository
{
    public function findOneBy(array $criteria, array $orderBy = null): ?ContactDetailType
    {
        $contactDetailType = parent::findOneBy($criteria, $orderBy);

        return $contactDetailType instanceof ContactDetailType ? $contactDetailType : null;
    }
}
