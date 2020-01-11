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
use Zakjakub\OswisAddressBookBundle\Entity\AbstractClass\AbstractPerson;
use Zakjakub\OswisCoreBundle\Filter\SearchAnnotation as Searchable;
use function assert;
use function rtrim;
use function trim;

/**
 * Class Person. Represents a person.
 *
 * @Doctrine\ORM\Mapping\Entity(repositoryClass="Zakjakub\OswisAddressBookBundle\Repository\PersonRepository")
 * @Doctrine\ORM\Mapping\Table(name="address_book_person")
 * @ApiResource(
 *   iri="http://schema.org/Person",
 *   attributes={
 *     "filters"={"search"},
 *     "access_control"="is_granted('ROLE_MANAGER')"
 *   },
 *   collectionOperations={
 *     "get"={
 *       "access_control"="is_granted('ROLE_MANAGER')",
 *       "normalization_context"={"groups"={"address_book_persons_get"}},
 *     },
 *     "post"={
 *       "access_control"="is_granted('ROLE_MANAGER')",
 *       "denormalization_context"={"groups"={"address_book_persons_post"}}
 *     }
 *   },
 *   itemOperations={
 *     "get"={
 *       "access_control"="is_granted('ROLE_MANAGER')",
 *       "normalization_context"={"groups"={"address_book_person_get"}},
 *     },
 *     "put"={
 *       "access_control"="is_granted('ROLE_MANAGER')",
 *       "denormalization_context"={"groups"={"address_book_person_put"}}
 *     },
 *     "delete"={
 *       "access_control"="is_granted('ROLE_ADMIN')",
 *       "denormalization_context"={"groups"={"address_book_person_delete"}}
 *     }
 *   }
 * )
 * @ApiFilter(OrderFilter::class)
 * @Searchable({
 *     "id",
 *     "fullName",
 *     "name",
 *     "description",
 *     "note"
 * })
 * @Doctrine\ORM\Mapping\Cache(usage="NONSTRICT_READ_WRITE", region="address_book_contact")
 */
class Person extends AbstractPerson
{
    /**
     * @Doctrine\ORM\Mapping\OneToMany(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\Position",
     *     mappedBy="person",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    protected ?Collection $positions = null;

    /**
     * @Doctrine\ORM\Mapping\OneToMany(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\PersonSkillConnection",
     *     mappedBy="person",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    protected ?Collection $personSkillConnections = null;

    public function __construct(
        ?string $fullName = null,
        ?string $description = null,
        ?DateTime $birthDate = null,
        ?string $type = self::TYPE_PERSON,
        ?Collection $notes = null,
        ?Collection $contactDetails = null,
        ?Collection $addresses = null,
        ?Collection $positions = null,
        ?Collection $personSkillConnections = null,
        ?Collection $addressBooks = null
    ) {
        parent::__construct($fullName, $description, $birthDate, $type, $notes, $contactDetails, $addresses, $addressBooks, $positions);
        $this->setPersonSkillConnections($personSkillConnections);
    }

    public function addPosition(?Position $position): void
    {
        if (null !== $position && !$this->positions->contains($position)) {
            $this->positions->add($position);
            $position->setPerson($this);
        }
    }

    public function removePosition(?Position $position): void
    {
        if (null !== $position && $this->positions->removeElement($position)) {
            $position->setOrganization(null);
            $position->setPerson(null);
        }
    }

    public function getEmployers(?DateTime $dateTime = null): Collection
    {
        $out = new ArrayCollection();
        $this->getMemberAndEmployeePositions($dateTime)->map(fn(AbstractContact $c) => $out->contains($c) ? null : $out->add($c));

        return $out;
    }

    public function getSchools(?DateTime $dateTime = null): Collection
    {
        $out = new ArrayCollection();
        $this->getStudyPositions($dateTime)->map(fn(AbstractContact $c) => $out->contains($c) ? null : $out->add($c));

        return $out;
    }

    public function addPersonSkillConnection(?PersonSkillConnection $personSkillConnection): void
    {
        if (null !== $personSkillConnection && !$this->personSkillConnections->contains($personSkillConnection)) {
            $this->personSkillConnections->add($personSkillConnection);
            $personSkillConnection->setPerson($this);
        }
    }

    public function removePersonSkillConnection(?PersonSkillConnection $personSkillConnection): void
    {
        if ($personSkillConnection && $this->personSkillConnections->removeElement($personSkillConnection)) {
            $personSkillConnection->setPersonSkill(null);
            $personSkillConnection->setPerson(null);
        }
    }

    public function getOrganizationsString(): string
    {
        $output = '';
        foreach ($this->getPositions(new DateTime(), null, true) as $position) {
            assert($position instanceof Position);
            $output .= (!empty($output) ? ', ' : null).$position->getEmployerString();
        }

        return preg_replace('!\s+!', ' ', rtrim(trim(preg_replace('/[,]+/', ',', $output)), ','));
    }

    public function getPersonSkillConnections(): Collection
    {
        return $this->personSkillConnections ?? new ArrayCollection();
    }

    public function setPersonSkillConnections(?Collection $newPersonSkillConnections): void
    {
        $this->personSkillConnections ??= new ArrayCollection();
        $newPersonSkillConnections ??= new ArrayCollection();
        foreach ($this->personSkillConnections as $oldPersonSkillConnection) {
            if (!$newPersonSkillConnections->contains($oldPersonSkillConnection)) {
                $this->personSkillConnections->removeElement($oldPersonSkillConnection);
            }
        }
        foreach ($newPersonSkillConnections as $newPersonSkillConnection) {
            if (!$this->personSkillConnections->contains($newPersonSkillConnection)) {
                $this->addPersonSkillConnection($newPersonSkillConnection);
            }
        }
    }

    public function getSortableContactName(): string
    {
        return $this->getFamilyName().' '.$this->getAdditionalName().' '.$this->getGivenName().' '.$this->getHonorificSuffix().' '.$this->getHonorificPrefix();
    }
}
