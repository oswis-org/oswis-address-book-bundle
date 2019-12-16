<?php /** @noinspection PhpUnused */

namespace Zakjakub\OswisAddressBookBundle\Entity\AddressBook;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Zakjakub\OswisAddressBookBundle\Entity\AbstractClass\AbstractContact;
use Zakjakub\OswisCoreBundle\Entity\Nameable;
use Zakjakub\OswisCoreBundle\Traits\Entity\BasicEntityTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\NameableBasicTrait;
use function assert;

/**
 * @Doctrine\ORM\Mapping\Entity()
 * @Doctrine\ORM\Mapping\Table(name="address_book_address_book")
 * @Doctrine\ORM\Mapping\Cache(usage="NONSTRICT_READ_WRITE", region="address_book_address_book")
 */
class AddressBook
{
    use BasicEntityTrait;
    use NameableBasicTrait;

    /**
     * @var Collection|null
     * @Doctrine\ORM\Mapping\OneToMany(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\AddressBook\AddressBookContactConnection",
     *     cascade={"all"},
     *     mappedBy="addressBook",
     *     fetch="EAGER"
     * )
     */
    protected ?Collection $addressBookContactConnections = null;

    public function __construct(?Nameable $nameable = null)
    {
        $this->addressBookContactConnections = new ArrayCollection();
        $this->setFieldsFromNameable($nameable);
    }


    final public function addContact(AbstractContact $contact): void
    {
        if (null !== $contact && !$this->containsContact($contact)) {
            $this->addAddressBookContactConnection(new AddressBookContactConnection(null, $contact));
        }
    }

    final public function containsContact(AbstractContact $contact): bool
    {
        return $this->getContacts()->contains($contact);
    }

    final public function getContacts(): Collection
    {
        return $this->getAddressBookContactConnections()->map(
            fn(AddressBookContactConnection $addressBookContactConnection): AbstractContact => $addressBookContactConnection->getContact()
        );
    }

    final public function getAddressBookContactConnections(): Collection
    {
        return $this->addressBookContactConnections ?? new ArrayCollection();
    }

    final public function setAddressBookContactConnections(?Collection $newAddressBookContactConnections): void
    {
        $this->addressBookContactConnections ??= new ArrayCollection();
        $newAddressBookContactConnections ??= new ArrayCollection();
        foreach ($this->addressBookContactConnections as $oldAddressBookContactConnection) {
            if (!$newAddressBookContactConnections->contains($oldAddressBookContactConnection)) {
                $this->removeAddressBookContactConnection($oldAddressBookContactConnection);
            }
        }
        foreach ($newAddressBookContactConnections as $newAddressBookContactConnection) {
            if (!$this->addressBookContactConnections->contains($newAddressBookContactConnection)) {
                $this->addAddressBookContactConnection($newAddressBookContactConnection);
            }
        }
    }

    final public function addAddressBookContactConnection(?AddressBookContactConnection $addressBookContactConnection): void
    {
        $this->addressBookContactConnections ??= new ArrayCollection();
        if ($addressBookContactConnection && !$this->addressBookContactConnections->contains($addressBookContactConnection)) {
            $this->addressBookContactConnections->add($addressBookContactConnection);
            $addressBookContactConnection->setAddressBook($this);
        }
    }

    final public function removeAddressBookContactConnection(?AddressBookContactConnection $addressBookContactConnection): void
    {
        if ($addressBookContactConnection && $this->addressBookContactConnections->removeElement($addressBookContactConnection)) {
            $addressBookContactConnection->setAddressBook(null);
        }
    }

    final public function removeContact(AbstractContact $contact): void
    {
        foreach ($this->getAddressBookContactConnections() as $addressBookContactConnection) {
            assert($addressBookContactConnection instanceof AddressBookContactConnection);
            if ($addressBookContactConnection->getContact() && $contact->getId() === $addressBookContactConnection->getContact()->getId()) {
                $this->removeAddressBookContactConnection($addressBookContactConnection);
            }
        }
    }

    final public function destroyRevisions(): void
    {
    }
}
