<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Entity;

use OswisOrg\OswisAddressBookBundle\Entity\AbstractClass\AbstractContact;
use OswisOrg\OswisCoreBundle\Entity\NonPersistent\Nameable;
use OswisOrg\OswisCoreBundle\Entity\NonPersistent\Publicity;
use OswisOrg\OswisCoreBundle\Interfaces\Common\NameableInterface;
use OswisOrg\OswisCoreBundle\Interfaces\Common\PriorityInterface;
use OswisOrg\OswisCoreBundle\Traits\Common\EntityPublicTrait;
use OswisOrg\OswisCoreBundle\Traits\Common\NameableTrait;
use OswisOrg\OswisCoreBundle\Traits\Common\PriorityTrait;
use function filter_var;
use function htmlspecialchars;

/**
 * @Doctrine\ORM\Mapping\Entity()
 * @Doctrine\ORM\Mapping\Table(name="address_book_contact_detail")
 * @Doctrine\ORM\Mapping\Cache(usage="NONSTRICT_READ_WRITE", region="address_book_contact")
 */
class ContactDetail implements NameableInterface, PriorityInterface
{
    use NameableTrait;
    use PriorityTrait;
    use EntityPublicTrait;

    /**
     * Type of this contact detail.
     * @Doctrine\ORM\Mapping\ManyToOne(targetEntity="OswisOrg\OswisAddressBookBundle\Entity\ContactDetailCategory", fetch="EAGER")
     * @Doctrine\ORM\Mapping\JoinColumn(name="category_id", referencedColumnName="id")
     */
    protected ?ContactDetailCategory $detailCategory = null;

    /**
     * @Doctrine\ORM\Mapping\ManyToOne(targetEntity="OswisOrg\OswisAddressBookBundle\Entity\AbstractClass\AbstractContact", inversedBy="details")
     * @Doctrine\ORM\Mapping\JoinColumn(name="contact_id", referencedColumnName="id")
     */
    protected ?AbstractContact $contact = null;

    /**
     * Text content of note.
     * @Doctrine\ORM\Mapping\Column(type="text", nullable=true)
     */
    protected ?string $content = null;

    public function __construct(
        ?ContactDetailCategory $category = null,
        ?string $content = null,
        ?Nameable $nameable = null,
        ?Publicity $publicity = null,
        ?AbstractContact $contact = null
    ) {
        $this->setContact($contact);
        $this->setDetailCategory($category);
        $this->setContent($content);
        $this->setFieldsFromNameable($nameable);
        $this->setFieldsFromPublicity($publicity);
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
        if (null !== $this->getDetailCategory()) {
            return $this->getDetailCategory()->getFormatted(
                filter_var($this->getContent(), FILTER_SANITIZE_URL).'',
                htmlspecialchars($this->getDescription()).null,
                htmlspecialchars($this->getName()).null,
            );
        }

        return $this->getContent();
    }

    public function getDetailCategory(): ?ContactDetailCategory
    {
        return $this->detailCategory;
    }

    public function setDetailCategory(?ContactDetailCategory $detailCategory): void
    {
        $this->detailCategory = $detailCategory;
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
        return $this->detailCategory ? $this->detailCategory->getContactSchema() : null;
    }

    public function getCategoryString(): ?string
    {
        return $this->detailCategory ? $this->detailCategory->getType() : null;
    }

    public function getCategoryName(): ?string
    {
        return $this->detailCategory ? $this->detailCategory->getName() : null;
    }

    public function getShowInPreview(): bool
    {
        return $this->detailCategory ? $this->detailCategory->getShowInPreview() : false;
    }
}
