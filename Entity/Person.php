<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use OswisOrg\OswisAddressBookBundle\Entity\AbstractClass\AbstractPerson;
use OswisOrg\OswisCoreBundle\Entity\AppUser\AppUser;
use OswisOrg\OswisCoreBundle\Entity\NonPersistent\Nameable;
use OswisOrg\OswisCoreBundle\Exceptions\InvalidTypeException;
use function assert;
use function rtrim;
use function trim;

/**
 * @Doctrine\ORM\Mapping\Entity(repositoryClass="OswisOrg\OswisAddressBookBundle\Repository\PersonRepository")
 * @Doctrine\ORM\Mapping\Table(name="address_book_person")
 * @ApiPlatform\Core\Annotation\ApiResource(
 *   iri="http://schema.org/Person",
 *   attributes={
 *     "filters"={"search"},
 *     "security"="is_granted('ROLE_MEMBER')"
 *   },
 *   collectionOperations={
 *     "get"={
 *       "security"="is_granted('ROLE_MEMBER')",
 *       "normalization_context"={"groups"={"entities_get", "address_book_abstract_contacts_get", "address_book_persons_get"}},
 *     },
 *     "post"={
 *       "security"="is_granted('ROLE_MEMBER')",
 *       "denormalization_context"={"groups"={"entities_post", "address_book_abstract_contacts_post", "address_book_persons_post"}}
 *     }
 *   },
 *   itemOperations={
 *     "get"={
 *       "security"="is_granted('ROLE_MEMBER')",
 *       "normalization_context"={"groups"={"entity_get", "address_book_abstract_contact_get", "address_book_person_get"}},
 *     },
 *     "put"={
 *       "security"="is_granted('ROLE_MEMBER')",
 *       "denormalization_context"={"groups"={"entity_put", "address_book_abstract_contact_put", "address_book_person_put"}}
 *     }
 *   }
 * )
 * @ApiPlatform\Core\Annotation\ApiFilter(ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter::class, properties={
 *     "id": "ASC",
 *     "slug",
 *     "description",
 *     "contactName",
 *     "sortableName",
 *     "note",
 *     "birthDate"
 * })
 * @ApiPlatform\Core\Annotation\ApiFilter(ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter::class, properties={
 *     "id": "exact",
 *     "description": "partial",
 *     "slug": "partial",
 *     "contactName": "partial",
 *     "note": "partial",
 *     "birthDate": "partial"
 * })
 * @OswisOrg\OswisCoreBundle\Filter\SearchAnnotation({
 *     "id",
 *     "slug",
 *     "contactName",
 *     "sortableName",
 *     "description",
 *     "note",
 *     "birthDate"
 * })
 * @ApiPlatform\Core\Annotation\ApiFilter(
 *     ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter::class, properties={"createdDateTime", "updatedDateTime", "birthDate"}
 * )
 * @Doctrine\ORM\Mapping\Cache(usage="NONSTRICT_READ_WRITE", region="address_book_contact")
 */
class Person extends AbstractPerson
{
    /**
     * @Doctrine\ORM\Mapping\OneToMany(
     *     targetEntity="OswisOrg\OswisAddressBookBundle\Entity\Position", mappedBy="person", cascade={"all"}, orphanRemoval=true
     * )
     */
    protected ?Collection $positions = null;

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
        $this->setPositions($positions);
        $this->setAppUser($appUser);
    }

    public function getMemberPositions(?DateTime $dateTime = null): Collection
    {
        return $this->getPositions($dateTime, Position::MEMBER_POSITION_TYPES);
    }

    public function getPositions(?DateTime $dateTime = null, ?array $types = null): Collection
    {
        $positions = $this->positions ?? new ArrayCollection();
        if (null !== $dateTime) {
            $positions = $positions->filter(fn(Position $p): bool => $p->isInDateRange($dateTime));
        }
        if (!empty($types)) {
            $positions = $positions->filter(fn(Position $position): bool => in_array($position->getType(), $types, true));
        }

        return $positions;
    }

    public function setPositions(?Collection $newPositions): void
    {
        $this->positions ??= new ArrayCollection();
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
        return $this->getPositions($dateTime, Position::STUDY_POSITION_TYPES);
    }

    public function getRegularPositions(): Collection
    {
        return $this->getPositions()->filter(fn(Position $position) => $position->isRegularPosition());
    }

    /**
     * @param Position|null $position
     *
     * @throws InvalidTypeException
     */
    public function addStudy(?Position $position): void
    {
        if (null === $position) {
            return;
        }
        if (false === $position->isStudy()) {
            throw new InvalidTypeException('Špatný typ pozice ('.$position->getType().' není typ studia)');
        }
        $this->addPosition($position);
    }

    public function addPosition(?Position $position): void
    {
        if (null !== $position && !$this->positions->contains($position)) {
            $this->positions->add($position);
            $position->setPerson($this);
        }
    }

    /**
     * @param Position|null $position
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
     * @param Position|null $position
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
        if (null !== $position && $this->positions->removeElement($position)) {
            $position->setOrganization(null);
            $position->setPerson(null);
        }
    }

    /**
     * @param Position|null $position
     *
     * @throws InvalidTypeException
     */
    public function removeRegularPosition(?Position $position): void
    {
        if (null !== $position && !$position->isRegularPosition()) {
            throw new InvalidTypeException('Špatný typ pozice ('.$position->getType().' není typ zaměstnání)');
        }
        $this->removePosition($position);
    }

    public function getEmployers(?DateTime $dateTime = null): Collection
    {
        $out = new ArrayCollection();
        $this->getMemberAndEmployeePositions($dateTime)->map(
            fn(Position $p) => $out->contains($p->getOrganization()) ? null : $out->add($p->getOrganization())
        );

        return $out;
    }

    public function getMemberAndEmployeePositions(?DateTime $dateTime = null): Collection
    {
        return $this->getPositions($dateTime, Position::EMPLOYEE_MEMBER_POSITION_TYPES);
    }

    public function getSchools(?DateTime $dateTime = null): Collection
    {
        $out = new ArrayCollection();
        $this->getStudies($dateTime)->map(fn(Position $p) => $out->contains($p->getOrganization()) ? null : $out->add($p->getOrganization()));

        return $out;
    }

    public function getStudies(?DateTime $dateTime = null): Collection
    {
        return $this->getPositions($dateTime, Position::STUDY_POSITION_TYPES);
    }

    public function getOrganizationsString(): string
    {
        $output = '';
        foreach ($this->getPositions(new DateTime(), null) as $position) {
            assert($position instanceof Position);
            $output .= (!empty($output) ? ', ' : null).$position->getEmployerName();
        }

        return preg_replace('!\s+!', ' ', rtrim(trim(preg_replace('/[,]+/', ',', $output)), ','));
    }

    public function getContactPersons(bool $onlyWithActivatedUser = false): Collection
    {
        return $onlyWithActivatedUser && !$this->hasActivatedUser() ? new ArrayCollection() : new ArrayCollection([$this]);
    }
}
