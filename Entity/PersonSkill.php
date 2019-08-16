<?php

namespace Zakjakub\OswisAddressBookBundle\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Zakjakub\OswisCoreBundle\Entity\Nameable;
use Zakjakub\OswisCoreBundle\Filter\SearchAnnotation as Searchable;
use Zakjakub\OswisCoreBundle\Traits\Entity\BasicEntityTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\NameableBasicTrait;

/**
 * Some skill (ability).
 * @Doctrine\ORM\Mapping\Entity()
 * @Doctrine\ORM\Mapping\Table(name="address_book_person_skill")
 * @ApiResource(
 *   attributes={
 *     "filters"={"search"},
 *     "access_control"="is_granted('ROLE_MANAGER')"
 *   },
 *   collectionOperations={
 *     "get"={
 *       "access_control"="is_granted('ROLE_MANAGER')",
 *       "normalization_context"={"groups"={"address_book_person_skills_get"}},
 *     },
 *     "post"={
 *       "access_control"="is_granted('ROLE_MANAGER')",
 *       "denormalization_context"={"groups"={"address_book_person_skills_post"}}
 *     }
 *   },
 *   itemOperations={
 *     "get"={
 *       "access_control"="is_granted('ROLE_MANAGER')",
 *       "normalization_context"={"groups"={"address_book_person_skill_get"}},
 *     },
 *     "put"={
 *       "access_control"="is_granted('ROLE_MANAGER')",
 *       "denormalization_context"={"groups"={"address_book_person_skill_put"}}
 *     },
 *     "delete"={
 *       "access_control"="is_granted('ROLE_ADMIN')",
 *       "denormalization_context"={"groups"={"address_book_person_skill_delete"}}
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
class PersonSkill
{
    use BasicEntityTrait;
    use NameableBasicTrait;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $publicOnWebDefault;

    /**
     * Can user edit connections with this skill?
     * @var boolean
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $connectionsUserEditable;

    /**
     * Connections to persons.
     * @var Collection|null $positions
     * @Doctrine\ORM\Mapping\OneToMany(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\PersonSkillConnection",
     *     mappedBy="personSkill",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    private $personSkillConnections;

    /**
     * Position constructor.
     *
     * @param Nameable|null   $nameable
     * @param Collection|null $personSkillConnections
     */
    public function __construct(
        ?Nameable $nameable = null,
        ?Collection $personSkillConnections = null
    ) {
        $this->setFieldsFromNameable($nameable);
        $this->setPersonSkillConnections($personSkillConnections);
    }

    final public function setPersonSkillConnections(?Collection $newPersonSkillConnections): void
    {
        if (!$this->personSkillConnections) {
            $this->personSkillConnections = new ArrayCollection();
        }
        if (!$newPersonSkillConnections) {
            $newPersonSkillConnections = new ArrayCollection();
        }
        foreach ($this->personSkillConnections as $oldPersonSkillConnection) {
            if (!$newPersonSkillConnections->contains($oldPersonSkillConnection)) {
                $this->personSkillConnections->removeElement($oldPersonSkillConnection);
            }
        }
        if ($newPersonSkillConnections) {
            foreach ($newPersonSkillConnections as $newPersonSkillConnection) {
                if (!$this->personSkillConnections->contains($newPersonSkillConnection)) {
                    $this->addPersonSkillConnection($newPersonSkillConnection);
                }
            }
        }
    }

    final public function addPersonSkillConnection(?PersonSkillConnection $personSkillConnection): void
    {
        if (!$personSkillConnection) {
            return;
        }
        if (!$this->personSkillConnections->contains($personSkillConnection)) {
            $this->personSkillConnections->add($personSkillConnection);
            $personSkillConnection->setPersonSkill($this);
        }
    }

    final public function removePersonSkillConnection(?PersonSkillConnection $personSkillConnection): void
    {
        if (!$personSkillConnection) {
            return;
        }
        if ($this->personSkillConnections->removeElement($personSkillConnection)) {
            $personSkillConnection->setPersonSkill(null);
        }
    }
}
