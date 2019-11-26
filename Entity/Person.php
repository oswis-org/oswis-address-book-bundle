<?php /** @noinspection PhpUnused */

namespace Zakjakub\OswisAddressBookBundle\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Exception;
use Zakjakub\OswisAddressBookBundle\Entity\AbstractClass\AbstractPerson;
use Zakjakub\OswisCoreBundle\Entity\AbstractClass\AbstractRevision;
use Zakjakub\OswisCoreBundle\Exceptions\RevisionMissingException;
use Zakjakub\OswisCoreBundle\Filter\SearchAnnotation as Searchable;
use function assert;
use function rtrim;
use function trim;

/**
 * Class Person
 *
 * Represents a person.
 *
 * @Doctrine\ORM\Mapping\Entity()
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
     * @var Collection
     * @Doctrine\ORM\Mapping\OneToMany(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\PersonRevision",
     *     mappedBy="container",
     *     cascade={"all"},
     *     orphanRemoval=true,
     *     fetch="EAGER"
     * )
     */
    protected Collection $revisions;

    /**
     * @var AbstractRevision|null
     * @Doctrine\ORM\Mapping\ManyToOne(targetEntity="Zakjakub\OswisAddressBookBundle\Entity\PersonRevision")
     * @Doctrine\ORM\Mapping\JoinColumn(name="active_revision_id", referencedColumnName="id")
     */
    protected ?AbstractRevision $activeRevision;

    /**
     * Positions (jobs, studies...).
     * @var Collection|null $positions
     * @Doctrine\ORM\Mapping\OneToMany(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\Position",
     *     mappedBy="person",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    private ?Collection $positions;

    /**
     * Connections to skills.
     * @var Collection|null $positions
     * @Doctrine\ORM\Mapping\OneToMany(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\PersonSkillConnection",
     *     mappedBy="person",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    private ?Collection $personSkillConnections;

    /**
     * Person constructor.
     *
     * @param string|null       $fullName
     * @param string|null       $description
     * @param DateTime|null     $birthDate
     * @param string|null       $type
     * @param Collection|null   $notes
     * @param Collection|null   $contactDetails
     * @param Collection|null   $addresses
     * @param ContactImage|null $image
     * @param Collection|null   $positions
     * @param Collection|null   $personSkillConnections
     *
     * @param Collection|null   $addressBooks
     *
     * @throws Exception
     */
    public function __construct(
        ?string $fullName = null,
        ?string $description = null,
        ?DateTime $birthDate = null,
        ?string $type = self::TYPE_PERSON,
        ?Collection $notes = null,
        ?Collection $contactDetails = null,
        ?Collection $addresses = null,
        ?ContactImage $image = null,
        ?Collection $positions = null,
        ?Collection $personSkillConnections = null,
        ?Collection $addressBooks = null
    ) {
        parent::__construct($fullName, $description, $birthDate, $type, $notes, $contactDetails, $addresses, $image, $addressBooks);
        $this->revisions = new ArrayCollection();
        $this->setPositions($positions);
        $this->setPersonSkillConnections($personSkillConnections);
    }

    /**
     * @param AbstractRevision|null $revision
     */
    public static function checkRevision(?AbstractRevision $revision): void
    {
        assert($revision instanceof PersonRevision);
    }

    /**
     * @return string
     */
    public static function getRevisionClassName(): string
    {
        return PersonRevision::class;
    }

    /**
     * @param DateTime|null $dateTime
     *
     * @return PersonRevision
     * @throws RevisionMissingException
     */
    final public function getRevisionByDate(?DateTime $dateTime = null): PersonRevision
    {
        $revision = $this->getRevision($dateTime);
        assert($revision instanceof PersonRevision);

        return $revision;
    }

    final public function addPosition(?Position $position): void
    {
        if (!$position) {
            return;
        }
        if (!$this->positions->contains($position)) {
            $this->positions->add($position);
            $position->setPerson($this);
        }
    }

    final public function removePosition(?Position $position): void
    {
        if (!$position) {
            return;
        }
        if ($this->positions->removeElement($position)) {
            $position->setOrganization(null);
            $position->setPerson(null);
        }
    }

    final public function addPersonSkillConnection(?PersonSkillConnection $personSkillConnection): void
    {
        if (!$personSkillConnection) {
            return;
        }
        if (!$this->personSkillConnections->contains($personSkillConnection)) {
            $this->personSkillConnections->add($personSkillConnection);
            $personSkillConnection->setPerson($this);
        }
    }

    final public function removePersonSkillConnection(?PersonSkillConnection $personSkillConnection): void
    {
        if (!$personSkillConnection) {
            return;
        }
        if ($this->personSkillConnections->removeElement($personSkillConnection)) {
            $personSkillConnection->setPersonSkill(null);
            $personSkillConnection->setPerson(null);
        }
    }

    /**
     * @return string
     */
    final public function getOrganizationsString(): string
    {
        $output = '';
        foreach ($this->getPositions() as $position) {
            assert($position instanceof Position);
            if ($output !== '') {
                $output .= ', ';
            }
            $output .= $position->getEmployerString();
        }
        $output = preg_replace('/[,]+/', ',', $output);
        $output = trim($output);
        $output = rtrim($output, ',');
        $output = preg_replace('!\s+!', ' ', $output);

        return $output;
    }

    final public function getPositions(): Collection
    {
        return $this->positions;
    }

    final public function setPositions(?Collection $newPositions): void
    {
        if (!$this->positions) {
            $this->positions = new ArrayCollection();
        }
        if (!$newPositions) {
            $newPositions = new ArrayCollection();
        }
        foreach ($this->positions as $oldPosition) {
            if (!$newPositions->contains($oldPosition)) {
                $this->removePosition($oldPosition);
            }
        }
        if ($newPositions) {
            foreach ($newPositions as $newPosition) {
                if (!$this->positions->contains($newPosition)) {
                    $this->addPosition($newPosition);
                }
            }
        }
    }

    final public function getPersonSkillConnections(): Collection
    {
        return $this->personSkillConnections;
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

    /** @noinspection MethodShouldBeFinalInspection */
    public function getSortableContactName(): string
    {
        return $this->getFamilyName().' '.$this->getGivenName();
    }
}
