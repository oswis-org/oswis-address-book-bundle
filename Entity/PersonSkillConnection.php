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
use Zakjakub\OswisCoreBundle\Filter\SearchAnnotation as Searchable;
use Zakjakub\OswisCoreBundle\Interfaces\BasicEntityInterface;
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
 * @Doctrine\ORM\Mapping\Cache(usage="NONSTRICT_READ_WRITE", region="address_book_contact")
 */
class PersonSkillConnection implements BasicEntityInterface
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
     * @Doctrine\ORM\Mapping\ManyToOne(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\Person",
     *     cascade={"all"},
     *     inversedBy="personSkillConnections"
     * )
     * @Doctrine\ORM\Mapping\JoinColumn(name="person_id", referencedColumnName="id")
     */
    protected ?Person $person = null;

    /**
     * Skill.
     * @Doctrine\ORM\Mapping\ManyToOne(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\PersonSkill",
     *     cascade={"all"},
     *     fetch="EAGER"
     * )
     * @Doctrine\ORM\Mapping\JoinColumn(name="person_skill_id", referencedColumnName="id")
     */
    protected ?PersonSkill $personSkill = null;

    /**
     * Level of skill.
     * @var int|null
     * @Doctrine\ORM\Mapping\Column(type="smallint", nullable=true)
     */
    protected ?int $level = null;

    /**
     * Is public on website?
     * @var boolean|null
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected ?bool $publicOnWeb = null;

    /**
     * Can give skill to other person?
     * @var boolean|null
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected ?bool $canGiveSkill = null;

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

    public function getCanGiveSkill(): ?bool
    {
        return $this->canGiveSkill;
    }

    public function setCanGiveSkill(?bool $canGiveSkill): void
    {
        $this->canGiveSkill = $canGiveSkill;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): void
    {
        if ($this->person && $person !== $this->person) {
            $this->person->removePersonSkillConnection($this);
        }
        $this->person = $person;
        if ($person && $this->person !== $person) {
            $person->addPersonSkillConnection($this);
        }
    }

    public function getPersonSkill(): ?PersonSkill
    {
        return $this->personSkill;
    }

    public function setPersonSkill(?PersonSkill $personSkill): void
    {
        $this->personSkill = $personSkill;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(?int $level): void
    {
        $this->level = $level;
    }

    public function isPublicOnWeb(): bool
    {
        return $this->publicOnWeb;
    }

    public function setPublicOnWeb(bool $publicOnWeb): void
    {
        $this->publicOnWeb = $publicOnWeb;
    }
}
