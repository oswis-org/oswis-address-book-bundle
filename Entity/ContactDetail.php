<?php

namespace Zakjakub\OswisAddressBookBundle\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Zakjakub\OswisAddressBookBundle\Entity\AbstractClass\AbstractContact;
use Zakjakub\OswisCoreBundle\Traits\Entity\BasicEntityTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\DescriptionTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\PriorityTrait;

/**
 * @Doctrine\ORM\Mapping\Entity()
 * @Doctrine\ORM\Mapping\Table(name="address_book_contact_detail")
 * @ApiResource()
 */
class ContactDetail
{
    use BasicEntityTrait;
    use DescriptionTrait;
    use PriorityTrait;

    /**
     * Type of this contact.
     * @var ContactDetailType|null $contactType
     * @Doctrine\ORM\Mapping\ManyToOne(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\ContactDetailType",
     *     inversedBy="contacts"
     * )
     * @Doctrine\ORM\Mapping\JoinColumn(name="type_id", referencedColumnName="id")
     */
    private $contactType;

    /**
     * Text content of note.
     * @var string|null $content
     * @Doctrine\ORM\Mapping\Column(type="text", nullable=true)
     */
    private $content;

    /**
     * Contact, that this contact belongs to.
     * @var AbstractContact|null $contact
     * @Doctrine\ORM\Mapping\ManyToOne(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\AbstractClass\AbstractContact",
     *     inversedBy="contactDetails"
     * )
     * @Doctrine\ORM\Mapping\JoinColumn(name="contact_id", referencedColumnName="id")
     */
    private $contact;

    /**
     * ContactDetail constructor.
     *
     * @param ContactDetailType|null $contactType
     * @param string|null            $content
     * @param AbstractContact|null   $contact
     */
    public function __construct(
        ?ContactDetailType $contactType = null,
        ?string $content = null,
        ?AbstractContact $contact = null
    ) {
        $this->contactType = $contactType;
        $this->content = $content;
        $this->contact = $contact;
    }


    /**
     * @return null|string
     */
    final public function getFormatted(): ?string
    {
        $value = \filter_var($this->getContent(), FILTER_SANITIZE_URL);
        $description = \htmlspecialchars($this->getDescription());

        if (!$this->getContactType()) {
            return null;
        }

        return $this->getContactType()->getFormatted($value, $description);
    }

    /**
     * @return string
     */
    final public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    final public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    /**
     * @return ContactDetailType|null
     */
    final public function getContactType(): ?ContactDetailType
    {
        return $this->contactType;
    }

    /**
     * @param ContactDetailType|null $contactType
     */
    final public function setContactType(?ContactDetailType $contactType): void
    {
        if ($this->contactType && $contactType !== $this->contactType) {
            $this->contactType->removeContact($this);
        }
        $this->contactType = $contactType;
        if ($contactType && $this->contactType !== $contactType) {
            $contactType->addContact($this);
        }
    }

    /**
     * @return null|string
     */
    final public function getSchemaString(): ?string
    {
        if ($this->contactType) {
            return $this->contactType->getContactSchema();
        }

        return null;
    }

    /**
     * @return null|string
     */
    final public function getTypeString(): ?string
    {
        if ($this->contactType) {
            return $this->contactType->getName();
        }

        return null;
    }

    /**
     * @return AbstractContact|null
     */
    final public function getContact(): ?AbstractContact
    {
        return $this->contact;
    }

    /**
     * @param AbstractContact $contact
     */
    final public function setContact(AbstractContact $contact): void
    {
        if (null !== $this->contact && $contact !== $this->contact) {
            $this->contact->removeContactDetail($this);
        }
        $this->contact = $contact;
        if ($contact && $this->contact !== $contact) {
            $contact->addContactDetail($this);
        }
    }
}
