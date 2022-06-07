<?php
/**
 * @noinspection PhpUnused
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use OswisOrg\OswisAddressBookBundle\Entity\AbstractClass\AbstractPerson;
use OswisOrg\OswisAddressBookBundle\Repository\PersonRepository;
use OswisOrg\OswisCoreBundle\Entity\AppUser\AppUser;
use OswisOrg\OswisCoreBundle\Entity\NonPersistent\Nameable;
use OswisOrg\OswisCoreBundle\Exceptions\InvalidTypeException;
use OswisOrg\OswisCoreBundle\Filter\SearchFilter;

use function assert;
use function rtrim;
use function trim;

/**
 * @OswisOrg\OswisCoreBundle\Filter\SearchAnnotation({
 *     "id",
 *     "slug",
 *     "name",
 *     "sortableName",
 *     "shortName",
 *     "description",
 *     "note",
 *     "birthDate"
 * })
 * @ApiPlatform\Core\Annotation\ApiResource(
 *   iri="http://schema.org/Person",
 *   attributes={
 *     "filters"={"search"},
 *     "security"="is_granted('ROLE_MEMBER')"
 *   },
 *   collectionOperations={
 *     "get"={
 *       "security"="is_granted('ROLE_MANAGER')",
 *       "normalization_context"={"groups"={"entities_get", "address_book_abstract_contacts_get", "address_book_persons_get"}},
 *     },
 *     "post"={
 *       "security"="is_granted('ROLE_MANAGER')",
 *       "denormalization_context"={"groups"={"entities_post", "address_book_abstract_contacts_post", "address_book_persons_post"}}
 *     }
 *   },
 *   itemOperations={
 *     "get"={
 *       "security"="is_granted('ROLE_MEMBER')",
 *       "normalization_context"={"groups"={"entity_get", "address_book_abstract_contact_get", "address_book_person_get"}},
 *     },
 *     "put"={
 *       "security"="is_granted('ROLE_MANAGER')",
 *       "denormalization_context"={"groups"={"entity_put", "address_book_abstract_contact_put", "address_book_person_put"}}
 *     }
 *   }
 * )
 */
#[Entity(repositoryClass: PersonRepository::class)]
#[Table(name: 'address_book_person')]
#[Cache(usage: 'NONSTRICT_READ_WRITE', region: 'address_book_contact')]
#[ApiFilter(DateFilter::class, properties: ["createdAt", "updatedAt", "birthDate"])]
#[ApiFilter(SearchFilter::class, properties: [
    "id"          => "exact",
    "description" => "partial",
    "slug"        => "partial",
    "note"        => "partial",
    "birthDate"   => "partial",
])]
#[ApiFilter(OrderFilter::class, properties: [
    "id" => "ASC",
    "slug",
    "description",
    "sortableName",
    "note",
    "birthDate",
])]
class Person extends AbstractPerson
{
    /**
     * @var Collection<Position>
     */
    #[OneToMany(mappedBy: 'person', targetEntity: Position::class, cascade: ['all'], orphanRemoval: true)]
    protected Collection $positions;

    public function __construct(
        ?Nameable $nameable = null,
        ?Collection $notes = null,
        ?Collection $details = null,
        ?Collection $addresses = null,
        ?Collection $positions = null,
        ?Collection $addressBooks = null,
        ?AppUser $appUser = null
    ) {
        parent::__construct($nameable, $notes, $details, $addresses, $addressBooks);
        $this->positions = new ArrayCollection();
        $this->setPositions($positions);
        $this->setAppUser($appUser);
    }

    public function getMemberPositions(?DateTime $dateTime = null): Collection
    {
        return $this->getPositions($dateTime, Position::MEMBER_POSITION_TYPES);
    }

    public function getPositions(?DateTime $dateTime = null, ?array $types = null): Collection
    {
        $positions = $this->positions;
        if (null !== $dateTime) {
            $positions = $positions->filter(fn(mixed $p): bool => $p instanceof Position && $p->isInDateRange($dateTime));
        }
        if (!empty($types)) {
            $positions = $positions->filter(fn(mixed $p): bool => $p instanceof Position && in_array($p->getType(), $types, true));
        }

        return $positions;
    }

    public function setPositions(?Collection $newPositions): void
    {
        /** @var Collection<Position>|null $newPositions */
        $newPositions ??= new ArrayCollection();
        foreach ($this->positions as $oldPosition) {
            if (!$newPositions->contains($oldPosition)) {
                $this->removePosition($oldPosition);
            }
        }
        foreach ($newPositions as $newPosition) {
            if (!$this->positions->contains($newPosition)) {
                $this->addPosition($newPosition);
            }
        }
    }

    public function getEmployeePositions(?DateTime $dateTime = null): Collection
    {
        return $this->getPositions($dateTime, Position::EMPLOYEE_POSITION_TYPES);
    }

    public function getManagerPositions(?DateTime $dateTime = null): Collection
    {
        return $this->getPositions($dateTime, Position::MANAGER_POSITION_TYPES);
    }

    public function getRegularPositions(): Collection
    {
        return $this->getPositions()->filter(fn(mixed $position) => $position instanceof Position && $position->isRegularPosition());
    }

    /**
     * @param  Position|null  $position
     *
     * @throws InvalidTypeException
     */
    public function addStudy(?Position $position): void
    {
        if (null === $position) {
            return;
        }
        if (false === $position->isStudy()) {
            $positionType = $position->getType();
            throw new InvalidTypeException("Špatný typ pozice ($positionType není typ studia)");
        }
        $this->addPosition($position);
    }

    public function addPosition(?Position $position): void
    {
        if (null !== $position && !$this->getPositions()->contains($position)) {
            $this->getPositions()->add($position);
            $position->setPerson($this);
        }
    }

    /**
     * @param  Position|null  $position
     *
     * @throws InvalidTypeException
     */
    public function addRegularPosition(?Position $position): void
    {
        if (null === $position) {
            return;
        }
        if (false === $position->isRegularPosition()) {
            $type = $position->getType();
            throw new InvalidTypeException("Špatný typ pozice ($type není typ zaměstnání)");
        }
        $this->addPosition($position);
    }

    /**
     * @param  Position|null  $position
     *
     * @throws InvalidTypeException
     */
    public function removeStudy(?Position $position): void
    {
        if (null !== $position && !$position->isStudy()) {
            throw new InvalidTypeException('Špatný typ pozice ('.$position->getType().' není typ studia)');
        }
        $this->removePosition($position);
    }

    public function removePosition(?Position $position): void
    {
        if (null !== $position && $this->getPositions()->removeElement($position)) {
            $position->setOrganization(null);
            $position->setPerson(null);
        }
    }

    /**
     * @param  Position|null  $position
     *
     * @throws InvalidTypeException
     */
    public function removeRegularPosition(?Position $position): void
    {
        if (null !== $position && !$position->isRegularPosition()) {
            $positionType = $position->getType();
            throw new InvalidTypeException("Špatný typ pozice ($positionType není typ zaměstnání)");
        }
        $this->removePosition($position);
    }

    public function getEmployers(?DateTime $dateTime = null): Collection
    {
        /** @var Collection<Organization> $employers */
        $employers = new ArrayCollection();
        $this->getMemberAndEmployeePositions($dateTime)->map(function (mixed $position) use ($employers) {
            if ($position instanceof Position && !$employers->contains($position->getOrganization())) {
                $employers->add($position->getOrganization());
            }
        });

        return $employers;
    }

    public function getMemberAndEmployeePositions(?DateTime $dateTime = null): Collection
    {
        return $this->getPositions($dateTime, Position::EMPLOYEE_MEMBER_POSITION_TYPES);
    }

    public function getSchools(?DateTime $dateTime = null): Collection
    {
        /** @var Collection<Organization> $schools */
        $schools = new ArrayCollection();
        $this->getStudies($dateTime)->map(function (mixed $position) use ($schools) {
            if ($position instanceof Position && !$schools->contains($position->getOrganization())) {
                $schools->add($position->getOrganization());
            }
        },);

        return $schools;
    }

    public function getStudies(?DateTime $dateTime = null): Collection
    {
        return $this->getPositions($dateTime, Position::STUDY_POSITION_TYPES);
    }

    public function getOrganizationsString(): string
    {
        $output = '';
        foreach ($this->getPositions(new DateTime()) as $position) {
            assert($position instanceof Position);
            $output .= (!empty($output) ? ', ' : null).$position->getEmployerName();
        }
        $output = preg_replace('/,+/', ',', $output);
        $output = preg_replace('!\s+!', ' ', rtrim(trim(''.$output), ','));

        return ''.$output;
    }

    public function getContactPersons(bool $onlyWithActivatedUser = false): Collection
    {
        return $onlyWithActivatedUser && !$this->hasActivatedUser() ? new ArrayCollection() : new ArrayCollection([$this]);
    }
}
