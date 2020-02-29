<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 * @noinspection PhpUnused
 */

namespace Zakjakub\OswisAddressBookBundle\Entity;

use Zakjakub\OswisAddressBookBundle\Entity\AbstractClass\AbstractAddress;
use Zakjakub\OswisAddressBookBundle\Entity\AbstractClass\AbstractContact;
use Zakjakub\OswisCoreBundle\Entity\Address;
use Zakjakub\OswisCoreBundle\Entity\Nameable;
use Zakjakub\OswisCoreBundle\Traits\Entity\PriorityTrait;

/**
 * @Doctrine\ORM\Mapping\Entity
 * @Doctrine\ORM\Mapping\Table(name="address_book_contact_address")
 * @Doctrine\ORM\Mapping\Cache(usage="NONSTRICT_READ_WRITE", region="address_book_contact")
 */
class ContactAddress extends AbstractAddress
{
    use PriorityTrait;

    /**
     * @Doctrine\ORM\Mapping\ManyToOne(targetEntity="Zakjakub\OswisAddressBookBundle\Entity\AbstractClass\AbstractContact", inversedBy="addresses")
     * @Doctrine\ORM\Mapping\JoinColumn(name="contact_id", referencedColumnName="id")
     */
    protected ?AbstractContact $contact = null;

    public function __construct(Nameable $nameable = null, Address $address = null, ?int $priority = null, ?AbstractContact $contact = null)
    {
        $this->setContact($contact);
        $this->setFieldsFromNameable($nameable);
        $this->setFieldsFromAddress($address);
        $this->setPriority($priority);
    }

    public function getContact(): ?AbstractContact
    {
        return $this->contact;
    }

    public function setContact(?AbstractContact $contact): void
    {
        if ($this->contact && $contact !== $this->contact) {
            $this->contact->removeAddress($this);
        }
        $this->contact = $contact;
        if ($contact && $this->contact !== $contact) {
            $contact->addAddress($this);
        }
    }
}
