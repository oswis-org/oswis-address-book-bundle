<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Entity\AddressBook;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use OswisOrg\OswisCoreBundle\Interfaces\Common\BasicInterface;
use OswisOrg\OswisCoreBundle\Interfaces\Common\DeletedInterface;
use OswisOrg\OswisCoreBundle\Traits\Common\BasicTrait;
use OswisOrg\OswisCoreBundle\Traits\Common\DeletedTrait;

#[Entity]
#[Table(name: 'address_book_contact_address_book')]
#[Cache(usage: 'NONSTRICT_READ_WRITE', region: 'address_book_address_book')]
class ContactAddressBook implements BasicInterface, DeletedInterface
{
    use BasicTrait;
    use DeletedTrait;

    #[ManyToOne(targetEntity: AddressBook::class, fetch: 'EAGER')]
    #[JoinColumn(nullable: true)]
    protected ?AddressBook $addressBook = null;

    public function __construct(?AddressBook $addressBook = null)
    {
        $this->setAddressBook($addressBook);
    }

    public function getAddressBook(): ?AddressBook
    {
        return $this->addressBook;
    }

    public function setAddressBook(?AddressBook $addressBook): void
    {
        $this->addressBook = $addressBook;
    }
}
