<?php

namespace Zakjakub\OswisAddressBookBundle\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Zakjakub\OswisAddressBookBundle\Entity\AbstractClass\AbstractOrganization;
use Zakjakub\OswisCoreBundle\Entity\AbstractClass\AbstractRevision;
use Zakjakub\OswisCoreBundle\Entity\Nameable;
use Zakjakub\OswisCoreBundle\Exceptions\RevisionMissingException;
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
 */
class Organization extends AbstractOrganization
{

    /**
     * @var Collection
     * @Doctrine\ORM\Mapping\OneToMany(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\OrganizationRevision",
     *     mappedBy="container",
     *     cascade={"all"},
     *     orphanRemoval=true,
     *     fetch="EAGER"
     * )
     */
    protected $revisions;

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
    protected $parentOrganization;

    /**
     * @var Collection|null $subOrganizations Sub organizations
     * @Doctrine\ORM\Mapping\OneToMany(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\Organization",
     *     mappedBy="parentOrganization"
     * )
     */
    protected $subOrganizations;

    /**
     * @var Collection|null $positions Positions
     * @Doctrine\ORM\Mapping\OneToMany(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\Position",
     *     mappedBy="organization",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    protected $positions;

    public function __construct(
        ?Nameable $nameable = null,
        ?string $identificationNumber = null,
        ?Organization $parentOrganization = null,
        ?string $color = null,
        ?string $type = 'organization',
        ?Collection $addresses = null,
        ?Collection $contactDetails = null,
        ?Collection $notes = null,
        ?Collection $addressBooks = null
    ) {
        parent::__construct($type, $notes, $contactDetails, $addresses, null, $addressBooks);
        $this->positions = new ArrayCollection();
        $this->subOrganizations = new ArrayCollection();
        $this->setParentOrganization($parentOrganization);
        $this->revisions = new ArrayCollection();
        $this->addRevision(new OrganizationRevision($nameable, $identificationNumber, $color));
    }

    /**
     * @param AbstractRevision|null $revision
     */
    public static function checkRevision(?AbstractRevision $revision): void
    {
        assert($revision instanceof OrganizationRevision);
    }

    /**
     * @return string
     */
    public static function getRevisionClassName(): string
    {
        return OrganizationRevision::class;
    }

    /**
     * @param DateTime|null $dateTime
     *
     * @return OrganizationRevision
     * @throws RevisionMissingException
     */
    final public function getRevisionByDate(?DateTime $dateTime = null): OrganizationRevision
    {
        $revision = $this->getRevision($dateTime);
        assert($revision instanceof OrganizationRevision);

        return $revision;
    }

    /**
     * @param Position|null $position
     */
    final public function addPosition(?Position $position): void
    {
        if (!$position) {
            return;
        }
        if ($position && !$this->positions->contains($position)) {
            $this->positions->add($position);
            $position->setOrganization($this);
        }
    }

    /**
     * @param Position|null $position
     */
    final public function removePosition(?Position $position): void
    {
        if (!$position) {
            return;
        }
        if ($this->positions->removeElement($position)) {
            $position->setOrganization(null);
            $position->setPerson(null);
        }
    }

    /**
     * @return Collection
     */
    final public function getDirectStudents(): Collection
    {
        $students = new ArrayCollection();
        if ($this->getType() === 'school') {
            $this->getDirectStudies()->map(
                static function (Position $position) use ($students) {
                    $students->add($position->getPerson());
                }
            );
        }

        return $students;
    }

    /**
     * @return Collection
     */
    final public function getDirectStudies(): Collection
    {
        if ($this->getType() === 'school') {
            return $this->filterPositionsByType('student');
        }

        return new ArrayCollection();
    }

    /**
     * @param string $positionName
     *
     * @return Collection
     */
    final public function filterPositionsByType(string $positionName): Collection
    {
        return $this->getPositions()->filter(
            static function (Position $position) use ($positionName) {
                return $positionName === $position->getType();
            }
        );
    }

    /**
     * @return Collection
     */
    final public function getPositions(): Collection
    {
        return $this->positions ?? new ArrayCollection();
    }

    /**
     * @return Collection
     */
    final public function getAllStudents(): Collection
    {
        $students = new ArrayCollection();
        if ($this->getType() === 'school') {
            $this->getAllStudies()->map(
                static function (Position $position) use ($students) {
                    $students->add($position->getPerson());
                }
            );
        }

        return $students;
    }

    /**
     * @return Collection
     */
    final public function getAllStudies(): Collection
    {
        if ($this->getType() === 'school') {
            $studies = $this->getDirectStudies();
            foreach ($this->getSubOrganizations() as $organization) {
                assert($organization instanceof self);
                $organization->getAllStudies()->forAll(
                    static function (Position $position) use ($studies) {
                        $studies->add($position);
                    }
                );
            }

            return $studies;
        }

        return new ArrayCollection();
    }

    /**
     * @return Collection
     */
    final public function getSubOrganizations(): Collection
    {
        return $this->subOrganizations;
    }

    /**
     * @return Collection
     */
    final public function getAllEmployees(): Collection
    {
        $employees = new ArrayCollection();
        if ($this->getType() === 'school') {
            $this->getAllStudies()->map(
                static function (Position $position) use ($employees) {
                    $employees->add($position->getPerson());
                }
            );
        }

        return $employees;
    }

    /**
     * @return Collection
     */
    final public function getAllEmployeesPositions(): Collection
    {
        $positions = $this->getDirectEmployeesPositions();
        foreach ($this->getSubOrganizations() as $organization) {
            assert($organization instanceof self);
            $organization->getAllEmployeesPositions()->forAll(
                static function (Position $position) use ($positions) {
                    $positions->add($position);
                }
            );
        }

        return $positions;
    }

    /**
     * @return Collection
     */
    final public function getDirectEmployeesPositions(): Collection
    {
        return $this->filterPositionsByType('employee');
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
        if (!$organization) {
            return;
        }
        if ($organization && !$this->subOrganizations->contains($organization)) {
            $this->subOrganizations->add($organization);
            $organization->setParentOrganization($this);
        }
        // TODO: Check!
    }

    /**
     * @param Organization|null $organization
     */
    final public function removeSubOrganization(?Organization $organization): void
    {
        if (!$organization) {
            return;
        }
        if ($this->subOrganizations->removeElement($organization)) {
            $organization->setParentOrganization(null);
        }
        // TODO: Check!
    }

    /**
     * @return Collection Get employees of this Organization and departments (without sub organizations)
     */
    final public function getEmployees(): Collection
    {
        $employees = $this->getDirectEmployees();
        foreach ($this->getDepartments() as $department) {
            assert($department instanceof self);
            $department->getDirectEmployees()->forAll(
                static function (Position $position) use ($employees) {
                    $employees->add($position);
                }
            );
        }

        return $employees;
    }

    /**
     * @return Collection Get employees of this Organization (without sub organizations, without departments)
     */
    final public function getDirectEmployees(): Collection
    {
        $employees = new ArrayCollection();
        $positionsOfEmployees = $this->getDirectEmployeesPositions();
        $positionsOfEmployees->forAll(
            static function (Position $position) use ($employees) {
                $employees->add($position->getPerson());
            }
        );

        return $employees;
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
        return $this->getSubOrganizations()->filter(
            static function (Organization $organization) use ($type) {
                return $type === $organization->getType();
            }
        );
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
        if ($this->getParentOrganization()) {
            if ($this->getParentOrganization()->getIdentificationNumber()) {
                return $this->getParentOrganization()->getIdentificationNumber();
            }

            return $this->getParentOrganization()->getIdentificationNumberFromParents();
        }

        return $this->getIdentificationNumber();
    }
}
