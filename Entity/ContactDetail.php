<?php /** @noinspection PhpUnused */

namespace Zakjakub\OswisAddressBookBundle\Entity;

use Zakjakub\OswisCoreBundle\Traits\Entity\BasicEntityTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\DescriptionTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\PriorityTrait;
use function filter_var;
use function htmlspecialchars;

/**
 * @Doctrine\ORM\Mapping\Entity()
 * @Doctrine\ORM\Mapping\Table(name="address_book_contact_detail")
 * @Doctrine\ORM\Mapping\Cache(usage="NONSTRICT_READ_WRITE", region="address_book_contact")
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
     *     fetch="EAGER"
     * )
     * @Doctrine\ORM\Mapping\JoinColumn(name="type_id", referencedColumnName="id")
     */
    private ?ContactDetailType $contactType = null;

    /**
     * Text content of note.
     * @var string|null $content
     * @Doctrine\ORM\Mapping\Column(type="text", nullable=true)
     */
    private ?string $content = null;

    public function __construct(?ContactDetailType $contactType = null, ?string $content = null)
    {
        $this->setContactType($contactType);
        $this->setContent($content);
    }

    /**
     * @return null|string
     */
    final public function getFormatted(): ?string
    {
        return $this->getContactType() ? $this->getContactType()->getFormatted(filter_var($this->getContent(), FILTER_SANITIZE_URL), htmlspecialchars($this->getDescription())) : null;
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
        $this->contactType = $contactType;
    }

    /**
     * @return string
     */
    final public function getContent(): ?string
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

    final public function getSchemaString(): ?string
    {
        return $this->contactType ? $this->contactType->getContactSchema() : null;
    }

    final public function getTypeString(): ?string
    {
        return $this->contactType ? $this->contactType->getName() : null;
    }
}
