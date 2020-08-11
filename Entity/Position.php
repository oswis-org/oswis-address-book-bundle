<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Entity;

use DateTime;
use OswisOrg\OswisAddressBookBundle\Entity\AbstractClass\AbstractContact;
use OswisOrg\OswisCoreBundle\Entity\NonPersistent\DateTimeRange;
use OswisOrg\OswisCoreBundle\Entity\NonPersistent\Nameable;
use OswisOrg\OswisCoreBundle\Exceptions\InvalidTypeException;
use OswisOrg\OswisCoreBundle\Interfaces\Common\NameableInterface;
use OswisOrg\OswisCoreBundle\Traits\Common\DateRangeTrait;
use OswisOrg\OswisCoreBundle\Traits\Common\EntityPublicTrait;
use OswisOrg\OswisCoreBundle\Traits\Common\NameableTrait;
use OswisOrg\OswisCoreBundle\Traits\Common\TypeTrait;
use function in_array;

/**
 * @Doctrine\ORM\Mapping\Entity()
 * @Doctrine\ORM\Mapping\Table(name="address_book_position")
 * @ApiPlatform\Core\Annotation\ApiResource(
 *   attributes={
 *     "filters"={"search"},
 *     "access_control"="is_granted('ROLE_MANAGER')"
 *   },
 *   collectionOperations={
 *     "get"={
 *       "access_control"="is_granted('ROLE_MANAGER')",
 *       "normalization_context"={"groups"={"entities_get", "address_book_positions_get"}},
 *     },
 *     "post"={
 *       "access_control"="is_granted('ROLE_MANAGER')",
 *       "denormalization_context"={"groups"={"entities_post", "address_book_positions_post"}}
 *     }
 *   },
 *   itemOperations={
 *     "get"={
 *       "access_control"="is_granted('ROLE_MANAGER')",
 *       "normalization_context"={"groups"={"entity_get", "address_book_position_get"}},
 *     },
 *     "put"={
 *       "access_control"="is_granted('ROLE_MANAGER')",
 *       "denormalization_context"={"groups"={"entity_put", "address_book_position_put"}}
 *     },
 *     "delete"={}
 *   }
 * )
 * @ApiPlatform\Core\Annotation\ApiFilter(ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter::class)
 * @OswisOrg\OswisCoreBundle\Filter\SearchAnnotation({
 *     "id",
 *     "name",
 *     "description",
 *     "note"
 * })
 * @Doctrine\ORM\Mapping\Cache(usage="NONSTRICT_READ_WRITE", region="address_book_contact")
 */
class Position implements NameableInterface
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

    use NameableTrait;
    use DateRangeTrait;
    use TypeTrait;
    use EntityPublicTrait;

    /**
     * True if person is intended for receiving messages about organization.
     * @Doctrine\ORM\Mapping\Column(type="boolean")
     */
    protected bool $contactPerson = false;

    /**
     * True if position is kind of "special" (and to be displayed in web profile).
     * @Doctrine\ORM\Mapping\Column(type="boolean")
     */
    protected bool $special = false;

    /**
     * @Doctrine\ORM\Mapping\ManyToOne(targetEntity="OswisOrg\OswisAddressBookBundle\Entity\Person", inversedBy="positions")
     * @Doctrine\ORM\Mapping\JoinColumn(name="person_id", referencedColumnName="id")
     */
    protected ?Person $person = null;

    /**
     * @Doctrine\ORM\Mapping\ManyToOne(targetEntity="OswisOrg\OswisAddressBookBundle\Entity\Organization")
     * @Doctrine\ORM\Mapping\JoinColumn(name="organization_id", referencedColumnName="id")
     */
    protected ?Organization $organization = null;

    /**
     * @param Nameable|null      $nameable
     * @param Person|null        $person
     * @param Organization|null  $organization
     * @param string|null        $type
     * @param bool|null          $isContactPerson
     * @param DateTimeRange|null $range
     *
     * @throws InvalidTypeException
     */
    public function __construct(
        ?Nameable $nameable = null,
        ?Person $person = null,
        ?Organization $organization = null,
        ?string $type = null,
        ?bool $isContactPerson = null,
        ?DateTimeRange $range = null
    ) {
        $this->setPerson($person);
        $this->setOrganization($organization);
        $this->setType($type);
        $this->setContactPerson($isContactPerson);
        $this->setFieldsFromNameable($nameable);
        $this->setDateTimeRange($range);
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

    public function isSpecial(): bool
    {
        return $this->special;
    }

    public function setSpecial(?bool $special): void
    {
        $this->special = $special ?? false;
    }

    public function getEmployerName(): string
    {
        return $this->organization ? $this->organization->getName() : '???';
    }

    public function isActive(?DateTime $dateTime = null): bool
    {
        return $this->isInDateRange($dateTime);
    }

    public function getEmployeeName(): string
    {
        return $this->person ? $this->person->getFullName() : '???';
    }

    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    public function setOrganization(?Organization $organization): void
    {
        $this->organization = $organization;
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
        return $this->contactPerson;
    }

    public function setContactPerson(?bool $isContactPerson): void
    {
        $this->contactPerson = $isContactPerson ?? false;
    }

    public function getGenderCssClass(): string
    {
        return null === $this->getPerson() ? AbstractContact::GENDER_UNISEX : $this->getPerson()->getGender();
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): void
    {
        if (null !== $this->person && $person !== $this->person) {
            $this->person->removePosition($this);
        }
        $this->person = $person;
        if (null !== $person && $this->person !== $person) {
            $person->addPosition($this);
        }
    }
}
