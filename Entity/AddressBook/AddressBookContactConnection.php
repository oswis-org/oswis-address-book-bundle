<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Entity\AddressBook;

use OswisOrg\OswisAddressBookBundle\Entity\AbstractClass\AbstractContact;
use OswisOrg\OswisCoreBundle\Interfaces\Common\BasicInterface;
use OswisOrg\OswisCoreBundle\Traits\Common\BasicTrait;

/**
 * @Doctrine\ORM\Mapping\Entity()
 * @Doctrine\ORM\Mapping\Table(name="address_book_address_book_contact_connection")
 * @Doctrine\ORM\Mapping\Cache(usage="NONSTRICT_READ_WRITE", region="address_book_address_book")
 */
class AddressBookContactConnection implements BasicInterface
{
    use BasicTrait;

    /**
     * @Doctrine\ORM\Mapping\ManyToOne(
     *     targetEntity="OswisOrg\OswisAddressBookBundle\Entity\AbstractClass\AbstractContact",
     *     inversedBy="addressBookContactConnections"
     * )
     * @Doctrine\ORM\Mapping\JoinColumn(nullable=true)
     */
    protected ?AbstractContact $contact = null;

    /**
     * @Doctrine\ORM\Mapping\ManyToOne(
     *     targetEntity="OswisOrg\OswisAddressBookBundle\Entity\AddressBook\AddressBook",
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
