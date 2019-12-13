<?php /** @noinspection PhpUnused */

namespace Zakjakub\OswisAddressBookBundle\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Zakjakub\OswisAddressBookBundle\Entity\AbstractClass\AbstractOrganization;
use Zakjakub\OswisCoreBundle\Entity\Nameable;
use Zakjakub\OswisCoreBundle\Filter\SearchAnnotation as Searchable;
use function assert;

/**
 * @Doctrine\ORM\Mapping\Entity(repositoryClass="Zakjakub\OswisAddressBookBundle\Repository\OrganizationRepository")
 * @Doctrine\ORM\Mapping\Table(name="address_book_organization")
 * @ApiResource(
 *   iri="http://schema.org/Organization",
 *   attributes={
 *     "filters"={"search"},
 *     "access_control"="is_granted('ROLE_MANAGER')"
 *   },
 *   collectionOperations={
 *     "get"={
 *       "access_control"="is_granted('ROLE_MANAGER')",
 *       "normalization_context"={"groups"={"address_book_organizations_get"}},
 *     },
 *     "post"={
 *       "access_control"="is_granted('ROLE_MANAGER')",
 *       "denormalization_context"={"groups"={"address_book_organizations_post"}}
 *     }
 *   },
 *   itemOperations={
 *     "get"={
 *       "access_control"="is_granted('ROLE_MANAGER')",
 *       "normalization_context"={"groups"={"address_book_organization_get"}},
 *     },
 *     "put"={
 *       "access_control"="is_granted('ROLE_MANAGER')",
 *       "denormalization_context"={"groups"={"address_book_organization_put"}}
 *     },
 *     "delete"={
 *       "access_control"="is_granted('ROLE_ADMIN')",
 *       "denormalization_context"={"groups"={"address_book_organization_delete"}}
 *     }
 *   }
 * )
 * @ApiFilter(OrderFilter::class)
 * @Searchable({
 *     "id",
 *     "name",
 *     "description",
 *     "note"
 * })
 * @Doctrine\ORM\Mapping\Cache(usage="NONSTRICT_READ_WRITE", region="address_book_contact")
 */
class Organization extends AbstractOrganization
{
    /**
     * @var Organization|null $parentOrganization Parent organization (if this is not top level org)
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
     * @var Collection|null $subOrganizations Sub organizations
     * @Doctrine\ORM\Mapping\OneToMany(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\Organization",
     *     mappedBy="parentOrganization"
     * )
     */
    protected ?Collection $subOrganizations = null;

    /**
     * @var Collection|null $positions Positions
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
        $this->positions = new ArrayCollection();
        $this->subOrganizations = new ArrayCollection();
        $this->setParentOrganization($parentOrganization);
    }

    final public function addPosition(?Position $position): void
    {
        if ($position && !$this->positions->contains($position)) {
            $this->positions->add($position);
            $position->setOrganization($this);
        }
    }

    final public function removePosition(?Position $position): void
    {
        if ($position && $this->positions->removeElement($position)) {
            $position->setOrganization(null);
            $position->setPerson(null);
        }
    }

    final public function getDirectStudents(): Collection
    {
        $students = new ArrayCollection();
        if ($this->isSchool()) {
            foreach ($this->getDirectStudies() as $study) {
                assert($study instanceof Position);
                if (!$students->contains($study->getPerson())) {
                    $students->add($study->getPerson());
                }
            }
        }

        return $students;
    }

    /**
     * Returns positions marked as study.
     *
     * @param DateTime|null $referenceDateTime
     *
     * @return Collection
     */
    final public function getDirectStudies(?DateTime $referenceDateTime = null): Collection
    {
        if (!$this->isSchool()) {
            return new ArrayCollection();
        }

        return $this->getPositions($referenceDateTime)->filter(fn(Position $position): bool => $position->isStudy());
    }

    /**
     * Returns positions in organization. If referenceDateTime is specified, only valid positions (in that datetime) are returned.
     *
     * @param DateTime|null $referenceDateTime
     *
     * @return Collection
     */
    final public function getPositions(?DateTime $referenceDateTime = null): Collection
    {
        $positions = $this->positions ?? new ArrayCollection();

        return $referenceDateTime ? $positions->filter(fn(Position $p): bool => $p->containsDateTimeInRange($referenceDateTime)) : $positions;
    }

    final public function getContactPersons(?DateTime $dateTime = null, bool $onlyWithActivatedUser = false): Collection
    {
        $onAc = $onlyWithActivatedUser;

        return $this->getPositions($dateTime ?? new DateTime())->filter(
            fn(Position $p) => ($onAc && (!$p->getPerson() || !$p->getPerson()->getAppUser() || !$p->getPerson()->getAppUser()->getAccountActivationDateTime())) ? false : $p->getIsContactPerson()
        );
    }

    /**
     * @return Collection
     */
    final public function getAllStudents(): Collection
    {
        return $this->getAllStudies()->map(fn(Position $position): ?Person => $position->isStudy() && $position->getPerson() ? $position->getPerson() : null);
    }

    /**
     * @return Collection
     */
    final public function getAllStudies(): Collection
    {
        if (!$this->isSchool()) {
            return new ArrayCollection();
        }
        $studies = $this->getDirectStudies();
        foreach ($this->getSubOrganizations() as $organization) {
            assert($organization instanceof self);
            $organization->getAllStudies()->map(
                fn(Position $position) => $studies->add($position)
            );
        }

        return $studies;
    }

    /**
     * @return Collection
     */
    final public function getSubOrganizations(): Collection
    {
        return $this->subOrganizations ?? new ArrayCollection();
    }

    /**
     * @return Collection
     */
    final public function getAllEmployees(): Collection
    {
        return $this->getAllEmployeesPositions()->map(fn(Position $position) => $position->getPerson());
    }

    final public function getAllEmployeesPositions(): Collection
    {
        $positions = $this->getDirectEmployeesPositions();
        foreach ($this->getSubOrganizations() as $organization) {
            assert($organization instanceof self);
            $organization->getAllEmployeesPositions()->map(fn(Position $position) => $positions->add($position));
        }

        return $positions;
    }

    /**
     * @return Collection
     */
    final public function getDirectEmployeesPositions(): Collection
    {
        return $this->filterPositionsByType('employee'); // TODO: Probably bug here.
    }

    /**
     * @param string $positionName
     *
     * @return Collection
     */
    final public function filterPositionsByType(string $positionName): Collection
    {
        return $this->getPositions()->filter(fn(Position $position) => $positionName === $position->getType());
    }

    /**
     * @return bool
     */
    final public function isRootOrganization(): bool
    {
        return $this->parentOrganization ? false : true;
    }

    /**
     * @param Organization|null $organization
     */
    final public function addSubOrganization(?Organization $organization): void
    {
        if ($organization && !$this->subOrganizations->contains($organization)) {
            $this->subOrganizations->add($organization);
            $organization->setParentOrganization($this);
        }
        // TODO: Check cycles!
    }

    /**
     * @param Organization|null $organization
     */
    final public function removeSubOrganization(?Organization $organization): void
    {
        if ($organization && $this->subOrganizations->removeElement($organization)) {
            $organization->setParentOrganization(null);
        }
        // TODO: Check cycles!
    }

    /**
     * @return Collection Get employees of this Organization and departments (without sub organizations).
     */
    final public function getEmployees(): Collection
    {
        $employees = $this->getDirectEmployees();
        foreach ($this->getDepartments() as $department) {
            assert($department instanceof self);
            $department->getDirectEmployees()->map(fn(Position $position) => $employees->add($position));
        }

        return $employees;
    }

    /**
     * @return Collection Get employees of this Organization (without sub organizations, without departments).
     */
    final public function getDirectEmployees(): Collection
    {
        return $this->getDirectEmployeesPositions()->map(fn(Position $position): Person => $position->getPerson());
    }

    /**
     * @return Collection
     */
    final public function getDepartments(): Collection
    {
        return $this->filterSubOrganizationsByType('department');
    }

    /**
     * @param string $type
     *
     * @return Collection
     */
    final public function filterSubOrganizationsByType(string $type): Collection
    {
        return $this->getSubOrganizations()->filter(fn(Organization $organization): bool => $type === $organization->getType());
    }

    /**
     * @return string Path (from parent organizations tree)
     * @Groups({
     *     "organizations_get",
     *     "organization_get",
     * })
     */
    final public function getPath(): string
    {
        return $this->getParentOrganization() ? '-&gt;'.$this->getParentOrganization()->getPath() : '';
    }

    /**
     * @return Organization|null
     */
    final public function getParentOrganization(): ?Organization
    {
        return $this->parentOrganization;
    }

    /**
     * @param Organization|null $organization
     */
    final public function setParentOrganization(?Organization $organization): void
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

    final public function getIdentificationNumberFromParents(): ?string
    {
        return $this->getIdentificationNumber() ?? ($this->getParentOrganization() ? $this->getParentOrganization()->getIdentificationNumberFromParents() : null) ?? null;
    }
}
