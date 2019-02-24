<?php

namespace Zakjakub\OswisAddressBookBundle\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\Common\Collections\Collection;
use Zakjakub\OswisCoreBundle\Traits\Entity\BasicEntityTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\NameableBasicTrait;

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

    use NameableBasicTrait;

    /**
     * @var Collection|null $contacts Contacts of this type
     * @Doctrine\ORM\Mapping\OneToMany(targetEntity="ContactDetail", mappedBy="contactType", cascade={"all"}, orphanRemoval=true)
     */
    private $contacts;

    /**
     * @var string|null $schema Schema of type of contact
     * @Doctrine\ORM\Mapping\Column(type="string", nullable=true)
     */
    private $schema;

    /**
     * Show in address book preview?
     *
     * @var bool|null $showInPreview
     * @Doctrine\ORM\Mapping\Column(type="boolean", nullable=true)
     */
    private $showInPreview;


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
