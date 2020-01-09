<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 * @noinspection PhpUnused
 */

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
use Zakjakub\OswisAddressBookBundle\Entity\ContactNote;
use Zakjakub\OswisAddressBookBundle\Entity\MediaObject\ContactImage;
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
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $contactName = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $sortableName = null;

    /**
     * Notes about person.
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
     * @Doctrine\ORM\Mapping\OneToMany(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\AddressBook\AddressBookContactConnection",
     *     cascade={"all"},
     *     mappedBy="contact",
     *     fetch="EAGER"
     * )
     */
    protected ?Collection $addressBookContactConnections = null;

    /**
     * @Doctrine\ORM\Mapping\OneToOne(
     *     targetEntity="Zakjakub\OswisCoreBundle\Entity\AppUser",
     *     cascade={"all"},
     *     fetch="EAGER"
     * )
     */
    protected ?AppUser $appUser = null;

    /**
     * @Doctrine\ORM\Mapping\OneToOne(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\MediaObject\ContactImage",
     *     cascade={"all"},
     *     fetch="EAGER"
     * )
     */
    protected ?ContactImage $image = null;

    /**
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
        $this->setType($type);
        $this->setNotes($notes);
        $this->setContactDetails($contactDetails);
        $this->setAddresses($addresses);
        $this->setAddressBooks($addressBooks);
    }

    public function setAddressBooks(?Collection $newAddressBooks): void
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

    public function getAddressBooks(): Collection
    {
        return $this->getAddressBookContactConnections()->map(
            fn(AddressBookContactConnection $addressBookContactConnection) => $addressBookContactConnection->getAddressBook()
        );
    }

    public function getAddressBookContactConnections(): Collection
    {
        return $this->addressBookContactConnections ?? new ArrayCollection();
    }

    public function setAddressBookContactConnections(?Collection $newAddressBookContactConnections): void
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

    public function removeAddressBook(AddressBook $addressBook): void
    {
        foreach ($this->getAddressBookContactConnections() as $addressBookContactConnection) {
            assert($addressBookContactConnection instanceof AddressBookContactConnection);
            if ($addressBook->getId() === $addressBookContactConnection->getId()) {
                $this->removeAddressBookContactConnection($addressBookContactConnection);
            }
        }
    }

    public function removeAddressBookContactConnection(?AddressBookContactConnection $addressBookContactConnection): void
    {
        if ($addressBookContactConnection && $this->addressBookContactConnections->removeElement($addressBookContactConnection)) {
            $addressBookContactConnection->setContact(null);
        }
    }

    public function containsAddressBook(AddressBook $addressBook): bool
    {
        return $this->getAddressBooks()->contains($addressBook);
    }

    public function addAddressBook(AddressBook $addressBook): void
    {
        if (null !== $addressBook && !$this->containsAddressBook($addressBook)) {
            $this->addAddressBookContactConnection(new AddressBookContactConnection($addressBook));
        }
    }

    public function addAddressBookContactConnection(?AddressBookContactConnection $addressBookContactConnection): void
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

    public function getImage(): ?ContactImage
    {
        return $this->image;
    }

    public function setImage(?ContactImage $image): void
    {
        $this->image = $image;
    }

    public function isPerson(): bool
    {
        return $this instanceof Person;
    }

    public function isOrganization(): bool
    {
        return $this instanceof Organization;
    }

    public function isSchool(): bool
    {
        return in_array($this->getType(), self::SCHOOL_TYPES, true);
    }

    public function isStudentOrganization(): bool
    {
        return in_array($this->getType(), self::STUDENT_ORGANIZATION_TYPES, true);
    }

    public function addNote(?ContactNote $personNote): void
    {
        if (null !== $personNote) {
            $this->notes->add($personNote);
        }
    }

    /**
     * Remove contact details where no content is present.
     */
    public function removeEmptyContactDetails(): void
    {
        $this->setContactDetails($this->getContactDetails()->filter(fn(ContactDetail $detail) => !empty($detail->getContent())));
    }

    public function getContactDetails(): Collection
    {
        return $this->contactDetails ?? new ArrayCollection();
    }

    public function setContactDetails(?Collection $newContactDetails): void
    {
        $this->contactDetails = $newContactDetails ?? new ArrayCollection();
    }

    public function removeContactDetail(?ContactDetail $contactDetail): void
    {
        if (null !== $contactDetail) {
            $this->contactDetails->removeElement($contactDetail);
        }
    }

    public function removeAddress(?ContactAddress $address): void
    {
        if (null !== $address) {
            $this->addresses->removeElement($address);
        }
    }

    public function addContactDetail(?ContactDetail $contactDetail): void
    {
        if (null !== $contactDetail && !$this->contactDetails->contains($contactDetail)) {
            $this->contactDetails->add($contactDetail);
        }
    }

    public function getAddresses(): Collection
    {
        return $this->addresses ?? new ArrayCollection();
    }

    public function setAddresses(?Collection $newAddresses): void
    {
        $this->addresses = $newAddresses ?? new ArrayCollection();
    }

    public function addAddress(?ContactAddress $address): void
    {
        if (null !== $address && !$this->addresses->contains($address)) {
            $this->addresses->add($address);
        }
    }

    /**
     * Remove notes where no content is present.
     */
    public function removeEmptyNotes(): void
    {
        $this->setNotes($this->getNotes()->filter(fn(ContactNote $note) => empty($note->getContent())));
    }

    public function getNotes(): Collection
    {
        return $this->notes ?? new ArrayCollection();
    }

    public function setNotes(?Collection $newNotes): void
    {
        $this->notes = $newNotes ?? new ArrayCollection();
    }

    public function removeNote(?ContactNote $personNote): void
    {
        if (null !== $personNote) {
            $this->notes->removeElement($personNote);
        }
    }

    /**
     * @ApiProperty(iri="http://schema.org/url")
     * @return string All urls in one string.
     */
    public function getUrlsAsString(): ?string
    {
        return implode([', '], $this->getUrls());
    }

    /**
     * @ApiProperty(iri="http://schema.org/url")
     * @return Collection Collection of URL addresses from contact details
     */
    public function getUrls(): Collection
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

    public function getAppUser(): ?AppUser
    {
        return $this->appUser;
    }

    public function setAppUser(?AppUser $appUser): void
    {
        if ($this->appUser !== $appUser) {
            $this->appUser = $appUser;
        }
    }

    public function getUrl(): ?string
    {
        $urls = $this->getUrls();

        return $urls->count() > 0 ? $urls->first()->getContent() : null;
    }

    public function getPhone(): ?string
    {
        $phones = $this->getPhones();

        return $phones->count() > 0 ? $phones->first()->getContent() : null;
    }

    /**
     * @ApiProperty(iri="http://schema.org/telephone")
     * @return Collection Collection of telephone numbers of AbstractContact (Person or Organization)
     */
    public function getPhones(): Collection
    {
        return $this->getContactDetails()->filter(fn(ContactDetail $contactDetail) => ContactDetailType::TYPE_PHONE === $contactDetail->getTypeString());
    }

    public function getAddress(): ?string
    {
        return $this->contactDetails->first();
    }

    /**
     * @ApiProperty(iri="http://schema.org/legalName")
     * @return string (Official) Name of AbstractContact (Person or Organization)
     */
    public function getLegalName(): ?string
    {
        return $this->getContactName();
    }

    public function getContactName(): ?string
    {
        return $this->updateContactName();
    }

    public function setContactName(?string $contactName): void
    {
        $this->setFullName($contactName);
        $this->updateContactName();
    }

    public function updateContactName(): ?string
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

    public function getName(): ?string
    {
        return $this->getContactName();
    }

    public function setName(?string $name): void
    {
        $this->setContactName($name);
    }

    abstract public function setFullName(?string $contactName): void;

    public function canRead(AppUser $user): bool
    {
        if (!($user instanceof AppUser)) { // User is not logged in.
            return false;
        }

        return $user->hasRole('ROLE_MEMBER') && $user->hasRole('ROLE_USER') && $user === $this->getUser();
    }

    public function getUser(): ?AppUser
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

    public function getEmail(): ?string
    {
        return $this->getEmails()->first() ? $this->getEmails()->first()->getContent() : null;
    }

    /**
     * @ApiProperty(iri="http://schema.org/email")
     * @return Collection Collection of e-mail addresses from contact details
     */
    public function getEmails(): Collection
    {
        return $this->getContactDetails()->filter(fn(ContactDetail $contactDetail) => ContactDetailType::TYPE_EMAIL === $contactDetail->getTypeString());
    }

    public function canEdit(AppUser $user): bool
    {
        if (!($user instanceof AppUser)) {// User is not logged in.
            return false;
        }

        return $user->hasRole('ROLE_MEMBER') && $user->hasRole('ROLE_USER') && $user === $this->getUser();
    }

    public function containsUserInPersons(AppUser $user): bool
    {
        return $this->getUsersOfPersons()->contains($user);
    }

    /**
     * @return Collection
     */
    public function getUsersOfPersons(): Collection
    {
        return $this->getPersons()->map(fn(Person $person) => $person->getAppUser());
    }

    /**
     * @return Collection
     */
    public function getPersons(): Collection
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
    public function getManagedDepartments(): Collection
    {
        // TODO: Return managed departments.
        return new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getContactName() ?? '';
    }

    /**
     * @return Collection
     */
    public function getStudies(): Collection
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
    public function getRegularPositions(): Collection
    {
        return $this->getPositions()->filter(fn(Position $position) => $position->isRegularPosition());
    }

    /**
     * @param Position|null $position
     *
     * @throws InvalidArgumentException
     */
    public function addStudy(?Position $position): void
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
    public function addRegularPosition(?Position $position): void
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
    public function removeStudy(?Position $position): void
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
    public function removeRegularPosition(?Position $position): void
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
