<?php
/**
 * @noinspection PhpUnused
 * @noinspection MethodShouldBeFinalInspection
 */

namespace Zakjakub\OswisAddressBookBundle\Entity;

use Zakjakub\OswisAddressBookBundle\Entity\AbstractClass\AbstractContact;
use Zakjakub\OswisCoreBundle\Entity\Nameable;
use Zakjakub\OswisCoreBundle\Interfaces\BasicEntityInterface;
use Zakjakub\OswisCoreBundle\Traits\Entity\BasicEntityTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\NameableBasicTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\PriorityTrait;
use function filter_var;
use function htmlspecialchars;

/**
 * @Doctrine\ORM\Mapping\Entity()
 * @Doctrine\ORM\Mapping\Table(name="address_book_contact_detail")
 * @Doctrine\ORM\Mapping\Cache(usage="NONSTRICT_READ_WRITE", region="address_book_contact")
 */
class ContactDetail implements BasicEntityInterface
{
    use BasicEntityTrait;
    use PriorityTrait;
    use NameableBasicTrait;

    /**
     * Type of this contact.
     * @Doctrine\ORM\Mapping\ManyToOne(targetEntity="Zakjakub\OswisAddressBookBundle\Entity\ContactDetailType", fetch="EAGER")
     * @Doctrine\ORM\Mapping\JoinColumn(name="type_id", referencedColumnName="id")
     */
    protected ?ContactDetailType $detailType = null;

    /**
     * @Doctrine\ORM\Mapping\ManyToOne(targetEntity="Zakjakub\OswisAddressBookBundle\Entity\AbstractClass\AbstractContact", inversedBy="details")
     * @Doctrine\ORM\Mapping\JoinColumn(name="contact_id", referencedColumnName="id")
     */
    protected ?AbstractContact $contact = null;

    /**
     * Text content of note.
     * @Doctrine\ORM\Mapping\Column(type="text", nullable=true)
     */
    protected ?string $content = null;

    public function __construct(?ContactDetailType $type = null, ?string $content = null, ?Nameable $nameable = null, ?AbstractContact $contact = null)
    {
        $this->setContact($contact);
        $this->setDetailType($type);
        $this->setContent($content);
        $this->setFieldsFromNameable($nameable);
    }

    public function getContact(): ?AbstractContact
    {
        return $this->contact;
    }

    public function setContact(?AbstractContact $contact): void
    {
        if ($this->contact && $contact !== $this->contact) {
            $this->contact->removeDetail($this);
        }
        $this->contact = $contact;
        if ($contact && $this->contact !== $contact) {
            $contact->addDetail($this);
        }
    }

    public function getFormatted(): ?string
    {
        if (null !== $this->getDetailType()) {
            return $this->getDetailType()->getFormatted(
                filter_var($this->getContent(), FILTER_SANITIZE_URL).'',
                htmlspecialchars($this->getDescription()).null,
                htmlspecialchars($this->getName()).null,
                );
        }

        return $this->getContent();
    }

    public function getDetailType(): ?ContactDetailType
    {
        return $this->detailType;
    }

    public function setDetailType(?ContactDetailType $detailType): void
    {
        $this->detailType = $detailType;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function getSchemaString(): ?string
    {
        return $this->detailType ? $this->detailType->getContactSchema() : null;
    }

    public function getTypeString(): ?string
    {
        return $this->detailType ? $this->detailType->getType() : null;
    }

    public function getTypeName(): ?string
    {
        return $this->detailType ? $this->detailType->getName() : null;
    }

    public function getShowInPreview(): bool
    {
        return $this->detailType ? $this->detailType->getShowInPreview() : false;
    }
}
