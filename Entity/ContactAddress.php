<?php /** @noinspection PhpUnused */

namespace Zakjakub\OswisAddressBookBundle\Entity;

use Zakjakub\OswisAddressBookBundle\Entity\AbstractClass\AbstractAddress;
use Zakjakub\OswisCoreBundle\Traits\Entity\PriorityTrait;

/**
 * @Doctrine\ORM\Mapping\Entity
 * @Doctrine\ORM\Mapping\Table(name="address_book_contact_address")
 * @Doctrine\ORM\Mapping\Cache(usage="NONSTRICT_READ_WRITE", region="address_book_contact")
 */
class ContactAddress extends AbstractAddress
{
    use PriorityTrait;
}
