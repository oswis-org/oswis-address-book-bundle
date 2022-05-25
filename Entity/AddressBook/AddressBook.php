<?php

namespace OswisOrg\OswisAddressBookBundle\Entity\AddressBook;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use OswisOrg\OswisAddressBookBundle\Repository\AddressBookRepository;
use OswisOrg\OswisCoreBundle\Entity\NonPersistent\Nameable;
use OswisOrg\OswisCoreBundle\Interfaces\Common\NameableInterface;
use OswisOrg\OswisCoreBundle\Traits\Common\NameableTrait;

#[Entity(repositoryClass: AddressBookRepository::class)]
#[Table(name: 'address_book_address_book')]
#[Cache(usage: 'NONSTRICT_READ_WRITE', region: 'address_book_address_book')]
class AddressBook implements NameableInterface
{
    use NameableTrait;

    public function __construct(?Nameable $nameable = null)
    {
        $this->setFieldsFromNameable($nameable);
    }
}
