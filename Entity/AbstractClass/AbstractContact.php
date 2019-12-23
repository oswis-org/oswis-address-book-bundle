<?php /** @noinspection MethodShouldBeFinalInspection */

/** @noinspection PhpUnused */

namespace Zakjakub\OswisAddressBookBundle\Entity\AbstractClass;

use ApiPlatform\Core\Annotation\ApiProperty;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Exception\LogicException;
use Symfony\Component\Mime\Exception\RfcComplianceException;
use Zakjakub\OswisAddressBookBundle\Entity\AddressBook\AddressBook;
use Zakjakub\OswisAddressBookBundle\Entity\AddressBook\AddressBookContactConnection;
use Zakjakub\OswisAddressBookBundle\Entity\ContactAddress;
use Zakjakub\OswisAddressBookBundle\Entity\ContactDetail;
use Zakjakub\OswisAddressBookBundle\Entity\ContactDetailType;
use Zakjakub\OswisAddressBookBundle\Entity\ContactImageConnection;
use Zakjakub\OswisAddressBookBundle\Entity\ContactNote;
use Zakjakub\OswisAddressBookBundle\Entity\Organization;
use Zakjakub\OswisAddressBookBundle\Entity\Person;
use Zakjakub\OswisAddressBookBundle\Entity\Position;
use Zakjakub\OswisCoreBundle\Entity\AppUser;
use Zakjakub\OswisCoreBundle\Traits\Entity\BasicEntityTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\TypeTrait;
use Zakjakub\OswisCoreBundle\Utils\EmailUtils;
use function assert;
use function implode;
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
 * @Doctrine\ORM\Mapping\Cache(usage="NONSTRICT_READ_WRITE", region="address_book_contact")
 */
abstract class AbstractContact
{
    use BasicEntityTrait;
    use TypeTrait;

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
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $contactName = null;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $sortableName = null;

    /**
     * Images of person.
     *
     * @var Collection|null
     * @Doctrine\ORM\Mapping\ManyToMany(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\ContactImageConnection",
     *     cascade={"all"},
     *     orphanRemoval=true,
     *     fetch="EAGER"
     * )
     * @Doctrine\ORM\Mapping\JoinTable(
     *     name="address_book_contact_image_connection_connection",
     *     joinColumns={@Doctrine\ORM\Mapping\JoinColumn(name="contact_id", referencedColumnName="id")},
     *     inverseJoinColumns={@Doctrine\ORM\Mapping\JoinColumn(name="image_id", referencedColumnName="id", unique=true)}
     * )
     */
    protected ?Collection $imageConnections = null;

    /**
     * Notes about person.
     *
     * @var Collection|null
     * @Doctrine\ORM\Mapping\ManyToMany(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\ContactNote",
     *     cascade={"all"},
     *     orphanRemoval=true,
     *     fetch="EAGER"
     * )
     * @Doctrine\ORM\Mapping\JoinTable(
     *     name="address_book_contact_note_connection",
     *     joinColumns={@Doctrine\ORM\Mapping\JoinColumn(name="contact_id", referencedColumnName="id")},
     *     inverseJoinColumns={@Doctrine\ORM\Mapping\JoinColumn(name="note_id", referencedColumnName="id", unique=true)}
     * )
     */
    protected ?Collection $notes = null;

    /**
     * Postal addresses of AbstractContact (Person, Organization).
     *
     * @var Collection|null
     * @Doctrine\ORM\Mapping\ManyToMany(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\ContactDetail",
     *     cascade={"all"},
     *     orphanRemoval=true,
     *     fetch="EAGER"
     * )
     * @Doctrine\ORM\Mapping\JoinTable(
     *     name="address_book_contact_detail_connection",
     *     joinColumns={@Doctrine\ORM\Mapping\JoinColumn(name="contact_id", referencedColumnName="id")},
     *     inverseJoinColumns={@Doctrine\ORM\Mapping\JoinColumn(name="contact_detail_id", referencedColumnName="id", unique=true)}
     * )
     */
    protected ?Collection $contactDetails = null;

    /**
     * Postal addresses of AbstractContact (Person, Organization).
     *
     * @var Collection|null
     * @Doctrine\ORM\Mapping\ManyToMany(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\ContactAddress",
     *     cascade={"all"},
     *     orphanRemoval=true,
     *     fetch="EAGER"
     * )
     * @ApiProperty(iri="http://schema.org/address")
     * @Doctrine\ORM\Mapping\JoinTable(
     *     name="address_book_contact_address_connection",
     *     joinColumns={@Doctrine\ORM\Mapping\JoinColumn(name="contact_id", referencedColumnName="id")},
     *     inverseJoinColumns={@Doctrine\ORM\Mapping\JoinColumn(name="address_id", referencedColumnName="id", unique=true)}
     * )
     */
    protected ?Collection $addresses = null;

    /**
     * @var Collection|null
     * @Doctrine\ORM\Mapping\OneToMany(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\AddressBook\AddressBookContactConnection",
     *     cascade={"all"},
     *     mappedBy="contact",
     *     fetch="EAGER"
     * )
     */
    protected ?Collection $addressBookContactConnections = null;

    /**
     * @var AppUser|null $appUser User
     * @Doctrine\ORM\Mapping\OneToOne(
     *     targetEntity="Zakjakub\OswisCoreBundle\Entity\AppUser",
     *     cascade={"all"},
     *     fetch="EAGER"
     * )
     */
    private ?AppUser $appUser = null;

    /**
     * AbstractContact constructor.
     *
     * @param string|null     $type
     * @param Collection|null $notes
     * @param Collection|null $contactDetails
     * @param Collection|null $addresses
     * @param Collection|null $addressBooks
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        ?string $type = null,
        ?Collection $notes = null,
        ?Collection $contactDetails = null,
        ?Collection $addresses = null,
        ?Collection $addressBooks = null
    ) {
        $this->imageConnections = new ArrayCollection();
        $this->setType($type);
        $this->setNotes($notes);
        $this->setContactDetails($contactDetails);
        $this->setAddresses($addresses);
        $this->setAddressBooks($addressBooks);
    }

    final public function setAddressBooks(?Collection $newAddressBooks): void
    {
        $this->addressBookContactConnections ??= new ArrayCollection();
        $newAddressBooks ??= new ArrayCollection();
        foreach ($this->getAddressBooks() as $oldAddressBook) {
            if (!$newAddressBooks->contains($oldAddressBook)) {
                $this->removeAddressBook($oldAddressBook);
            }
        }
        foreach ($newAddressBooks as $newAddressBook) {
            if (!$this->containsAddressBook($newAddressBook)) {
                $this->addAddressBook($newAddressBook);
            }
        }
    }

    final public function getAddressBooks(): Collection
    {
        return $this->getAddressBookContactConnections()->map(
            fn(AddressBookContactConnection $addressBookContactConnection) => $addressBookContactConnection->getAddressBook()
        );
    }

    final public function getAddressBookContactConnections(): Collection
    {
        return $this->addressBookContactConnections ?? new ArrayCollection();
    }

    final public function setAddressBookContactConnections(?Collection $newAddressBookContactConnections): void
    {
        $this->addressBookContactConnections ??= new ArrayCollection();
        $newAddressBookContactConnections ??= new ArrayCollection();
        foreach ($this->addressBookContactConnections as $oldAddressBookContactConnection) {
            if (!$newAddressBookContactConnections->contains($oldAddressBookContactConnection)) {
                $this->removeAddressBookContactConnection($oldAddressBookContactConnection);
            }
        }
        foreach ($newAddressBookContactConnections as $newAddressBookContactConnection) {
            if (!$this->addressBookContactConnections->contains($newAddressBookContactConnection)) {
                $this->addAddressBookContactConnection($newAddressBookContactConnection);
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
        if ($addressBookContactConnection && $this->addressBookContactConnections->removeElement($addressBookContactConnection)) {
            $addressBookContactConnection->setContact(null);
        }
    }

    final public function containsAddressBook(AddressBook $addressBook): bool
    {
        return $this->getAddressBooks()->contains($addressBook);
    }

    final public function addAddressBook(AddressBook $addressBook): void
    {
        if (null !== $addressBook && !$this->containsAddressBook($addressBook)) {
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
     * @param ContactNote|null $personNote
     */
    final public function addNote(?ContactNote $personNote): void
    {
        if ($personNote) {
            $this->notes->add($personNote);
        }
    }

    /**
     * @param ContactImageConnection|null $contactImageConnection
     */
    final public function addImageConnection(?ContactImageConnection $contactImageConnection): void
    {
        if ($contactImageConnection && !$this->imageConnections->contains($contactImageConnection)) {
            $this->imageConnections->add($contactImageConnection);
        }
    }

    /**
     * @param ContactImageConnection|null $contactImageConnection
     */
    final public function removeImageConnection(?ContactImageConnection $contactImageConnection): void
    {
        if ($contactImageConnection) {
            $this->imageConnections->removeElement($contactImageConnection);
        }
    }

    /**
     * Remove contact details where no content is present.
     */
    final public function removeEmptyContactDetails(): void
    {
        $this->setContactDetails($this->getContactDetails()->filter(fn(ContactDetail $detail) => !empty($detail->getContent())));
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
        $this->contactDetails = $newContactDetails ?? new ArrayCollection();
    }

    /**
     * @param ContactDetail|null $contactDetail
     */
    final public function removeContactDetail(?ContactDetail $contactDetail): void
    {
        if ($contactDetail) {
            $this->contactDetails->removeElement($contactDetail);
        }
    }

    /**
     * @param ContactAddress|null $address
     */
    final public function removeAddress(?ContactAddress $address): void
    {
        if ($address) {
            $this->addresses->removeElement($address);
        }
    }

    /**
     * @param ContactDetail|null $contactDetail
     */
    final public function addContactDetail(?ContactDetail $contactDetail): void
    {
        if ($contactDetail && !$this->contactDetails->contains($contactDetail)) {
            $this->contactDetails->add($contactDetail);
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
        $this->addresses = $newAddresses ?? new ArrayCollection();
    }

    /**
     * @param ContactAddress|null $address
     */
    final public function addAddress(?ContactAddress $address): void
    {
        if ($address && !$this->addresses->contains($address)) {
            $this->addresses->add($address);
        }
    }

    /**
     * Remove notes where no content is present.
     */
    final public function removeEmptyNotes(): void
    {
        $this->setNotes($this->getNotes()->filter(fn(ContactNote $note) => empty($note->getContent())));
    }

    /**
     * @return Collection
     */
    final public function getNotes(): Collection
    {
        return $this->notes ?? new ArrayCollection();
    }

    final public function setNotes(?Collection $newNotes): void
    {
        $this->notes = $newNotes ?? new ArrayCollection();
    }

    /**
     * @param ContactNote|null $personNote
     */
    final public function removeNote(?ContactNote $personNote): void
    {
        if ($personNote) {
            $this->notes->removeElement($personNote);
        }
    }

    /**
     * @ApiProperty(iri="http://schema.org/url")
     * @return string All urls in one string.
     */
    final public function getUrlsAsString(): ?string
    {
        return implode([', '], $this->getUrls());
    }

    /**
     * @ApiProperty(iri="http://schema.org/url")
     * @return Collection Collection of URL addresses from contact details
     */
    final public function getUrls(): Collection
    {
        return $this->getContactDetails()->filter(fn(ContactDetail $contactDetail) => ContactDetailType::TYPE_URL === $contactDetail->getTypeString());
    }

    public function getContactPersons(?DateTime $referenceDateTime = null, bool $onlyWithActivatedUser = false): Collection
    {
        if ($onlyWithActivatedUser) {
            return $this->getAppUser() && $this->getAppUser()->isActive($referenceDateTime) ? new ArrayCollection([$this]) : new ArrayCollection();
        }

        return new ArrayCollection([$this]);
    }

    /**
     * User associated with this contact.
     * @return AppUser|null
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
        if ($this->appUser !== $appUser) {
            $this->appUser = $appUser;
        }
    }

    final public function getUrl(): ?string
    {
        $urls = $this->getUrls();

        return $urls->count() > 0 ? $urls->first()->getContent() : null;
    }

    final public function getPhone(): ?string
    {
        $phones = $this->getPhones();

        return $phones->count() > 0 ? $phones->first()->getContent() : null;
    }

    /**
     * @ApiProperty(iri="http://schema.org/telephone")
     * @return Collection Collection of telephone numbers of AbstractContact (Person or Organization)
     */
    final public function getPhones(): Collection
    {
        return $this->getContactDetails()->filter(fn(ContactDetail $contactDetail) => ContactDetailType::TYPE_PHONE === $contactDetail->getTypeString());
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

    final public function getContactName(): string
    {
        return $this->updateContactName();
    }

    final public function setContactName(?string $contactName): void
    {
        $this->setFullName($contactName);
        $this->updateContactName();
    }

    final public function updateContactName(): string
    {
        $this->contactName = $this->getFullName();
        $this->sortableName = $this->getSortableContactName();

        return $this->getFullName();
    }

    abstract public function getFullName(): ?string;

    public function getSortableContactName(): string
    {
        return $this->getFullName() ?? '';
    }

    abstract public function setFullName(?string $contactName): void;

    /** @noinspection MethodShouldBeFinalInspection */
    final public function canRead(AppUser $user): bool
    {
        if (!($user instanceof AppUser)) { // User is not logged in.
            return false;
        }

        return $user->hasRole('ROLE_MEMBER') && $user->hasRole('ROLE_USER') && $user === $this->getUser();
    }

    final public function getUser(): ?AppUser
    {
        return $this->getAppUser();
    }

    /**
     * @return Address
     * @throws LogicException
     * @throws RfcComplianceException
     */
    public function getMailerAddress(): Address
    {
        $name = $this->getContactName() ?? ($this->getAppUser() ? $this->getAppUser()->getFullName() : '') ?? '';
        $eMail = ($this->getAppUser() ? $this->getAppUser()->getEmail() : $this->getEmail()) ?? '';

        return new Address($eMail, EmailUtils::mime_header_encode($name));
    }

    final public function getEmail(): ?string
    {
        return $this->getEmails()->first() ? $this->getEmails()->first()->getContent() : null;
    }

    /**
     * @ApiProperty(iri="http://schema.org/email")
     * @return Collection Collection of e-mail addresses from contact details
     */
    final public function getEmails(): Collection
    {
        return $this->getContactDetails()->filter(fn(ContactDetail $contactDetail) => ContactDetailType::TYPE_EMAIL === $contactDetail->getTypeString());
    }

    final public function canEdit(AppUser $user): bool
    {
        if (!($user instanceof AppUser)) {// User is not logged in.
            return false;
        }

        return $user->hasRole('ROLE_MEMBER') && $user->hasRole('ROLE_USER') && $user === $this->getUser();
    }

    final public function containsUserInPersons(AppUser $user): bool
    {
        return $this->getUsersOfPersons()->contains($user);
    }

    /**
     * @return Collection
     */
    final public function getUsersOfPersons(): Collection
    {
        return $this->getPersons()->map(fn(Person $person) => $person->getAppUser());
    }

    /**
     * @return Collection
     */
    final public function getPersons(): Collection
    {
        if ($this instanceof Person) {
            return new ArrayCollection([$this]);
        }
        if ($this instanceof Organization) {
            return $this->getPositions()->map(fn(Position $position) => $position->getPerson());
        }

        return new ArrayCollection();
    }

    /**
     * @return Collection
     */
    final public function getManagedDepartments(): Collection
    {
        // TODO: Return managed departments.
        return new ArrayCollection();
    }

    /**
     * @return string
     */
    final public function __toString(): string
    {
        return $this->getContactName() ?? '';
    }

    /**
     * @return Collection
     */
    final public function getStudies(): Collection
    {
        return $this->getPositions()->filter(fn(Position $position) => $position->isStudy());
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
        return $this->getPositions()->filter(fn(Position $position) => $position->isRegularPosition());
    }

    /**
     * @param Position|null $position
     *
     * @throws InvalidArgumentException
     */
    final public function addStudy(?Position $position): void
    {
        if (!$position) {
            return;
        }
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
     * @param Position|null $position
     *
     * @throws InvalidArgumentException
     */
    final public function addRegularPosition(?Position $position): void
    {
        if (!$position) {
            return;
        }
        if (!$position->isRegularPosition()) {
            throw new InvalidArgumentException('Špatný typ pozice ('.$position->getType().' není typ zaměstnání)');
        }
        $this->addPosition($position);
    }

    /**
     * @param Position|null $position
     *
     * @throws InvalidArgumentException
     */
    final public function removeStudy(?Position $position): void
    {
        if (!$position) {
            return;
        }
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
     * @param Position|null $position
     *
     * @throws InvalidArgumentException
     */
    final public function removeRegularPosition(?Position $position): void
    {
        if (!$position) {
            return;
        }
        if (!$position->isRegularPosition()) {
            throw new InvalidArgumentException('Špatný typ pozice ('.$position->getType().' není typ zaměstnání)');
        }
        $this->removePosition($position);
    }
}
