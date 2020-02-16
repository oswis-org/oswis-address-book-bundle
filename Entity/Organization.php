<?php
/**
 * @noinspection PhpUnused
 * @noinspection MethodShouldBeFinalInspection
 */

namespace Zakjakub\OswisAddressBookBundle\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Zakjakub\OswisAddressBookBundle\Entity\AbstractClass\AbstractOrganization;
use Zakjakub\OswisCoreBundle\Entity\Nameable;
use Zakjakub\OswisCoreBundle\Filter\SearchAnnotation as Searchable;

/**
 * @Doctrine\ORM\Mapping\Entity(repositoryClass="Zakjakub\OswisAddressBookBundle\Repository\OrganizationRepository")
 * @Doctrine\ORM\Mapping\Table(name="address_book_organization")
 * @ApiResource(
 *   iri="http://schema.org/Organization",
 *   attributes={
 *     "filters"={"search"},
 *     "access_control"="is_granted('ROLE_MEMBER')"
 *   },
 *   collectionOperations={
 *     "get"={
 *       "access_control"="is_granted('ROLE_MEMBER')",
 *       "normalization_context"={"groups"={"address_book_organizations_get"}},
 *     },
 *     "post"={
 *       "access_control"="is_granted('ROLE_MEMBER')",
 *       "denormalization_context"={"groups"={"address_book_organizations_post"}}
 *     }
 *   },
 *   itemOperations={
 *     "get"={
 *       "access_control"="is_granted('ROLE_MEMBER')",
 *       "normalization_context"={"groups"={"address_book_organization_get"}},
 *     },
 *     "put"={
 *       "access_control"="is_granted('ROLE_MEMBER')",
 *       "denormalization_context"={"groups"={"address_book_organization_put"}}
 *     }
 *   }
 * )
 * @ApiFilter(OrderFilter::class)
 * @Searchable({
 *     "id",
 *     "slug",
 *     "name",
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
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\Organization",
     *     inversedBy="subOrganizations",
     *     cascade={"all"},
     *     fetch="EAGER"
     * )
     * @Doctrine\ORM\Mapping\JoinColumn(nullable=true)
     */
    protected ?Organization $parentOrganization = null;

    /**
     * @Doctrine\ORM\Mapping\OneToMany(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\Organization",
     *     mappedBy="parentOrganization"
     * )
     */
    protected ?Collection $subOrganizations = null;

    /**
     * @Doctrine\ORM\Mapping\OneToMany(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\Position",
     *     mappedBy="organization",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    protected ?Collection $positions = null;

    public function __construct(
        ?Nameable $nameable = null,
        ?string $identificationNumber = null,
        ?Organization $parentOrganization = null,
        ?string $color = null,
        ?string $type = self::ORGANIZATION,
        ?Collection $addresses = null,
        ?Collection $contactDetails = null,
        ?Collection $notes = null,
        ?Collection $addressBooks = null
    ) {
        parent::__construct($nameable, $identificationNumber, $color, $type, $notes, $contactDetails, $addresses, $addressBooks);
        $this->subOrganizations = new ArrayCollection();
        $this->setParentOrganization($parentOrganization);
    }

    public function addPosition(?Position $position): void
    {
        if ($position && !$this->positions->contains($position)) {
            $this->positions->add($position);
            $position->setOrganization($this);
        }
    }

    public function removePosition(?Position $position): void
    {
        if ($position && $this->positions->removeElement($position)) {
            $position->setOrganization(null);
            $position->setPerson(null);
        }
    }

    public function getStudents(?DateTimeInterface $dateTime = null, bool $recursive = false): Collection
    {
        $out = new ArrayCollection();
        $this->getStudyPositions($dateTime, $recursive)->map(
            fn(Position $p) => $out->contains($p->getPerson()) ? null : $out->add($p->getPerson())
        );

        return $out;
    }

    public function getMembers(?DateTimeInterface $dateTime = null, bool $recursive = false): Collection
    {
        $out = new ArrayCollection();
        $this->getMemberPositions($dateTime, $recursive)->map(
            fn(Position $p) => $out->contains($p->getPerson()) ? null : $out->add($p->getPerson())
        );

        return $out;
    }

    public function getMembersAndEmployees(?DateTimeInterface $dateTime = null, bool $recursive = false): Collection
    {
        $out = new ArrayCollection();
        $this->getMemberAndEmployeePositions($dateTime, $recursive)->map(
            fn(Position $p) => $out->contains($p->getPerson()) ? null : $out->add($p->getPerson())
        );

        return $out;
    }

    public function getEmployees(?DateTimeInterface $dateTime = null, bool $recursive = false): Collection
    {
        $out = new ArrayCollection();
        $this->getEmployeePositions($dateTime, $recursive)->map(
            fn(Position $p) => $out->contains($p->getPerson()) ? null : $out->add($p->getPerson())
        );

        return $out;
    }

    public function getManagers(?DateTimeInterface $dateTime = null, bool $recursive = false): Collection
    {
        $out = new ArrayCollection();
        $this->getManagerPositions($dateTime, $recursive)->map(
            fn(Position $p) => $out->contains($p->getPerson()) ? null : $out->add($p->getPerson())
        );

        return $out;
    }

    public function getContactPersons(?DateTimeInterface $dateTime = null, bool $onlyWithActivatedUser = false): Collection
    {
        $act = $onlyWithActivatedUser;
        $positions = $this->getPositions($dateTime ?? new DateTime());

        return $positions->filter(
            static function (Position $p) use ($act) {
                if ($act && (!$p->getPerson() || !$p->getPerson()->getAppUser() || !$p->getPerson()->getAppUser()->getAccountActivationDateTime())) {
                    return false;
                }

                return $p->getIsContactPerson();
            }
        );
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
        }
        // TODO: Check cycles!
    }

    public function removeSubOrganization(?Organization $organization): void
    {
        if ($organization && $this->subOrganizations->removeElement($organization)) {
            $organization->setParentOrganization(null);
        }
        // TODO: Check cycles!
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
        }
        // TODO: Check!
    }

    public function getIdentificationNumberFromParents(): ?string
    {
        return $this->getIdentificationNumber() ?? ($this->getParentOrganization() ? $this->getParentOrganization()->getIdentificationNumberFromParents() : null) ?? null;
    }
}
