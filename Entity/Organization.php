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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Zakjakub\OswisAddressBookBundle\Entity\AbstractClass\AbstractContact;
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

    public function getDirectStudents(): Collection
    {
        $students = new ArrayCollection();
        if ($this->isSchool()) {
            foreach ($this->getStudyPositions() as $study) {
                assert($study instanceof Position);
                if (!$students->contains($study->getPerson())) {
                    $students->add($study->getPerson());
                }
            }
        }

        return $students;
    }

    public function getStudyPositions(?DateTime $dateTime = null, bool $recursive = false): Collection
    {
        return $this->getPositions($dateTime, Position::STUDY_POSITION_TYPES, $recursive);
    }

    public function getPositions(?DateTime $dateTime = null, array $types = [], bool $recursive = false): Collection
    {
        $out = $this->positions ?? new ArrayCollection();
        if (null !== $dateTime) {
            $out = $out->filter(fn(Position $p): bool => $p->containsDateTimeInRange($dateTime));
        }
        if (!empty($types)) {
            $out = $out->filter(fn(Position $position) => in_array($position->getType(), $types, true));
        }
        if (true === $recursive) {
            foreach ($this->getSubOrganizations() as $subOrganization) {
                if ($subOrganization instanceof self) {
                    $subOrganization->getPositions($dateTime, $types, true)->map(fn(Position $p) => $out->add($p));
                }
            }
        }

        return $out;
    }

    public function getSubOrganizations(): Collection
    {
        return $this->subOrganizations ?? new ArrayCollection();
    }

    public function getStudents(?DateTime $dateTime = null, bool $recursive = false): Collection
    {
        $out = new ArrayCollection();
        $this->getStudyPositions($dateTime, $recursive)->map(fn(AbstractContact $c) => $out->contains($c) ? null : $out->add($c));

        return $out;
    }

    public function getMembers(?DateTime $dateTime = null, bool $recursive = false): Collection
    {
        $out = new ArrayCollection();
        $this->getMemberPositions($dateTime, $recursive)->map(fn(AbstractContact $c) => $out->contains($c) ? null : $out->add($c));

        return $out;
    }

    public function getMemberPositions(?DateTime $dateTime = null, bool $recursive = false): Collection
    {
        return $this->getPositions($dateTime, Position::MEMBER_POSITION_TYPES, $recursive);
    }

    public function getMembersAndEmployees(?DateTime $dateTime = null, bool $recursive = false): Collection
    {
        $out = new ArrayCollection();
        $this->getMemberAndEmployeePositions($dateTime, $recursive)->map(fn(AbstractContact $c) => $out->contains($c) ? null : $out->add($c));

        return $out;
    }

    public function getMemberAndEmployeePositions(?DateTime $dateTime = null, bool $recursive = false): Collection
    {
        return $this->getPositions($dateTime, Position::EMPLOYEE_MEMBER_POSITION_TYPES, $recursive);
    }

    public function getEmployees(?DateTime $dateTime = null, bool $recursive = false): Collection
    {
        $out = new ArrayCollection();
        $this->getEmployeePositions($dateTime, $recursive)->map(fn(AbstractContact $c) => $out->contains($c) ? null : $out->add($c));

        return $out;
    }

    public function getEmployeePositions(?DateTime $dateTime = null, bool $recursive = false): Collection
    {
        return $this->getPositions($dateTime, Position::EMPLOYEE_POSITION_TYPES, $recursive);
    }

    public function getManagers(?DateTime $dateTime = null, bool $recursive = false): Collection
    {
        $out = new ArrayCollection();
        $this->getManagerPositions($dateTime, $recursive)->map(fn(AbstractContact $c) => $out->contains($c) ? null : $out->add($c));

        return $out;
    }

    public function getManagerPositions(?DateTime $dateTime = null, bool $recursive = false): Collection
    {
        return $this->getPositions($dateTime, Position::STUDY_POSITION_TYPES, $recursive);
    }

    public function getContactPersons(?DateTime $dateTime = null, bool $onlyWithActivatedUser = false): Collection
    {
        $act = $onlyWithActivatedUser;
        $positions = $this->getPositions($dateTime ?? new DateTime());

        return $positions->filter(
            fn(Position $p) => ($act && (!$p->getPerson() || !$p->getPerson()->getAppUser() || !$p->getPerson()->getAppUser()->getAccountActivationDateTime(
                    ))) ? false : $p->getIsContactPerson()
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
