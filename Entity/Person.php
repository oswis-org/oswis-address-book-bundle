<?php
/**
 * @noinspection PhpUnused
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use InvalidArgumentException;
use OswisOrg\OswisAddressBookBundle\Entity\AbstractClass\AbstractPerson;
use OswisOrg\OswisCoreBundle\Entity\AppUser;
use OswisOrg\OswisCoreBundle\Entity\Publicity;
use OswisOrg\OswisCoreBundle\Filter\SearchAnnotation as Searchable;
use Vokativ\Name as VokativName;
use function assert;
use function rtrim;
use function trim;

/**
 * @Doctrine\ORM\Mapping\Entity(repositoryClass="OswisOrg\OswisAddressBookBundle\Repository\PersonRepository")
 * @Doctrine\ORM\Mapping\Table(name="address_book_person")
 * @ApiResource(
 *   iri="http://schema.org/Person",
 *   attributes={
 *     "filters"={"search"},
 *     "access_control"="is_granted('ROLE_MEMBER')"
 *   },
 *   collectionOperations={
 *     "get"={
 *       "access_control"="is_granted('ROLE_MEMBER')",
 *       "normalization_context"={"groups"={"address_book_abstract_contacts_get", "address_book_persons_get"}},
 *     },
 *     "post"={
 *       "access_control"="is_granted('ROLE_MEMBER')",
 *       "denormalization_context"={"groups"={"address_book_abstract_contacts_post", "address_book_persons_post"}}
 *     }
 *   },
 *   itemOperations={
 *     "get"={
 *       "access_control"="is_granted('ROLE_MEMBER')",
 *       "normalization_context"={"groups"={"address_book_abstract_contact_get", "address_book_person_get"}},
 *     },
 *     "put"={
 *       "access_control"="is_granted('ROLE_MEMBER')",
 *       "denormalization_context"={"groups"={"address_book_abstract_contact_put", "address_book_person_put"}}
 *     }
 *   }
 * )
 * @ApiFilter(OrderFilter::class, properties={
 *     "id": "ASC",
 *     "slug",
 *     "description",
 *     "contactName",
 *     "sortableName",
 *     "note",
 *     "birthDate"
 * })
 * @ApiFilter(SearchFilter::class, properties={
 *     "id": "exact",
 *     "description": "partial",
 *     "slug": "partial",
 *     "contactName": "partial",
 *     "note": "partial",
 *     "birthDate": "partial"
 * })
 * @Searchable({
 *     "id",
 *     "slug",
 *     "contactName",
 *     "sortableName",
 *     "description",
 *     "note",
 *     "birthDate"
 * })
 * @ApiFilter(DateFilter::class, properties={"createdDateTime", "updatedDateTime", "birthDate"})
 * @Doctrine\ORM\Mapping\Cache(usage="NONSTRICT_READ_WRITE", region="address_book_contact")
 */
class Person extends AbstractPerson
{
    /**
     * @Doctrine\ORM\Mapping\OneToMany(
     *     targetEntity="OswisOrg\OswisAddressBookBundle\Entity\Position",
     *     mappedBy="person",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    protected ?Collection $positions = null;

//    /**
//     * @Doctrine\ORM\Mapping\OneToMany(
//     *     targetEntity="OswisOrg\OswisAddressBookBundle\Entity\PersonSkillConnection",
//     *     mappedBy="person",
//     *     cascade={"all"},
//     *     orphanRemoval=true,
//     *     fetch="EAGER"
//     * )
//     */
//    protected ?Collection $personSkillConnections = null;
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
        ?Collection $addressBooks = null,
        ?AppUser $appUser = null,
        ?Publicity $publicity = null
    ) {
        $type ??= self::TYPE_PERSON;
        parent::__construct($fullName, $description, $birthDate, $type, $notes, $contactDetails, $addresses, $addressBooks, $positions, $publicity);
        $this->setAppUser($appUser);
        $this->setPersonSkillConnections($personSkillConnections);
    }

    public function setPersonSkillConnections(?Collection $newPersonSkillConnections): void
    {
//        $this->personSkillConnections ??= new ArrayCollection();
//        $newPersonSkillConnections ??= new ArrayCollection();
//        foreach ($this->personSkillConnections as $oldPersonSkillConnection) {
//            if (!$newPersonSkillConnections->contains($oldPersonSkillConnection)) {
//                $this->personSkillConnections->removeElement($oldPersonSkillConnection);
//            }
//        }
//        foreach ($newPersonSkillConnections as $newPersonSkillConnection) {
//            if (!$this->personSkillConnections->contains($newPersonSkillConnection)) {
//                $this->addPersonSkillConnection($newPersonSkillConnection);
//            }
//        }
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
        $this->getMemberAndEmployeePositions($dateTime)
            ->map(fn(Position $p) => $out->contains($p->getOrganization()) ? null : $out->add($p->getOrganization()));

        return $out;
    }

//    public function addPersonSkillConnection(?PersonSkillConnection $personSkillConnection): void
//    {
//        if (null !== $personSkillConnection && !$this->personSkillConnections->contains($personSkillConnection)) {
//            $this->personSkillConnections->add($personSkillConnection);
//            $personSkillConnection->setPerson($this);
//        }
//    }
//    public function removePersonSkillConnection(?PersonSkillConnection $personSkillConnection): void
//    {
//        if ($personSkillConnection && $this->personSkillConnections->removeElement($personSkillConnection)) {
//            $personSkillConnection->setPersonSkill(null);
//            $personSkillConnection->setPerson(null);
//        }
//    }
    public function getSchools(?DateTime $dateTime = null): Collection
    {
        $out = new ArrayCollection();
        $this->getStudies($dateTime)
            ->map(fn(Position $p) => $out->contains($p->getOrganization()) ? null : $out->add($p->getOrganization()));

        return $out;
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
        return new ArrayCollection();
        // return $this->personSkillConnections ?? new ArrayCollection();
    }

    public function getSortableContactName(): string
    {
        return $this->getFamilyName().' '.$this->getAdditionalName().' '.$this->getGivenName().' '.$this->getHonorificSuffix().' '.$this->getHonorificPrefix();
    }

    public function getGender(): string
    {
        if (empty($this->getGivenName())) {
            return self::GENDER_UNISEX;
        }
        try {
            return (new VokativName())->isMale($this->getGivenName()) ? self::GENDER_MALE : self::GENDER_FEMALE;
        } catch (InvalidArgumentException $e) {
            return self::GENDER_UNISEX;
        }
    }
}
