<?php

namespace Zakjakub\OswisAddressBookBundle\Entity\AddressBook;

use Zakjakub\OswisCoreBundle\Traits\Entity\BasicEntityTrait;

/**
 * @Doctrine\ORM\Mapping\Entity(repositoryClass="Zakjakub\OswisAddressBookBundle\Repository\AddressBookRevisionRepository")
 * @Doctrine\ORM\Mapping\Table(name="address_book_address_book_revision")
 * @Doctrine\ORM\Mapping\Cache(usage="NONSTRICT_READ_WRITE", region="address_book_address_book")
 */
class AddressBookRevision
{
    use BasicEntityTrait;
}
