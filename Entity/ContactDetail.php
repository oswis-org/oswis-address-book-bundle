<?php
/**
 * @noinspection PhpUnused
 * @noinspection PropertyCanBePrivateInspection
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
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

#[Entity]
#[Table(name: 'address_book_contact_detail')]
#[Cache(usage: 'NONSTRICT_READ_WRITE', region: 'address_book_contact')]
class ContactDetail implements NameableInterface, PriorityInterface
{
    use NameableTrait;
    use PriorityTrait;
    use EntityPublicTrait;

    /** Type of this contact detail. */
    #[ManyToOne(targetEntity: ContactDetailCategory::class, fetch: 'EAGER')]
    #[JoinColumn(name: 'category_id', referencedColumnName: 'id')]
    protected ?ContactDetailCategory $detailCategory = null;

    #[ManyToOne(targetEntity: AbstractContact::class, inversedBy: 'details')]
    #[JoinColumn(name: 'contact_id', referencedColumnName: 'id')]
    protected ?AbstractContact $contact = null;

    /** Text content of note. */
    #[Column(type: 'text', nullable: true)]
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
    }

    public function getFormatted(): ?string
    {
        if (null !== $this->getDetailCategory()) {
            return $this->getDetailCategory()->getFormatted(filter_var($this->getContent(), FILTER_SANITIZE_URL) ?: null,
                htmlspecialchars($this->getDescription()), htmlspecialchars(''.$this->getName()),);
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
        if ($this->content && $this->isSpaceless()) {
            $this->content = preg_replace('/\s/', '', $this->content);
        }

        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content && $this->isSpaceless() ? preg_replace('/\s/', '', $content) : $content;
    }

    public function isSpaceless(): bool
    {
        return $this->getDetailCategory()?->isSpaceless() ?? false;
    }

    public function getSchemaString(): ?string
    {
        return $this->detailCategory?->getContactSchema();
    }

    public function getCategoryString(): ?string
    {
        return $this->detailCategory?->getType();
    }

    public function getType(): ?string
    {
        return $this->getDetailCategory()?->getType();
    }

    public function getCategoryName(): ?string
    {
        return $this->detailCategory?->getName();
    }

    public function getShowInPreview(): bool
    {
        return $this->detailCategory?->getShowInPreview() ?? false;
    }
}
