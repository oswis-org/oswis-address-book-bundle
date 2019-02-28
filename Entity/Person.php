<?php

namespace Zakjakub\OswisAddressBookBundle\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Zakjakub\OswisAddressBookBundle\Entity\AbstractClass\AbstractPerson;
use Zakjakub\OswisCoreBundle\Entity\AbstractClass\AbstractRevision;
use Zakjakub\OswisCoreBundle\Entity\Address;
use Zakjakub\OswisCoreBundle\Entity\AppUser;

/**
 * Class Person
 *
 * Represents a person.
 *
 * @Doctrine\ORM\Mapping\Entity()
 * @Doctrine\ORM\Mapping\Table(name="address_book_person")
 * @ApiResource(
 *   iri="http://schema.org/Person"
 * )
 * @ApiFilter(OrderFilter::class)
 * @ApiFilter(SearchFilter::class, properties={"id": "exact", "name": "ipartial", "familyName": "ipartial"})
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

    public function __construct(
        ?Address $address = null,
        ?string $fullName = null,
        ?string $email = null,
        ?string $phone = null,
        ?string $description = null,
        ?\DateTime $birthDate = null,
        ?string $note = null,
        ?string $url = null
    ) {
        $revision = new PersonRevision();
        $this->positions = new ArrayCollection();
        $this->revisions = new ArrayCollection();
        $revision->setFullName($fullName);
        $revision->setEmail($email);
        $revision->setPhone($phone);
        $revision->setDescription($description);
        $revision->setBirthDate($birthDate);
        $revision->setNote($note);
        $revision->setUrl($url);
        $this->addRevision($revision);
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

    final public function getRevisionByDate(?\DateTime $dateTime = null): PersonRevision
    {
        $revision = $this->getRevision($dateTime);
        \assert($revision instanceof PersonRevision);

        return $revision;
    }


    final public function addPosition(Position $position): void
    {
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
