<?php
/**
 * @noinspection PhpUnused
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use OswisOrg\OswisAddressBookBundle\Entity\AbstractClass\AbstractAddress;
use OswisOrg\OswisAddressBookBundle\Entity\AbstractClass\AbstractContact;
use OswisOrg\OswisCoreBundle\Entity\NonPersistent\Nameable;
use OswisOrg\OswisCoreBundle\Entity\NonPersistent\PostalAddress;
use OswisOrg\OswisCoreBundle\Interfaces\Common\PriorityInterface;
use OswisOrg\OswisCoreBundle\Traits\Common\PriorityTrait;

#[Entity]
#[Table(name: 'address_book_contact_address')]
#[Cache(usage: 'NONSTRICT_READ_WRITE', region: 'address_book_contact')]
class ContactAddress extends AbstractAddress implements PriorityInterface
{
    use PriorityTrait;

    #[ManyToOne(targetEntity: AbstractContact::class, inversedBy: 'addresses')]
    #[JoinColumn(name: 'contact_id', referencedColumnName: 'id')]
    protected ?AbstractContact $contact = null;

    public function __construct(Nameable $nameable = null, PostalAddress $address = null, ?int $priority = null, ?AbstractContact $contact = null)
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
    }
}
