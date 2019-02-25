<?php

namespace Zakjakub\OswisAddressBookBundle\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Zakjakub\OswisCoreBundle\Traits\Entity\BasicEntityTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\NameableBasicTrait;

/**
 * @Doctrine\ORM\Mapping\Entity()
 * @Doctrine\ORM\Mapping\Table(name="address_book_position")
 * @ApiResource()
 */
class Position
{

    use BasicEntityTrait;
    use NameableBasicTrait;

    public const MANAGER_POSITION_TYPES = ['manager', 'director', 'boss'];

    /**
     * Person in this position.
     * @var Person|null $person
     * @Doctrine\ORM\Mapping\ManyToOne(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\Person",
     *     inversedBy="positions"
     * )
     * @Doctrine\ORM\Mapping\JoinColumn(name="person_id", referencedColumnName="id")
     */
    private $person;

    /**
     * Organization of this position.
     * @var Organization|null $organization
     * @Doctrine\ORM\Mapping\ManyToOne(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\Organization",
     *     inversedBy="positions"
     * )
     * @Doctrine\ORM\Mapping\JoinColumn(name="organization_id", referencedColumnName="id")
     */
    private $organization;

    /**
     * Type of position (student, employee, member...).
     * @var string|null
     * @Doctrine\ORM\Mapping\Column(type="string", nullable=true)
     */
    private $type;

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
        if ($person && $this->person !== $person) {
            $this->person = $person;
            $person->addPosition($this);
        }
    }

    /**
     * @return Organization
     */
    final public function getOrganization(): Organization
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
        if ($organization && $this->organization !== $organization) {
            $this->organization = $organization;
            $organization->addPosition($this);
        }
    }

    final public function isManager(): bool
    {
        $managerPositionTypes = new ArrayCollection($this::MANAGER_POSITION_TYPES);

        return $managerPositionTypes->contains($this->getType());
    }

    /**
     * @return string
     */
    final public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    final public function setType(?string $type): void
    {
        $this->type = $type;
    }
}
