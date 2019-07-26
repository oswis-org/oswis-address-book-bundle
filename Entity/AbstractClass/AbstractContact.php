<?php

namespace Zakjakub\OswisAddressBookBundle\Entity\AbstractClass;

use ApiPlatform\Core\Annotation\ApiProperty;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use InvalidArgumentException;
use Zakjakub\OswisAddressBookBundle\Entity\AddressBook\AddressBook;
use Zakjakub\OswisAddressBookBundle\Entity\AddressBook\AddressBookContactConnection;
use Zakjakub\OswisAddressBookBundle\Entity\ContactAddress;
use Zakjakub\OswisAddressBookBundle\Entity\ContactDetail;
use Zakjakub\OswisAddressBookBundle\Entity\ContactImage;
use Zakjakub\OswisAddressBookBundle\Entity\ContactImageConnection;
use Zakjakub\OswisAddressBookBundle\Entity\ContactNote;
use Zakjakub\OswisAddressBookBundle\Entity\Organization;
use Zakjakub\OswisAddressBookBundle\Entity\Person;
use Zakjakub\OswisAddressBookBundle\Entity\Position;
use Zakjakub\OswisCoreBundle\Entity\AbstractClass\AbstractRevisionContainer;
use Zakjakub\OswisCoreBundle\Entity\AppUser;
use Zakjakub\OswisCoreBundle\Traits\Entity\BasicEntityTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\TypeTrait;
use function assert;
use function in_array;

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
    public const TYPE_ORGANIZATION = 'organization';
    public const TYPE_PERSON = 'person';

    public const TYPE_UNIVERSITY = 'university';
    public const TYPE_FACULTY = 'faculty';
    public const TYPE_FACULTY_DEPARTMENT = 'faculty-department';
    public const TYPE_STUDENT_ORGANIZATION = 'student-organization';
    public const TYPE_HIGH_SCHOOL = 'high-school';
    public const TYPE_PRIMARY_SCHOOL = 'primary-school';
    public const TYPE_KINDERGARTEN = 'kindergarten';
    public const TYPE_COMPANY = 'company';

    public const COMPANY_TYPES = [self::TYPE_COMPANY];
    public const ORGANIZATION_TYPES = [self::TYPE_ORGANIZATION];
    public const STUDENT_ORGANIZATION_TYPES = [self::TYPE_STUDENT_ORGANIZATION];
    public const SCHOOL_TYPES = [
        self::TYPE_UNIVERSITY,
        self::TYPE_FACULTY,
        self::TYPE_FACULTY_DEPARTMENT,
        self::TYPE_HIGH_SCHOOL,
        self::TYPE_PRIMARY_SCHOOL,
        self::TYPE_KINDERGARTEN,
    ];

    public const PERSON_TYPES = [self::TYPE_PERSON];

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
     * @var Collection|null
     * @Doctrine\ORM\Mapping\OneToMany(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\AddressBook\AddressBookContactConnection",
     *     cascade={"all"},
     *     mappedBy="contact",
     *     fetch="EAGER"
     * )
     */
    protected $addressBookContactConnections;

    /**
     * @var AppUser|null $appUser User
     * @Doctrine\ORM\Mapping\OneToOne(
     *     targetEntity="Zakjakub\OswisCoreBundle\Entity\AppUser",
     *     cascade={"all"},
     *     fetch="EAGER"
     * )
     */
    private $appUser;

    /**
     * AbstractContact constructor.
     *
     * @param string|null       $type
     * @param Collection|null   $notes
     * @param Collection|null   $contactDetails
     * @param Collection|null   $addresses
     * @param ContactImage|null $image
     * @param Collection|null   $addressBooks
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        ?string $type = null,
        ?Collection $notes = null,
        ?Collection $contactDetails = null,
        ?Collection $addresses = null,
        ?ContactImage $image = null,
        ?Collection $addressBooks = null
    ) {
        $this->image = $image;
        $this->setType($type);
        $this->setNotes($notes);
        $this->setContactDetails($contactDetails);
        $this->setAddresses($addresses);
        $this->setAddressBooks($addressBooks);
    }

    final public function setAddressBooks(?Collection $newAddressBooks): void
    {
        if (!$this->addressBookContactConnections) {
            $this->addressBookContactConnections = new ArrayCollection();
        }
        if (!$newAddressBooks) {
            $newAddressBooks = new ArrayCollection();
        }
        foreach ($this->getAddressBooks() as $oldAddressBook) {
            if (!$newAddressBooks->contains($oldAddressBook)) {
                $this->removeAddressBook($oldAddressBook);
            }
        }
        if ($newAddressBooks) {
            foreach ($newAddressBooks as $newAddressBook) {
                if (!$this->containsAddressBook($newAddressBook)) {
                    $this->addAddressBook($newAddressBook);
                }
            }
        }
    }

    use BasicEntityTrait;
    use TypeTrait;

    final public function getAddressBooks(): Collection
    {
        return $this->getAddressBookContactConnections()->map(
            static function (AddressBookContactConnection $addressBookContactConnection) {
                return $addressBookContactConnection->getAddressBook();
            }
        );
    }

    final public function getAddressBookContactConnections(): Collection
    {
        return $this->addressBookContactConnections ?? new ArrayCollection();
    }

    final public function setAddressBookContactConnections(?Collection $newAddressBookContactConnections): void
    {
        if (!$this->addressBookContactConnections) {
            $this->addressBookContactConnections = new ArrayCollection();
        }
        if (!$newAddressBookContactConnections) {
            $newAddressBookContactConnections = new ArrayCollection();
        }
        foreach ($this->addressBookContactConnections as $oldAddressBookContactConnection) {
            if (!$newAddressBookContactConnections->contains($oldAddressBookContactConnection)) {
                $this->removeAddressBookContactConnection($oldAddressBookContactConnection);
            }
        }
        if ($newAddressBookContactConnections) {
            foreach ($newAddressBookContactConnections as $newAddressBookContactConnection) {
                if (!$this->addressBookContactConnections->contains($newAddressBookContactConnection)) {
                    $this->addAddressBookContactConnection($newAddressBookContactConnection);
                }
            }
        }
    }

    final public function removeAddressBook(AddressBook $addressBook): void
    {
        foreach ($this->getAddressBookContactConnections() as $addressBookContactConnection) {
            assert($addressBookContactConnection instanceof AddressBookContactConnection);
            if ($addressBook->getId() === $addressBookContactConnection->getId()) {
                $this->removeAddressBookContactConnection($addressBookContactConnection);
            }
        }
    }

    final public function removeAddressBookContactConnection(?AddressBookContactConnection $addressBookContactConnection): void
    {
        if (!$addressBookContactConnection) {
            return;
        }
        if ($this->addressBookContactConnections->removeElement($addressBookContactConnection)) {
            $addressBookContactConnection->setContact(null);
        }
    }

    final public function containsAddressBook(AddressBook $addressBook): bool
    {
        return $this->getAddressBooks()->contains($addressBook);
    }

    final public function addAddressBook(AddressBook $addressBook): void
    {
        if (!$this->containsAddressBook($addressBook)) {
            $this->addAddressBookContactConnection(new AddressBookContactConnection($addressBook));
        }
    }

    final public function addAddressBookContactConnection(?AddressBookContactConnection $addressBookContactConnection): void
    {
        if ($addressBookContactConnection && !$this->addressBookContactConnections->contains($addressBookContactConnection)) {
            $this->addressBookContactConnections->add($addressBookContactConnection);
            $addressBookContactConnection->setContact($this);
        }
    }

    public static function getAllowedTypesDefault(): array
    {
        return [
            self::TYPE_ORGANIZATION,
            self::TYPE_PERSON,
            self::TYPE_UNIVERSITY,
            self::TYPE_FACULTY,
            self::TYPE_FACULTY_DEPARTMENT,
            self::TYPE_STUDENT_ORGANIZATION,
            self::TYPE_HIGH_SCHOOL,
            self::TYPE_PRIMARY_SCHOOL,
            self::TYPE_KINDERGARTEN,
            self::TYPE_COMPANY,
        ];
    }

    public static function getAllowedTypesCustom(): array
    {
        return [];
    }

    final public function isPerson(): bool
    {
        return $this instanceof Person;
    }

    final public function isOrganization(): bool
    {
        return $this instanceof Organization;
    }

    final public function isSchool(): bool
    {
        return in_array($this->getType(), self::SCHOOL_TYPES, true);
    }

    final public function isStudentOrganization(): bool
    {
        return in_array($this->getType(), self::STUDENT_ORGANIZATION_TYPES, true);
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
     * Remove contact details where no content is present.
     */
    final public function removeEmptyContactDetails(): void
    {
        foreach ($this->getContactDetails() as $contactDetail) {
            assert($contactDetail instanceof ContactDetail);
            if (!$contactDetail->getContent() || '' === $contactDetail->getContent()) {
                $this->removeContactDetail($contactDetail);
            }
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

    final public function setContactDetails(?Collection $newContactDetails): void
    {
        if (!$this->contactDetails) {
            $this->contactDetails = new ArrayCollection();
        }
        if (!$newContactDetails) {
            $newContactDetails = new ArrayCollection();
        }
        foreach ($this->contactDetails as $oldContactDetail) {
            if (!$newContactDetails->contains($oldContactDetail)) {
                $this->removeContactDetail($oldContactDetail);
            }
        }
        if ($newContactDetails) {
            foreach ($newContactDetails as $newContactDetail) {
                if (!$this->contactDetails->contains($newContactDetail)) {
                    $this->addContactDetail($newContactDetail);
                }
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

    final public function setAddresses(?Collection $newAddresses): void
    {
        if (!$this->addresses) {
            $this->addresses = new ArrayCollection();
        }
        if (!$newAddresses) {
            $newAddresses = new ArrayCollection();
        }
        foreach ($this->addresses as $oldAddress) {
            if (!$newAddresses->contains($oldAddress)) {
                $this->removeAddress($oldAddress);
            }
        }
        if ($newAddresses) {
            foreach ($newAddresses as $newAddress) {
                if (!$this->addresses->contains($newAddress)) {
                    $this->addAddress($newAddress);
                }
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

    final public function setNotes(?Collection $newNotes): void
    {
        if (!$this->notes) {
            $this->notes = new ArrayCollection();
        }
        if (!$newNotes) {
            $newNotes = new ArrayCollection();
        }
        foreach ($this->notes as $oldNote) {
            if (!$newNotes->contains($oldNote)) {
                $this->removeNote($oldNote);
            }
        }
        if ($newNotes) {
            foreach ($newNotes as $newNote) {
                if (!$this->notes->contains($newNote)) {
                    $this->addNote($newNote);
                }
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

    /**
     * @return ArrayCollection
     */
    final public function getManagedDepartments(): ArrayCollection
    {
        // TODO: Return managed departmenmts.
        return new ArrayCollection();
    }

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
