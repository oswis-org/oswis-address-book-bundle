<?php
/**
 * @noinspection PhpUnused
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use OswisOrg\OswisAddressBookBundle\Entity\AbstractClass\AbstractContact;
use OswisOrg\OswisAddressBookBundle\Entity\AbstractClass\AbstractOrganization;
use OswisOrg\OswisAddressBookBundle\Entity\MediaObject\ContactImage;
use OswisOrg\OswisAddressBookBundle\Repository\OrganizationRepository;
use OswisOrg\OswisCoreBundle\Entity\NonPersistent\Nameable;
use OswisOrg\OswisCoreBundle\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
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
 */
#[Entity(repositoryClass: OrganizationRepository::class)]
#[Table(name: 'address_book_organization')]
#[Cache(usage: 'NONSTRICT_READ_WRITE', region: 'address_book_contact')]
#[ApiFilter(OrderFilter::class, properties: [
    "id" => "ASC",
    "slug",
    "description",
    "contactName",
    "sortableName",
    "shortName",
    "note",
    "identificationNumber",
])]
#[ApiFilter(SearchFilter::class, properties: [
    "id"                   => "exact",
    "description"          => "partial",
    "slug"                 => "partial",
    "contactName"          => "partial",
    "shortName"            => "partial",
    "note"                 => "partial",
    "identificationNumber" => "partial",
])]
class Organization extends AbstractOrganization
{

    #[ManyToOne(targetEntity: self::class, fetch: 'EAGER', inversedBy: 'subOrganizations')]
    #[JoinColumn(nullable: true)]
    #[MaxDepth(3)]
    protected ?Organization $parentOrganization = null;

    #[OneToMany(mappedBy: 'parentOrganization', targetEntity: self::class)]
    #[MaxDepth(3)]
    protected ?Collection $subOrganizations = null;

    #[ManyToMany(targetEntity: Person::class, fetch: 'EAGER')]
    #[JoinTable(name: 'address_book_organization_contact_person_connection', joinColumns: [
        new JoinColumn(name: 'organization_id', referencedColumnName: 'id'),
    ], inverseJoinColumns: [new JoinColumn(name: 'contact_person_id', referencedColumnName: 'id', unique: true)])]
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

        return true === $recursive ? $this->getParentOrganization()?->getOneImage($type, true) : null;
    }

    public function getImages(?string $type = null): Collection
    {
        $images = $this->images;
        if (!empty($type)) {
            $images = $images->filter(fn(mixed $image) => $image instanceof ContactImage && $image->getType() === $type);
        }

        /** @var Collection<ContactImage> $images */
        return $images;
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
        $this->parentOrganization?->addSubOrganization($this);
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
            $contactPersons = $contactPersons->filter(fn(mixed $person) => $person instanceof Person && $person->hasActivatedUser());
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
        return !$this->parentOrganization;
    }

    public function addSubOrganization(?Organization $organization): void
    {
        if ($organization && !$this->getSubOrganizations()->contains($organization)) {
            $this->getSubOrganizations()->add($organization);
            $organization->setParentOrganization($this);
        }
    }

    public function removeSubOrganization(?Organization $organization): void
    {
        if ($organization && $this->getSubOrganizations()->removeElement($organization)) {
            $organization->setParentOrganization(null);
        }
    }

    public function filterSubOrganizationsByType(string $type): Collection
    {
        return $this->getSubOrganizations()->filter(fn(mixed $organization): bool => $organization instanceof Organization
                                                                                     && $type === $organization->getType());
    }

    public function getSubOrganizations(): Collection
    {
        return $this->subOrganizations ?? new ArrayCollection();
    }

    public function getPath(): string
    {
        return $this->getParentOrganization() ? '-&gt;'.$this->getParentOrganization()->getPath() : $this->getName().'';
    }

    public function getIdentificationNumberRecursive(): ?string
    {
        return $this->getIdentificationNumber() ?? $this->getParentOrganization()?->getIdentificationNumberRecursive() ?? null;
    }
}
