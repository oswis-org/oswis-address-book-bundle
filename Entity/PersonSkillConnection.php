<?php

namespace Zakjakub\OswisAddressBookBundle\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Zakjakub\OswisCoreBundle\Filter\SearchAnnotation as Searchable;
use Zakjakub\OswisCoreBundle\Traits\Entity\BasicEntityTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\DateRangeTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\NoteTrait;

/**
 * Connection between person and skill.
 * @Doctrine\ORM\Mapping\Entity()
 * @Doctrine\ORM\Mapping\Table(name="address_book_person_skill_connection")
 * @ApiResource(
 *   attributes={
 *     "filters"={"search"},
 *     "access_control"="is_granted('ROLE_MANAGER')"
 *   },
 *   collectionOperations={
 *     "get"={
 *       "access_control"="is_granted('ROLE_MANAGER')",
 *       "normalization_context"={"groups"={"address_book_person_skill_connections_get"}},
 *     },
 *     "post"={
 *       "access_control"="is_granted('ROLE_MANAGER')",
 *       "denormalization_context"={"groups"={"address_book_person_skill_connections_post"}}
 *     }
 *   },
 *   itemOperations={
 *     "get"={
 *       "access_control"="is_granted('ROLE_MANAGER')",
 *       "normalization_context"={"groups"={"address_book_person_skill_connection_get"}},
 *     },
 *     "put"={
 *       "access_control"="is_granted('ROLE_MANAGER')",
 *       "denormalization_context"={"groups"={"address_book_person_skill_connection_put"}}
 *     },
 *     "delete"={
 *       "access_control"="is_granted('ROLE_ADMIN')",
 *       "denormalization_context"={"groups"={"address_book_person_skill_connection_delete"}}
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
class PersonSkillConnection
{
    use BasicEntityTrait;
    use DateRangeTrait;
    use NoteTrait;

    public const LEVEL_NONE = 0;
    public const LEVEL_BEGINNER = 1;
    public const LEVEL_NORMAL = 3;
    public const LEVEL_ADVANCED = 5;
    public const LEVEL_EXPERT = 7;
    public const LEVEL_SUPER = 9;

    /**
     * Person in this position.
     * @var Person|null $person
     * @Doctrine\ORM\Mapping\ManyToOne(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\Person",
     *     cascade={"all"},
     *     inversedBy="personSkillConnections",
     *     fetch="EAGER"
     * )
     * @Doctrine\ORM\Mapping\JoinColumn(name="person_id", referencedColumnName="id")
     */
    protected $person;

    /**
     * Skill.
     * @var PersonSkill|null $personSkill
     * @Doctrine\ORM\Mapping\ManyToOne(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\PersonSkill",
     *     cascade={"all"},
     *     inversedBy="personSkillConnections",
     *     fetch="EAGER"
     * )
     * @Doctrine\ORM\Mapping\JoinColumn(name="person_skill_id", referencedColumnName="id")
     */
    protected $personSkill;

    /**
     * Level of skill.
     * @var int|null
     * @Doctrine\ORM\Mapping\Column(type="smallint", nullable=true)
     */
    protected $level;

    /**
     * Is public on website?
     * @var boolean
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $publicOnWeb;

    /**
     * PersonSkill constructor.
     *
     * @param Person|null      $person
     * @param PersonSkill|null $personSkill
     * @param int|null         $level
     * @param string|null      $note
     * @param DateTime|null    $startDateTime
     * @param DateTime|null    $endDateTime
     */
    public function __construct(
        ?Person $person = null,
        ?PersonSkill $personSkill = null,
        ?int $level = null,
        ?string $note = null,
        ?DateTime $startDateTime = null,
        ?DateTime $endDateTime = null
    ) {
        $this->setPerson($person);
        $this->setPersonSkill($personSkill);
        $this->setLevel($level);
        $this->setNote($note);
        $this->setStartDateTime($startDateTime);
        $this->setEndDateTime($endDateTime);
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
            $this->person->removePersonSkillConnection($this);
        }
        $this->person = $person;
        if ($person && $this->person !== $person) {
            $person->addPersonSkillConnection($this);
        }
    }

    /**
     * @return PersonSkill
     */
    final public function getPersonSkill(): ?PersonSkill
    {
        return $this->personSkill;
    }

    /**
     * @param PersonSkill|null $personSkill
     */
    final public function setPersonSkill(?PersonSkill $personSkill): void
    {
        if ($this->personSkill && $personSkill !== $this->personSkill) {
            $this->personSkill->removePersonSkillConnection($this);
        }
        $this->personSkill = $personSkill;
        if ($personSkill && $this->personSkill !== $personSkill) {
            $personSkill->addPersonSkillConnection($this);
        }
    }

    /**
     * @return int|null
     */
    final public function getLevel(): ?int
    {
        return $this->level;
    }

    /**
     * @param int|null $level
     */
    final public function setLevel(?int $level): void
    {
        $this->level = $level;
    }

    /**
     * @return bool
     */
    final public function isPublicOnWeb(): bool
    {
        return $this->publicOnWeb;
    }

    /**
     * @param bool $publicOnWeb
     */
    final public function setPublicOnWeb(bool $publicOnWeb): void
    {
        $this->publicOnWeb = $publicOnWeb;
    }

}
