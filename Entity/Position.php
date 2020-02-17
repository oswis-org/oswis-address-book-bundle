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
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Vokativ\Name as VokativName;
use Zakjakub\OswisCoreBundle\Entity\Nameable;
use Zakjakub\OswisCoreBundle\Filter\SearchAnnotation as Searchable;
use Zakjakub\OswisCoreBundle\Interfaces\BasicEntityInterface;
use Zakjakub\OswisCoreBundle\Traits\Entity\BasicEntityTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\DateRangeTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\NameableBasicTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\TypeTrait;
use function in_array;

/**
 * @Doctrine\ORM\Mapping\Entity()
 * @Doctrine\ORM\Mapping\Table(name="address_book_position")
 * @ApiResource(
 *   attributes={
 *     "filters"={"search"},
 *     "access_control"="is_granted('ROLE_MANAGER')"
 *   },
 *   collectionOperations={
 *     "get"={
 *       "access_control"="is_granted('ROLE_MANAGER')",
 *       "normalization_context"={"groups"={"address_book_positions_get"}},
 *     },
 *     "post"={
 *       "access_control"="is_granted('ROLE_MANAGER')",
 *       "denormalization_context"={"groups"={"address_book_positions_post"}}
 *     }
 *   },
 *   itemOperations={
 *     "get"={
 *       "access_control"="is_granted('ROLE_MANAGER')",
 *       "normalization_context"={"groups"={"address_book_position_get"}},
 *     },
 *     "put"={
 *       "access_control"="is_granted('ROLE_MANAGER')",
 *       "denormalization_context"={"groups"={"address_book_position_put"}}
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
class Position implements BasicEntityInterface
{
    public const TYPE_EMPLOYEE = 'employee';
    public const TYPE_MEMBER = 'member';
    public const TYPE_MANAGER = 'manager';
    public const TYPE_DIRECTOR = 'director';
    public const TYPE_STUDENT = 'student';
    public const TYPE_GRADUATED = 'graduated';
    public const TYPE_STUDENT_OR_GRADUATED = 'student/graduated';

    public const MANAGER_POSITION_TYPES = [self::TYPE_MANAGER, self::TYPE_DIRECTOR];
    public const STUDY_POSITION_TYPES = [self::TYPE_STUDENT, self::TYPE_GRADUATED, self::TYPE_STUDENT_OR_GRADUATED];
    public const EMPLOYEE_POSITION_TYPES = [self::TYPE_EMPLOYEE, ...self::MANAGER_POSITION_TYPES];
    public const MEMBER_POSITION_TYPES = [self::TYPE_MEMBER, ...self::MANAGER_POSITION_TYPES];
    public const EMPLOYEE_MEMBER_POSITION_TYPES = [self::TYPE_MEMBER, self::TYPE_EMPLOYEE, ...self::MANAGER_POSITION_TYPES];

    use BasicEntityTrait;
    use NameableBasicTrait;
    use DateRangeTrait;
    use TypeTrait;

    /**
     * True if person is intended for receiving messages about organization.
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected ?bool $isContactPerson = null;

    /**
     * True if position is kind of "special" (and to be displayed in web profile).
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected ?bool $special = null;

    /**
     * @Doctrine\ORM\Mapping\ManyToOne(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\Person",
     *     cascade={"all"},
     *     inversedBy="positions"
     * )
     * @Doctrine\ORM\Mapping\JoinColumn(name="person_id", referencedColumnName="id")
     */
    protected ?Person $person = null;

    /**
     * @Doctrine\ORM\Mapping\ManyToOne(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\Organization",
     *     cascade={"all"},
     *     inversedBy="positions"
     * )
     * @Doctrine\ORM\Mapping\JoinColumn(name="organization_id", referencedColumnName="id")
     */
    protected ?Organization $organization = null;

    /**
     * @param Person|null       $person
     * @param Organization|null $organization
     * @param string|null       $type
     * @param bool|null         $isContactPerson
     * @param Nameable|null     $nameable
     * @param DateTime|null     $startDateTime
     * @param DateTime|null     $endDateTime
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        ?Person $person = null,
        ?Organization $organization = null,
        ?string $type = null,
        ?bool $isContactPerson = null,
        ?Nameable $nameable = null,
        ?DateTime $startDateTime = null,
        ?DateTime $endDateTime = null
    ) {
        $this->setPerson($person);
        $this->setOrganization($organization);
        $this->setType($type);
        $this->setIsContactPerson($isContactPerson);
        $this->setFieldsFromNameable($nameable);
        $this->setStartDateTime($startDateTime);
        $this->setEndDateTime($endDateTime);
    }

    public static function getAllowedTypesDefault(): array
    {
        return [
            self::TYPE_EMPLOYEE,
            self::TYPE_MEMBER,
            self::TYPE_MANAGER,
            self::TYPE_DIRECTOR,
            self::TYPE_STUDENT,
            self::TYPE_GRADUATED,
            self::TYPE_STUDENT_OR_GRADUATED,
        ];
    }

    public static function getAllowedTypesCustom(): array
    {
        return [];
    }

    public function isSpecial(): ?bool
    {
        return $this->special;
    }

    public function setSpecial(?bool $special): void
    {
        $this->special = $special;
    }

    /**
     * Get organization of this position.
     */
    public function getEmployerString(): string
    {
        return $this->organization ? $this->organization->getName() : '???';
    }

    public function isActive(): bool
    {
        return $this->containsDateTimeInRange();
    }

    /**
     * Get person of this position.
     */
    public function getEmployeeString(): string
    {
        return $this->person ? $this->person->getFullName() : '???';
    }

    /**
     * Get organization of this position.
     */
    public function getDepartmentString(): ?string
    {
        return ''; // TODO
        // if ($this->department) {
        //     return $this->department->getName();
        // } else {
        //     return null;
        // }
    }

    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    public function setOrganization(?Organization $organization): void
    {
        // if (null != $this->department) {
        //     $this->department->removePosition($this);
        //     $this->setDepartment(null);
        // }
        if ($this->organization && $organization !== $this->organization) {
            $this->organization->removePosition($this);
        }
        $this->organization = $organization;
        if ($organization && $this->organization !== $organization) {
            $organization->addPosition($this);
        }
    }

    public function isManager(): bool
    {
        return in_array($this->getType(), self::MANAGER_POSITION_TYPES, true);
    }

    public function isRegularPosition(): bool
    {
        return !$this->isStudy();
    }

    public function isStudy(): bool
    {
        return in_array($this->getType(), self::STUDY_POSITION_TYPES, true);
    }

    public function isContactPerson(): bool
    {
        return $this->getIsContactPerson();
    }

    public function getIsContactPerson(): bool
    {
        return $this->isContactPerson ?? false;
    }

    public function setIsContactPerson(?bool $isContactPerson): void
    {
        $this->isContactPerson = $isContactPerson ?? false;
    }

    public function getGenderCssClass(): string
    {
        if ($this->getPerson() === null || !$this->getPerson()->getGivenName()) {
            return 'unisex';
        }
        try {
            return (new VokativName())->isMale($this->getPerson()->getGivenName()) ? 'male' : 'female';
        } catch (InvalidArgumentException $e) {
            return 'unisex';
        }
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): void
    {
        if ($this->person && $person !== $this->person) {
            $this->person->removePosition($this);
        }
        $this->person = $person;
        if ($person && $this->person !== $person) {
            $person->addPosition($this);
        }
    }
}
