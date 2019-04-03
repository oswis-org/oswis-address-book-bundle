<?php

namespace Zakjakub\OswisAddressBookBundle\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Zakjakub\OswisAddressBookBundle\Entity\AbstractClass\AbstractPerson;
use Zakjakub\OswisCoreBundle\Entity\AbstractClass\AbstractRevision;
use Zakjakub\OswisCoreBundle\Entity\AppUser;
use Zakjakub\OswisCoreBundle\Filter\SearchAnnotation as Searchable;

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
    protected $revisions;

    /**
     * @var AppUser|null $appUser User
     * @Doctrine\ORM\Mapping\OneToOne(
     *     targetEntity="Zakjakub\OswisCoreBundle\Entity\AppUser"
     * )
     */
    private $appUser;

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
    private $positions;

    /**
     * Person constructor.
     *
     * @param string|null       $fullName
     * @param string|null       $description
     * @param \DateTime|null    $birthDate
     * @param string|null       $type
     * @param Collection|null   $notes
     * @param Collection|null   $contactDetails
     * @param Collection|null   $addresses
     * @param ContactImage|null $image
     *
     * @throws \Exception
     */
    public function __construct(
        ?string $fullName = null,
        ?string $description = null,
        ?\DateTime $birthDate = null,
        ?string $type = null,
        ?Collection $notes = null,
        ?Collection $contactDetails = null,
        ?Collection $addresses = null,
        ?ContactImage $image = null
    ) {
        parent::__construct($type, $notes, $contactDetails, $addresses, $image);
        $this->positions = new ArrayCollection();
        $this->revisions = new ArrayCollection();
        $this->addRevision(new PersonRevision($fullName, $description, $birthDate));
    }

    /**
     * @param AbstractRevision|null $revision
     */
    public static function checkRevision(?AbstractRevision $revision): void
    {
        \assert($revision instanceof PersonRevision);
    }

    /**
     * @return string
     */
    public static function getRevisionClassName(): string
    {
        return PersonRevision::class;
    }

    /**
     * @param \DateTime|null $dateTime
     *
     * @return PersonRevision
     * @throws \Zakjakub\OswisCoreBundle\Exceptions\RevisionMissingException
     */
    final public function getRevisionByDate(?\DateTime $dateTime = null): PersonRevision
    {
        $revision = $this->getRevision($dateTime);
        \assert($revision instanceof PersonRevision);

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

    /**
     * @return string
     */
    final public function getOrganizationsString(): string
    {
        $output = '';
        foreach ($this->getPositions() as $position) {
            \assert($position instanceof Position);
            if ($output !== '') {
                $output .= ', ';
            }
            $output .= $position->getEmployerString();
        }
        $output = preg_replace('/[,]+/', ',', $output);
        $output = \trim($output);
        $output = \rtrim($output, ',');
        $output = preg_replace('!\s+!', ' ', $output);

        return $output;
    }

    final public function getPositions(): Collection
    {
        return $this->positions;
    }

    /**
     * User associated with this contact.
     * @return AppUser
     */
    final public function getAppUser(): ?AppUser
    {
        return $this->appUser;
    }

    /**
     * @param AppUser|null $appUser
     */
    final public function setAppUser(?AppUser $appUser): void
    {
        if (!$appUser) {
            return;
        }
        if ($this->appUser !== $appUser) {
            $this->appUser = $appUser;
        }
    }
}
