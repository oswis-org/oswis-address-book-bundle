<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 */

namespace Zakjakub\OswisAddressBookBundle\Entity\AddressBook;

use Zakjakub\OswisAddressBookBundle\Entity\AbstractClass\AbstractContact;
use Zakjakub\OswisCoreBundle\Traits\Entity\BasicEntityTrait;

/**
 * @Doctrine\ORM\Mapping\Entity()
 * @Doctrine\ORM\Mapping\Table(name="address_book_address_book_contact_connection")
 * @Doctrine\ORM\Mapping\Cache(usage="NONSTRICT_READ_WRITE", region="address_book_address_book")
 */
class AddressBookContactConnection
{
    use BasicEntityTrait;

    /**
     * @Doctrine\ORM\Mapping\ManyToOne(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\AbstractClass\AbstractContact",
     *     inversedBy="addressBookContactConnections",
     *     fetch="EAGER"
     * )
     * @Doctrine\ORM\Mapping\JoinColumn(nullable=true)
     */
    protected ?AbstractContact $contact = null;

    /**
     * @Doctrine\ORM\Mapping\ManyToOne(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\AddressBook\AddressBook",
     *     inversedBy="addressBookContactConnections",
     *     fetch="EAGER"
     * )
     * @Doctrine\ORM\Mapping\JoinColumn(nullable=true)
     */
    protected ?AddressBook $addressBook = null;

    public function __construct(?AddressBook $addressBook = null, ?AbstractContact $contact = null)
    {
        $this->setAddressBook($addressBook);
        $this->setContact($contact);
    }

    public function getAddressBook(): ?AddressBook
    {
        return $this->addressBook;
    }

    public function setAddressBook(?AddressBook $addressBook): void
    {
        if ($this->addressBook && $addressBook !== $this->addressBook) {
            $this->addressBook->removeAddressBookContactConnection($this);
        }
        if ($addressBook && $this->addressBook !== $addressBook) {
            $this->addressBook = $addressBook;
            $addressBook->addAddressBookContactConnection($this);
        }
    }

    public function getContact(): ?AbstractContact
    {
        return $this->contact;
    }

    public function setContact(?AbstractContact $contact): void
    {
        if ($this->contact && $contact !== $this->contact) {
            $this->contact->removeAddressBookContactConnection($this);
        }
        $this->contact = $contact;
        if ($contact && $this->contact !== $contact) {
            $contact->addAddressBookContactConnection($this);
        }
    }
}
