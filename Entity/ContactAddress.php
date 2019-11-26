<?php /** @noinspection PhpUnused */

namespace Zakjakub\OswisAddressBookBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Zakjakub\OswisAddressBookBundle\Entity\AbstractClass\AbstractAddress;
use Zakjakub\OswisAddressBookBundle\Entity\AbstractClass\AbstractContact;
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
     * @var AbstractContact|null $contact Contact, that this address belongs to
     * @Doctrine\ORM\Mapping\ManyToOne(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\AbstractClass\AbstractContact",
     *     inversedBy="addresses"
     * )
     * @Doctrine\ORM\Mapping\JoinColumn(name="contact_id", referencedColumnName="id")
     * @Assert\NotNull
     */
    private ?AbstractContact $contact;

    /**
     * @return AbstractContact
     */
    final public function getContact(): ?AbstractContact
    {
        return $this->contact;
    }

    /**
     * @param AbstractContact $contact
     */
    final public function setContact(?AbstractContact $contact): void
    {
        if (null !== $this->contact) {
            $this->contact->removeAddress($this);
        }
        $this->contact = $contact;
        if ($contact && $this->contact !== $contact) {
            $contact->addAddress($this);
        }
    }
}
