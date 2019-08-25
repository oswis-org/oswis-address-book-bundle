<?php

namespace Zakjakub\OswisAddressBookBundle\Entity\AddressBook;

use Zakjakub\OswisAddressBookBundle\Entity\AbstractClass\AbstractContact;
use Zakjakub\OswisCoreBundle\Traits\Entity\BasicEntityTrait;

/**
 * @Doctrine\ORM\Mapping\Entity()
 * @Doctrine\ORM\Mapping\Table(name="address_book_address_book_contact_connection")
 * @Doctrine\ORM\Mapping\Cache(usage="READ_WRITE", region="address_book_contact")
 */
class AddressBookContactConnection
{
    use BasicEntityTrait;

    /**
     * @var AbstractContact|null
     * @Doctrine\ORM\Mapping\ManyToOne(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\AbstractClass\AbstractContact",
     *     inversedBy="addressBookContactConnections",
     *     fetch="EAGER"
     * )
     * @Doctrine\ORM\Mapping\JoinColumn(nullable=true)
     */
    protected $contact;

    /**
     * @var AddressBook|null
     * @Doctrine\ORM\Mapping\ManyToOne(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\AddressBook\AddressBook",
     *     inversedBy="addressBookContactConnections",
     *     fetch="EAGER"
     * )
     * @Doctrine\ORM\Mapping\JoinColumn(nullable=true)
     */
    protected $addressBook;

    public function __construct(
        ?AddressBook $addressBook = null,
        ?AbstractContact $contact = null
    ) {
        $this->setAddressBook($addressBook);
        $this->setContact($contact);
    }

    final public function getAddressBook(): ?AddressBook
    {
        return $this->addressBook;
    }

    final public function setAddressBook(?AddressBook $addressBook): void
    {
        if ($this->addressBook && $addressBook !== $this->addressBook) {
            $this->addressBook->removeAddressBookContactConnection($this);
        }
        if ($addressBook && $this->addressBook !== $addressBook) {
            $this->addressBook = $addressBook;
            $addressBook->addAddressBookContactConnection($this);
        }
    }

    final public function getContact(): ?AbstractContact
    {
        return $this->contact;
    }

    final public function setContact(?AbstractContact $contact): void
    {
        if ($this->contact && $contact !== $this->contact) {
            $this->contact->removeAddressBookContactConnection($this);
        }
        if ($contact && $this->contact !== $contact) {
            $this->contact = $contact;
            $contact->addAddressBookContactConnection($this);
        }
    }
}
