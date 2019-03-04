<?php

namespace Zakjakub\OswisAddressBookBundle\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Zakjakub\OswisCoreBundle\Traits\Entity\BasicEntityTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\NameableBasicTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\PriorityTrait;

/**
 * Class ContactType
 * @Doctrine\ORM\Mapping\Entity
 * @Doctrine\ORM\Mapping\Table(name="address_book_contact_detail_type")
 * @ApiResource()
 * @ApiFilter(OrderFilter::class)
 * @ApiFilter(SearchFilter::class, properties={"id": "exact", "name": "ipartial"})
 */
class ContactDetailType
{
    public const ALLOWED_TYPES = [
        'url'       => ['name' => 'URL'],
        'email'     => ['name' => 'E-mail'],
        'phone'     => ['name' => 'Telefon'],
        'social'    => ['name' => 'Profil na sociální síti'],
        'messenger' => ['name' => 'Internetový komunikátor'],
        'voip'      => ['name' => 'Internetová telefonie'],
    ];

    use BasicEntityTrait;
    use NameableBasicTrait;
    use PriorityTrait;

    /**
     * @var Collection|null $contacts Contacts of this type
     * @Doctrine\ORM\Mapping\OneToMany(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\ContactDetail",
     *     mappedBy="contactType",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    protected $contacts;

    /**
     * @var string|null $schema Schema of type of contact
     * @Doctrine\ORM\Mapping\Column(type="string", nullable=true)
     */
    protected $schema;

    /**
     * Show in address book preview?
     *
     * @var bool|null $showInPreview
     * @Doctrine\ORM\Mapping\Column(type="boolean", nullable=true)
     */
    protected $showInPreview;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    protected $type;

    /**
     * @return bool|null
     */
    final public function getShowInPreview(): ?bool
    {
        return $this->showInPreview;
    }

    /**
     * @param bool|null $showInPreview
     */
    final public function setShowInPreview(?bool $showInPreview): void
    {
        $this->showInPreview = $showInPreview;
    }

    /**
     * @return array
     * @throws \InvalidArgumentException
     */
    final public function getTypeAsArray(): array
    {
        $this->checkType();
        if ($this->getType() && \array_key_exists($this->getType(), self::ALLOWED_TYPES)) {
            return self::ALLOWED_TYPES[$this->getType()];
        }

        return null;
    }

    /**
     * @param string|null $type
     *
     * @throws \InvalidArgumentException
     */
    final public function checkType(?string $type = null): void
    {
        $type = $type ?? $this->type;
        if (!$type || \array_key_exists($type, self::ALLOWED_TYPES)) {
            return;
        }
        throw new \InvalidArgumentException("Typ $type není povoleným typem akce.");
    }

    /**
     * @return string|null
     * @throws \InvalidArgumentException
     */
    final public function getType(): ?string
    {
        $this->checkType();

        return $this->type;
    }

    /**
     * @param string|null $type
     *
     * @throws \InvalidArgumentException
     */
    final public function setType(?string $type): void
    {
        $this->checkType($type);
        $this->type = $type;
    }

    final public function addContact(?ContactDetail $contact): void
    {
        if ($contact && !$this->contacts->contains($contact)) {
            $this->contacts->add($contact);
            $contact->setContactType($this);
        }
    }

    final public function removeContact(?ContactDetail $contact): void
    {
        if ($contact && $this->contacts->removeElement($contact)) {
            $contact->setContactType(null);
        }
    }

    /**
     * @return Collection
     */
    final public function getContacts(): Collection
    {
        return $this->contacts;
    }

    final public function getFormatted(string $value, ?string $description): string
    {
        return strtr($this->getSchema(), array('$<value>' => $value, '$<description>' => $description));
    }

    /**
     * Get schema of contact detail.
     * @return string
     */
    final public function getSchema(): string
    {
        return $this->schema;
    }

    /**
     * Set schema of contact detail.
     *
     * @param string $schema
     */
    final public function setSchema(string $schema): void
    {
        $this->schema = $schema;
    }
}
