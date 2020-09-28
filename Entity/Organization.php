<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use OswisOrg\OswisAddressBookBundle\Entity\AbstractClass\AbstractContact;
use OswisOrg\OswisAddressBookBundle\Entity\AbstractClass\AbstractOrganization;
use OswisOrg\OswisAddressBookBundle\Entity\MediaObject\ContactImage;
use OswisOrg\OswisCoreBundle\Entity\NonPersistent\Nameable;

/**
 * @Doctrine\ORM\Mapping\Entity(repositoryClass="OswisOrg\OswisAddressBookBundle\Repository\OrganizationRepository")
 * @Doctrine\ORM\Mapping\Table(name="address_book_organization")
 * @ApiPlatform\Core\Annotation\ApiResource(
 *   iri="http://schema.org/Organization",
 *   attributes={
 *     "filters"={"search"},
 *     "security"="is_granted('ROLE_MEMBER')"
 *   },
 *   collectionOperations={
 *     "get"={
 *       "security"="is_granted('ROLE_MEMBER')",
 *       "normalization_context"={"groups"={"entities_get", "address_book_abstract_contacts_get", "address_book_organizations_get"}},
 *     },
 *     "post"={
 *       "security"="is_granted('ROLE_MEMBER')",
 *       "denormalization_context"={"groups"={"entities_post", "address_book_abstract_contacts_post", "address_book_organizations_post"}}
 *     }
 *   },
 *   itemOperations={
 *     "get"={
 *       "security"="is_granted('ROLE_MEMBER')",
 *       "normalization_context"={"groups"={"entity_get", "address_book_abstract_contact_get", "address_book_organization_get"}},
 *     },
 *     "put"={
 *       "security"="is_granted('ROLE_MEMBER')",
 *       "denormalization_context"={"groups"={"entity_put", "address_book_abstract_contact_put", "address_book_organization_put"}}
 *     }
 *   }
 * )
 * @ApiPlatform\Core\Annotation\ApiFilter(ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter::class, properties={
 *     "id": "ASC",
 *     "slug",
 *     "description",
 *     "contactName",
 *     "sortableName",
 *     "shortName",
 *     "note",
 *     "identificationNumber"
 * })
 * @ApiPlatform\Core\Annotation\ApiFilter(ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter::class, properties={
 *     "id": "exact",
 *     "description": "partial",
 *     "slug": "partial",
 *     "contactName": "partial",
 *     "shortName": "partial",
 *     "note": "partial",
 *     "identificationNumber": "partial"
 * })
 * @OswisOrg\OswisCoreBundle\Filter\SearchAnnotation({
 *     "id",
 *     "slug",
 *     "contactName",
 *     "shortName",
 *     "description",
 *     "note",
 *     "parentOrganization.name",
 *     "identificationNumber"
 * })
 * @Doctrine\ORM\Mapping\Cache(usage="NONSTRICT_READ_WRITE", region="address_book_contact")
 */
class Organization extends AbstractOrganization
{
    /**
     * @Doctrine\ORM\Mapping\ManyToOne(
     *     targetEntity="OswisOrg\OswisAddressBookBundle\Entity\Organization", inversedBy="subOrganizations", fetch="EAGER"
     * )
     * @Doctrine\ORM\Mapping\JoinColumn(nullable=true)
     * @Symfony\Component\Serializer\Annotation\MaxDepth(3)
     */
    protected ?Organization $parentOrganization = null;

    /**
     * @Doctrine\ORM\Mapping\OneToMany(targetEntity="OswisOrg\OswisAddressBookBundle\Entity\Organization", mappedBy="parentOrganization")
     * @Symfony\Component\Serializer\Annotation\MaxDepth(3)
     */
    protected ?Collection $subOrganizations = null;

    /**
     * @Doctrine\ORM\Mapping\ManyToMany(targetEntity="OswisOrg\OswisAddressBookBundle\Entity\Person", fetch="EAGER")
     * @Doctrine\ORM\Mapping\JoinTable(
     *     name="address_book_organization_contact_person_connection",
     *     joinColumns={@Doctrine\ORM\Mapping\JoinColumn(name="organization_id", referencedColumnName="id")},
     *     inverseJoinColumns={@Doctrine\ORM\Mapping\JoinColumn(name="contact_person_id", referencedColumnName="id", unique=true)}
     * )
     */
    protected ?Collection $contactPersons = null;

    public function __construct(
        ?Nameable $nameable = null,
        ?string $identificationNumber = null,
        ?Organization $parentOrganization = null,
        ?string $type = self::TYPE_ORGANIZATION,
        ?Collection $addresses = null,
        ?Collection $details = null,
        ?Collection $notes = null,
        ?Collection $addressBooks = null
    ) {
        parent::__construct($nameable, $identificationNumber, $type ?? self::TYPE_ORGANIZATION, $notes, $details, $addresses, $addressBooks);
        $this->subOrganizations = new ArrayCollection();
        $this->contactPersons = new ArrayCollection();
        $this->setParentOrganization($parentOrganization);
    }

    public function getOneImage(?string $type = null, bool $recursive = false): ?ContactImage
    {
        $image = $this->getImages($type)->first();
        if ($image instanceof ContactImage) {
            return $image;
        }

        return true === $recursive && $this->getParentOrganization() ? $this->getParentOrganization()->getOneImage($type, true) : null;
    }

    public function getImages(?string $type = null): Collection
    {
        $images = $this->images ?? new ArrayCollection();

        return empty($type) ? $images : $images->filter(fn(ContactImage $eventImage) => $eventImage->getType() === $type);
    }

    public function getParentOrganization(): ?Organization
    {
        return $this->parentOrganization;
    }

    public function setParentOrganization(?Organization $organization): void
    {
        if ($this->parentOrganization && $organization !== $this->parentOrganization) {
            $this->parentOrganization->removeSubOrganization($this);
        }
        $this->parentOrganization = $organization;
        if ($this->parentOrganization) {
            $this->parentOrganization->addSubOrganization($this);
        } // TODO: Check!
    }

    public function addContactPerson(?AbstractContact $contact): void
    {
        if (null !== $contact && !$this->getContactPersons()->contains($contact)) {
            $this->getContactPersons()->add($contact);
        }
    }

    public function getContactPersons(bool $onlyWithActivatedUser = false): Collection
    {
        $contactPersons = $this->contactPersons ?? new ArrayCollection();
        if ($onlyWithActivatedUser) {
            $contactPersons = $contactPersons->filter(fn(Person $person) => $person->hasActivatedUser());
        }

        return $contactPersons;
    }

    public function removeContactPerson(?AbstractContact $contact): void
    {
        if (null !== $contact) {
            $this->getContactPersons()->remove($contact);
        }
    }

    public function isRoot(): bool
    {
        return $this->parentOrganization ? false : true;
    }

    public function addSubOrganization(?Organization $organization): void
    {
        if ($organization && !$this->subOrganizations->contains($organization)) {
            $this->subOrganizations->add($organization);
            $organization->setParentOrganization($this);
        } // TODO: Check cycles!
    }

    public function removeSubOrganization(?Organization $organization): void
    {
        if ($organization && $this->subOrganizations->removeElement($organization)) {
            $organization->setParentOrganization(null);
        }
    }

    public function filterSubOrganizationsByType(string $type): Collection
    {
        return $this->getSubOrganizations()->filter(fn(Organization $organization): bool => $type === $organization->getType());
    }

    public function getSubOrganizations(): Collection
    {
        return $this->subOrganizations ?? new ArrayCollection();
    }

    public function getPath(): string
    {
        return $this->getParentOrganization() ? '-&gt;'.$this->getParentOrganization()->getPath() : $this->getName();
    }

    public function getIdentificationNumberRecursive(): ?string
    {
        return $this->getIdentificationNumber() ?? ($this->getParentOrganization() ? $this->getParentOrganization()->getIdentificationNumberRecursive() : null) ?? null;
    }
}
