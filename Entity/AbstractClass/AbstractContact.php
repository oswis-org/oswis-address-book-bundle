<?php

namespace Zakjakub\OswisAddressBookBundle\Entity\AbstractClass;

use ApiPlatform\Core\Annotation\ApiProperty;
use function assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use InvalidArgumentException;
use Zakjakub\OswisAddressBookBundle\Entity\ContactAddress;
use Zakjakub\OswisAddressBookBundle\Entity\ContactDetail;
use Zakjakub\OswisAddressBookBundle\Entity\ContactImage;
use Zakjakub\OswisAddressBookBundle\Entity\ContactImageConnection;
use Zakjakub\OswisAddressBookBundle\Entity\ContactNote;
use Zakjakub\OswisAddressBookBundle\Entity\Person;
use Zakjakub\OswisAddressBookBundle\Entity\Position;
use Zakjakub\OswisCoreBundle\Entity\AbstractClass\AbstractRevisionContainer;
use Zakjakub\OswisCoreBundle\Entity\AppUser;
use Zakjakub\OswisCoreBundle\Traits\Entity\BasicEntityTrait;

/**
 * Class Contact (abstract class for Person, Department, Organization)
 *
 * @Doctrine\ORM\Mapping\Entity()
 * @Doctrine\ORM\Mapping\Table(name="address_book_abstract_contact")
 * @Doctrine\ORM\Mapping\InheritanceType("JOINED")
 * @Doctrine\ORM\Mapping\DiscriminatorColumn(name="discriminator", type="text")
 * @Doctrine\ORM\Mapping\DiscriminatorMap({
 *   "address_book_person" = "Zakjakub\OswisAddressBookBundle\Entity\Person",
 *   "address_book_organization" = "Zakjakub\OswisAddressBookBundle\Entity\Organization"
 * })
 */
abstract class AbstractContact extends AbstractRevisionContainer
{
    use BasicEntityTrait;

    /**
     * @var ContactImage|null
     * @Doctrine\ORM\Mapping\ManyToOne(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\ContactImage",
     *     cascade={"all"},
     *     fetch="EAGER"
     * )
     * @Doctrine\ORM\Mapping\JoinColumn(nullable=true)
     * @ApiProperty(iri="http://schema.org/image")
     */
    public $image;

    /**
     * Images of person.
     *
     * @var Collection|null
     * @Doctrine\ORM\Mapping\OneToMany(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\ContactImageConnection",
     *     mappedBy="contact",
     *     cascade={"all"},
     *     orphanRemoval=true,
     *     fetch="EAGER"
     * )
     */
    protected $imageConnections;

    /**
     * @var string|null $type Type of contact (person, organization, school, department...)
     * @Doctrine\ORM\Mapping\Column(type="string", nullable=true)
     */
    protected $type;

    /**
     * Notes about person.
     *
     * @var Collection|null
     * @Doctrine\ORM\Mapping\OneToMany(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\ContactNote",
     *     mappedBy="contact",
     *     cascade={"all"},
     *     orphanRemoval=true,
     *     fetch="EAGER"
     * )
     */
    protected $notes;

    /**
     *  Contact details (e-mails, phones...)
     *
     * @var Collection|null
     * @Doctrine\ORM\Mapping\OneToMany(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\ContactDetail",
     *     mappedBy="contact",
     *     cascade={"all"},
     *     orphanRemoval=true,
     *     fetch="EAGER"
     * )
     */
    protected $contactDetails;

    /**
     * Postal addresses of AbstractContact (Person, Organization).
     *
     * @var Collection|null
     * @Doctrine\ORM\Mapping\OneToMany(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\ContactAddress",
     *     mappedBy="contact",
     *     cascade={"all"},
     *     orphanRemoval=true,
     *     fetch="EAGER"
     * )
     * @ApiProperty(iri="http://schema.org/address")
     */
    protected $addresses;

    /**
     * AbstractContact constructor.
     *
     * @param ContactImage|null $image
     * @param string|null       $type
     * @param Collection|null   $notes
     * @param Collection|null   $contactDetails
     * @param Collection|null   $addresses
     */
    public function __construct(
        ?string $type = null,
        ?Collection $notes = null,
        ?Collection $contactDetails = null,
        ?Collection $addresses = null,
        ?ContactImage $image = null
    ) {
        $this->image = $image;
        $this->setType($type);
        $this->setNotes($notes);
        $this->setContactDetails($contactDetails);
        $this->setAddresses($addresses);
    }

    /**
     * @param string|null $name
     */
    abstract public function setContactName(?string $name): void;

    /**
     * @param ContactNote|null $personNote
     */
    final public function addNote(?ContactNote $personNote): void
    {
        if (!$personNote) {
            return;
        }
        if (!$this->notes->contains($personNote)) {
            $this->notes->add($personNote);
        }
        $personNote->setContact($this);
    }

    /**
     * @param ContactNote|null $personNote
     */
    final public function removeNote(?ContactNote $personNote): void
    {
        if ($personNote && $this->notes->removeElement($personNote)) {
            $personNote->setContact(null);
        }
    }

    /**
     * @param ContactImageConnection|null $contactImageConnection
     */
    final public function addImageConnection(?ContactImageConnection $contactImageConnection): void
    {
        if (!$contactImageConnection) {
            return;
        }
        if (!$this->imageConnections->contains($contactImageConnection)) {
            $this->imageConnections->add($contactImageConnection);
        }
        $contactImageConnection->setContact($this);
    }

    /**
     * @param ContactImageConnection|null $contactImageConnection
     */
    final public function removeImageConnection(?ContactImageConnection $contactImageConnection): void
    {
        if ($contactImageConnection && $this->imageConnections->removeElement($contactImageConnection)) {
            $contactImageConnection->setContact(null);
        }
    }

    /**
     * @param ContactDetail|null $contactDetail
     */
    final public function removeContactDetail(?ContactDetail $contactDetail): void
    {
        if ($contactDetail && $this->contactDetails->removeElement($contactDetail)) {
            $contactDetail->setContact(null);
        }
    }

    /**
     * @param ContactAddress|null $address
     */
    final public function removeAddress(?ContactAddress $address): void
    {
        if (!$address) {
            return;
        }
        if ($this->addresses->removeElement($address)) {
            $address->setContact(null);
        }
    }

    /**
     * @return Collection
     */
    final public function getContactDetails(): Collection
    {
        return $this->contactDetails ?? new ArrayCollection();
    }

    final public function setContactDetails(?Collection $contactDetails): void
    {
        $this->contactDetails = new ArrayCollection();
        if ($contactDetails) {
            foreach ($contactDetails as $contactDetail) {
                assert($contactDetail instanceof ContactDetail);
                $this->addContactDetail($contactDetail);
            }
        }
    }

    /**
     * @param ContactDetail|null $contactDetail
     */
    final public function addContactDetail(?ContactDetail $contactDetail): void
    {
        if ($contactDetail && !$this->contactDetails->contains($contactDetail)) {
            $this->contactDetails->add($contactDetail);
            $contactDetail->setContact($this);
        }
    }

    /**
     * @return Collection
     */
    final public function getAddresses(): Collection
    {
        return $this->addresses ?? new ArrayCollection();
    }

    final public function setAddresses(?Collection $addresses): void
    {
        $this->addresses = new ArrayCollection();
        if ($addresses) {
            foreach ($addresses as $address) {
                assert($address instanceof ContactAddress);
                $this->addAddress($address);
            }
        }
    }

    /**
     * @param ContactAddress|null $address
     */
    final public function addAddress(?ContactAddress $address): void
    {
        if (!$address) {
            return;
        }
        if (!$this->addresses->contains($address)) {
            $this->addresses->add($address);
            $address->setContact($this);
        }
    }

    /**
     * @return Collection
     */
    final public function getNotes(): Collection
    {
        return $this->notes;
    }

    final public function setNotes(?Collection $notes): void
    {
        $this->notes = new ArrayCollection();
        if ($notes) {
            foreach ($notes as $note) {
                assert($note instanceof ContactNote);
                $this->addNote($note);
            }
        }
    }

    /**
     * @ApiProperty(iri="http://schema.org/url")
     * @return Collection Collection of URL addresses from contact details
     */
    final public function getUrls(): ?Collection
    {
        // TODO: Return Urls as strings.
        return new ArrayCollection();
    }

    /**
     * @ApiProperty(iri="http://schema.org/email")
     * @return Collection Collection of e-mail addresses from contact details
     */
    final public function getEmails(): ?Collection
    {
        // TODO: Return Emails as strings.
        return new ArrayCollection();
    }

    /**
     * @ApiProperty(iri="http://schema.org/telephone")
     * @return Collection Collection of telephone numbers of AbstractContact (Person or Organization)
     */
    final public function getTelephones(): ?Collection
    {
        // TODO: Return telephones as strings.
        return new ArrayCollection();
    }

    final public function getEmail(): ?string
    {
        $result = $this->contactDetails->filter(
            static function (ContactDetail $contactDetail) {
                return ($contactDetail->getContactType() && $contactDetail->getContactType()->getType() === 'email');
            }
        )->first();
        assert($result instanceof ContactDetail);

        return $result ? $result->getContent() : null;
    }

    final public function getUrl(): ?string
    {
        $result = $this->contactDetails->filter(
            static function (ContactDetail $contactDetail) {
                return ($contactDetail->getContactType() && $contactDetail->getContactType()->getType() === 'url');
            }
        )->first();
        assert($result instanceof ContactDetail);

        return $result ? $result->getContent() : null;
    }

    final public function getPhone(): ?string
    {
        $result = $this->contactDetails->filter(
            static function (ContactDetail $contactDetail) {
                return ($contactDetail->getContactType() && $contactDetail->getContactType()->getType() === 'phone');
            }
        )->first();
        assert($result instanceof ContactDetail);

        return $result ? $result->getContent() : null;
    }

    final public function getAddress(): ?string
    {
        return $this->contactDetails->first();
    }

    /**
     * @ApiProperty(iri="http://schema.org/legalName")
     * @return string (Official) Name of AbstractContact (Person or Organization)
     */
    final public function getLegalName(): string
    {
        return $this->getContactName();
    }

    /**
     * @return string
     */
    abstract public function getContactName(): string;

    /**
     * @param $user
     *
     * @return bool
     */
    final public function canRead(AppUser $user): bool
    {
        if (!($user instanceof AppUser)) { // User is not logged in.
            return false;
        }
        if ($user->hasRole('ROLE_MEMBER')) {
            return true;
        }
        if ($user->hasRole('ROLE_USER') && $user === $this->getUser()) {
            // User can read itself.
            return true;
        }

        return false;
    }

    /**
     * @return AppUser|null
     */
    final public function getUser(): ?AppUser
    {
        return null;
    }

    /**
     * @param $user
     *
     * @return bool
     */
    final public function canEdit(AppUser $user): bool
    {
        if (!($user instanceof AppUser)) {
            // User is not logged in.
            return false;
        }
        if ($user->hasRole('ROLE_MEMBER')) {
            return true;
        }
        if ($user->hasRole('ROLE_USER') && $user === $this->getUser()) {
            // User can read itself.
            return true;
        }

        return false;
    }

    /**
     * @param AppUser $user
     *
     * @return bool
     */
    final public function containsUserInPersons(AppUser $user): bool
    {
        return $this->getUsersOfPersons()->contains($user);
    }

    /**
     * @return Collection
     */
    final public function getUsersOfPersons(): Collection
    {
        $users = new ArrayCollection();
        $this->getPersons()->forAll(
            static function (Person $person) use ($users) {
                $users->add($person->getAppUser());
            }
        );

        return $users;
    }

    /**
     * @return Collection
     */
    final public function getPersons(): Collection
    {
        $persons = new ArrayCollection();
        if ($this instanceof Person) {
            $persons->add($this);
        } elseif ($this instanceof OrganizationRevision) {
            $this->getPositions()->forAll(
                static function (Person $person) use ($persons) {
                    $persons->add($person);
                }
            );
        }

        return $persons;
    }

    /**
     * @return ArrayCollection
     */
    final public function getManagedDepartments(): ArrayCollection
    {
        // TODO: Return managed departmenmts.
        return new ArrayCollection();
    }


    /**
     * @return null|string
     */
    final public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param null|string $type
     */
    final public function setType(?string $type): void
    {
        // $this->checkType($type);
        $this->type = $type;
    }

    /**
     * @param string $typeName
     *
     * @return bool
     */
    abstract public function checkType(string $typeName): bool;

    /**
     * @return string
     */
    final public function __toString(): string
    {
        return $this->getContactName();
    }

    /**
     * @return Collection
     */
    final public function getStudies(): Collection
    {
        return $this->getPositions()->filter(
            static function (Position $position) {
                return $position->isStudy();
            }
        );
    }

    /**
     * @return Collection
     */
    abstract public function getPositions(): Collection;

    /**
     * @return Collection
     */
    final public function getRegularPositions(): Collection
    {
        return $this->getPositions()->filter(
            static function (Position $position) {
                return $position->isRegularPosition();
            }
        );
    }

    /**
     * @param Position $position
     *
     * @throws InvalidArgumentException
     */
    final public function addStudy(Position $position): void
    {
        if (!$position->isStudy()) {
            throw new InvalidArgumentException('Špatný typ pozice ('.$position->getType().' není typ studia)');
        }
        $this->addPosition($position);
    }

    /**
     * @param Position|null $position
     */
    abstract public function addPosition(?Position $position): void;

    /**
     * @param Position $position
     *
     * @throws InvalidArgumentException
     */
    final public function addRegularPosition(Position $position): void
    {
        if (!$position->isRegularPosition()) {
            throw new InvalidArgumentException('Špatný typ pozice ('.$position->getType().' není typ zaměstnání)');
        }
        $this->addPosition($position);
    }

    /**
     * @param Position $position
     *
     * @throws InvalidArgumentException
     */
    final public function removeStudy(Position $position): void
    {
        if (!$position->isStudy()) {
            throw new InvalidArgumentException('Špatný typ pozice ('.$position->getType().' není typ studia)');
        }
        $this->removePosition($position);
    }

    /**
     * @param Position|null $position
     */
    abstract public function removePosition(?Position $position): void;

    /**
     * @param Position $position
     *
     * @throws InvalidArgumentException
     */
    final public function removeRegularPosition(Position $position): void
    {
        if (!$position->isRegularPosition()) {
            throw new InvalidArgumentException('Špatný typ pozice ('.$position->getType().' není typ zaměstnání)');
        }
        $this->removePosition($position);
    }
}
