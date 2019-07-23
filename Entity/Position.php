<?php

namespace Zakjakub\OswisAddressBookBundle\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use InvalidArgumentException;
use Zakjakub\OswisCoreBundle\Filter\SearchAnnotation as Searchable;
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
 *     },
 *     "delete"={
 *       "access_control"="is_granted('ROLE_ADMIN')",
 *       "denormalization_context"={"groups"={"address_book_position_delete"}}
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
class Position
{
    public const TYPE_EMPLOYEE = 'employee';
    public const TYPE_MEMBER = 'member';
    public const TYPE_MANAGER = 'manager';
    public const TYPE_DIRECTOR = 'director';
    public const TYPE_BOSS = 'boss';
    public const TYPE_STUDENT = 'student';
    public const TYPE_GRADUATED = 'graduated';
    public const TYPE_STUDENT_OR_GRADUATED = 'student/graduated';

    public const MANAGER_POSITION_TYPES = [self::TYPE_MANAGER, self::TYPE_DIRECTOR, self::TYPE_BOSS];
    public const STUDY_POSITION_TYPES = [self::TYPE_STUDENT, self::TYPE_GRADUATED, self::TYPE_STUDENT_OR_GRADUATED];

    use BasicEntityTrait;
    use NameableBasicTrait;
    use DateRangeTrait;
    use TypeTrait;

    /**
     * True if person is intended for receiving messages about organization.
     * @var bool|null
     */
    protected $isContactPerson;

    /**
     * Person in this position.
     * @var Person|null $person
     * @Doctrine\ORM\Mapping\ManyToOne(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\Person",
     *     cascade={"all"},
     *     inversedBy="positions",
     *     fetch="EAGER"
     * )
     * @Doctrine\ORM\Mapping\JoinColumn(name="person_id", referencedColumnName="id")
     */
    private $person;

    /**
     * Organization of this position.
     * @var Organization|null $organization
     * @Doctrine\ORM\Mapping\ManyToOne(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\Organization",
     *     cascade={"all"},
     *     inversedBy="positions",
     *     fetch="EAGER"
     * )
     * @Doctrine\ORM\Mapping\JoinColumn(name="organization_id", referencedColumnName="id")
     */
    private $organization;

    /**
     * Position constructor.
     *
     * @param Person|null       $person
     * @param Organization|null $organization
     * @param string|null       $type
     * @param bool|null         $isContactPerson
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        ?Person $person = null,
        ?Organization $organization = null,
        ?string $type = null,
        ?bool $isContactPerson = null
    ) {
        $this->setPerson($person);
        $this->setOrganization($organization);
        $this->setType($type);
        $this->setIsContactPerson($isContactPerson);
    }

    public static function getAllowedTypesDefault(): array
    {
        return [
            self::TYPE_EMPLOYEE,
            self::TYPE_MEMBER,
            self::TYPE_MANAGER,
            self::TYPE_DIRECTOR,
            self::TYPE_BOSS,
            self::TYPE_STUDENT,
            self::TYPE_GRADUATED,
            self::TYPE_STUDENT_OR_GRADUATED,
        ];
    }

    public static function getAllowedTypesCustom(): array
    {
        return [];
    }

    /**
     * Get organization of this position.
     *
     * @return string
     */
    final public function getEmployerString(): string
    {
        if ($this->organization) {
            return $this->organization->getName();
        }

        return '???';
    }

    /**
     * Get person of this position.
     * @return string
     */
    final public function getEmployeeString(): string
    {
        if ($this->person) {
            return $this->person->getFullName();
        }

        return '???';
    }

    /**
     * Get organization of this position.
     * @return string|null
     */
    final public function getDepartmentString(): ?string
    {
        return ''; // TODO
        // if ($this->department) {
        //     return $this->department->getName();
        // } else {
        //     return null;
        // }
    }


    /**
     * @return Person
     */
    final public function getPerson(): ?Person
    {
        return $this->person;
    }

    /**
     * @param Person $person
     */
    final public function setPerson(?Person $person): void
    {
        if ($this->person && $person !== $this->person) {
            $this->person->removePosition($this);
        }
        $this->person = $person;
        if ($person && $this->person !== $person) {
            $person->addPosition($this);
        }
    }

    /**
     * @return Organization
     */
    final public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    /**
     * @param Organization $organization
     */
    final public function setOrganization(?Organization $organization): void
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

    final public function isManager(): bool
    {
        return in_array($this->getType(), self::MANAGER_POSITION_TYPES, true);
    }

    final public function isRegularPosition(): bool
    {
        return !$this->isStudy();
    }

    final public function isStudy(): bool
    {
        return in_array($this->getType(), self::STUDY_POSITION_TYPES, true);
    }

    final public function isContactPerson(): bool
    {
        return $this->getIsContactPerson();
    }

    /**
     * @return bool|null
     */
    final public function getIsContactPerson(): bool
    {
        return $this->isContactPerson ?? false;
    }

    /**
     * @param bool|null $isContactPerson
     */
    final public function setIsContactPerson(bool $isContactPerson): void
    {
        $this->isContactPerson = $isContactPerson ?? false;
    }

}
