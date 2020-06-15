<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 * @noinspection PhpUnused
 */

namespace OswisOrg\OswisAddressBookBundle\Entity\AbstractClass;

use ApiPlatform\Core\Annotation\ApiProperty;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use InvalidArgumentException;
use OswisOrg\OswisAddressBookBundle\Entity\AddressBook\AddressBook;
use OswisOrg\OswisAddressBookBundle\Entity\AddressBook\ContactAddressBook;
use OswisOrg\OswisAddressBookBundle\Entity\ContactAddress;
use OswisOrg\OswisAddressBookBundle\Entity\ContactDetail;
use OswisOrg\OswisAddressBookBundle\Entity\ContactDetailType;
use OswisOrg\OswisAddressBookBundle\Entity\ContactNote;
use OswisOrg\OswisAddressBookBundle\Entity\MediaObject\ContactImage;
use OswisOrg\OswisAddressBookBundle\Entity\Organization;
use OswisOrg\OswisAddressBookBundle\Entity\Person;
use OswisOrg\OswisAddressBookBundle\Entity\Position;
use OswisOrg\OswisCoreBundle\Entity\AppUser\AppUser;
use OswisOrg\OswisCoreBundle\Entity\NonPersistent\Nameable;
use OswisOrg\OswisCoreBundle\Entity\NonPersistent\Publicity;
use OswisOrg\OswisCoreBundle\Interfaces\AddressBook\ContactInterface;
use OswisOrg\OswisCoreBundle\Traits\Common\EntityPublicTrait;
use OswisOrg\OswisCoreBundle\Traits\Common\NameableTrait;
use OswisOrg\OswisCoreBundle\Traits\Common\TypeTrait;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Exception\LogicException;
use Symfony\Component\Mime\Exception\RfcComplianceException;
use Symfony\Component\Serializer\Annotation\DiscriminatorMap;
use function assert;
use function in_array;

/**
 * Class Contact (abstract class for Person, Department, Organization, School...).
 * @Doctrine\ORM\Mapping\Entity()
 * @Doctrine\ORM\Mapping\Table(name="address_book_abstract_contact")
 * @Doctrine\ORM\Mapping\InheritanceType("JOINED")
 * @Doctrine\ORM\Mapping\DiscriminatorColumn(name="discriminator", type="text")
 * @Doctrine\ORM\Mapping\DiscriminatorMap({
 *   "address_book_person" = "OswisOrg\OswisAddressBookBundle\Entity\Person",
 *   "address_book_organization" = "OswisOrg\OswisAddressBookBundle\Entity\Organization"
 * })
 * @DiscriminatorMap(typeProperty="discriminator", mapping={
 *   "address_book_person" = "OswisOrg\OswisAddressBookBundle\Entity\Person",
 *   "address_book_organization" = "OswisOrg\OswisAddressBookBundle\Entity\Organization"
 * })
 * @Doctrine\ORM\Mapping\Cache(usage="NONSTRICT_READ_WRITE", region="address_book_contact")
 */
abstract class AbstractContact implements ContactInterface
{
    use NameableTrait;
    use TypeTrait;
    use EntityPublicTrait;

    /**
     * Notes about person.
     * @Doctrine\ORM\Mapping\OneToMany(
     *     targetEntity="OswisOrg\OswisAddressBookBundle\Entity\ContactNote",
     *     mappedBy="contact",
     *     cascade={"all"},
     *     orphanRemoval=true,
     *     fetch="EAGER"
     * )
     */
    protected ?Collection $notes = null;

    /**
     * Postal addresses of AbstractContact (Person, Organization).
     * @Doctrine\ORM\Mapping\OneToMany(
     *     targetEntity="OswisOrg\OswisAddressBookBundle\Entity\ContactDetail",
     *     mappedBy="contact",
     *     cascade={"all"},
     *     orphanRemoval=true,
     *     fetch="EAGER"
     * )
     */
    protected ?Collection $details = null;

    /**
     * Postal addresses of AbstractContact (Person, Organization).
     * @Doctrine\ORM\Mapping\OneToMany(
     *     targetEntity="OswisOrg\OswisAddressBookBundle\Entity\ContactAddress",
     *     mappedBy="contact",
     *     cascade={"all"},
     *     orphanRemoval=true,
     *     fetch="EAGER"
     * )
     * @ApiProperty(iri="http://schema.org/address")
     */
    protected ?Collection $addresses = null;

    /**
     * @Doctrine\ORM\Mapping\ManyToMany(
     *     targetEntity="OswisOrg\OswisAddressBookBundle\Entity\AddressBook\ContactAddressBook", cascade={"all"}, fetch="EAGER"
     * )
     * @Doctrine\ORM\Mapping\JoinTable(
     *     name="address_book_address_book_contact_connection"
     *     joinColumns={@Doctrine\ORM\Mapping\JoinColumn(name="participant_id", referencedColumnName="id")},
     *     inverseJoinColumns={@Doctrine\ORM\Mapping\JoinColumn(name="participant_contact_id", referencedColumnName="id", unique=true)}
     * )
     */
    protected ?Collection $contactAddressBooks = null;

    /**
     * @Doctrine\ORM\Mapping\OneToOne(targetEntity="OswisOrg\OswisCoreBundle\Entity\AppUser\AppUser", cascade={"all"}, fetch="EAGER")
     */
    protected ?AppUser $appUser = null;

    /**
     * @Doctrine\ORM\Mapping\OneToOne(
     *     targetEntity="OswisOrg\OswisAddressBookBundle\Entity\MediaObject\ContactImage",
     *     cascade={"all"},
     *     fetch="EAGER"
     * )
     */
    protected ?ContactImage $image = null;

    protected ?Collection $positions = null;

    /**
     * @param Nameable|null   $nameable
     * @param string|null     $type
     * @param Collection|null $notes
     * @param Collection|null $contactDetails
     * @param Collection|null $addresses
     * @param Collection|null $addressBooks
     * @param Collection|null $positions
     * @param Publicity|null  $publicity
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        ?Nameable $nameable = null,
        ?string $type = null,
        ?Collection $notes = null,
        ?Collection $contactDetails = null,
        ?Collection $addresses = null,
        ?Collection $addressBooks = null,
        ?Collection $positions = null,
        ?Publicity $publicity = null
    ) {
        $this->setFieldsFromNameable($nameable);
        $this->setType($type);
        $this->setNotes($notes);
        $this->setDetails($contactDetails);
        $this->setAddresses($addresses);
        $this->setPositions($positions);
        $this->setAddressBooks($addressBooks);
        $this->setFieldsFromPublicity($publicity);
    }

    public function setAddressBooks(?Collection $newAddressBooks): void
    {
        $this->contactAddressBooks ??= new ArrayCollection();
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
        return $this->getContactAddressBooks()->map(
            fn(ContactAddressBook $addressBookContactConnection) => $addressBookContactConnection->getAddressBook()
        );
    }

    public function getContactAddressBooks(): Collection
    {
        return $this->contactAddressBooks ?? new ArrayCollection();
    }

    public function setContactAddressBooks(?Collection $newAddressBookContactConnections): void
    {
        $this->contactAddressBooks = $newAddressBookContactConnections ?? new ArrayCollection();
    }

    public function removeAddressBook(AddressBook $addressBook): void
    {
        foreach ($this->getContactAddressBooks() as $addressBookContactConnection) {
            assert($addressBookContactConnection instanceof ContactAddressBook);
            if ($addressBookContactConnection->getAddressBook() === $addressBook) {
                $this->removeContactAddressBook($addressBookContactConnection);
            }
        }
    }

    public function removeContactAddressBook(?ContactAddressBook $addressBookContactConnection): void
    {
        $this->contactAddressBooks->removeElement($addressBookContactConnection);
    }

    public function containsAddressBook(AddressBook $addressBook): bool
    {
        return $this->getAddressBooks()->contains($addressBook);
    }

    public function addAddressBook(AddressBook $addressBook): void
    {
        if (null !== $addressBook && !$this->containsAddressBook($addressBook)) {
            $this->addContactAddressBook(new ContactAddressBook($addressBook));
        }
    }

    public function addContactAddressBook(?ContactAddressBook $addressBookContactConnection): void
    {
        if ($addressBookContactConnection && !$this->contactAddressBooks->contains($addressBookContactConnection)) {
            $this->contactAddressBooks->add($addressBookContactConnection);
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

    /**
     * Remove contact details where no content is present.
     */
    public function removeEmptyDetails(): void
    {
        $this->setDetails(
            $this->getDetails()->filter(fn(ContactDetail $detail) => !empty($detail->getContent()))
        );
    }

    public function getDetails(?string $typeString = null): Collection
    {
        if (!empty($typeString)) {
            return $this->getDetails()->filter(fn(ContactDetail $detail) => $typeString === $detail->getTypeString());
        }

        return $this->details ?? new ArrayCollection();
    }

    public function setDetails(?Collection $newDetails): void
    {
        $this->details ??= new ArrayCollection();
        $newDetails ??= new ArrayCollection();
        foreach ($this->details as $oldDetail) {
            if (!$newDetails->contains($oldDetail)) {
                $this->removeDetail($oldDetail);
            }
        }
        foreach ($newDetails as $newDetail) {
            if (!$this->details->contains($newDetail)) {
                $this->addDetail($newDetail);
            }
        }
    }

    public function removeNote(?ContactNote $note): void
    {
        if (null !== $note && $this->getNotes()->removeElement($note)) {
            $note->setContact(null);
        }
    }

    public function getNotes(): Collection
    {
        return $this->notes ?? new ArrayCollection();
    }

    public function setNotes(?Collection $newNotes): void
    {
        $this->notes ??= new ArrayCollection();
        $newNotes ??= new ArrayCollection();
        foreach ($this->notes as $oldNote) {
            if (!$newNotes->contains($oldNote)) {
                $this->removeNote($oldNote);
            }
        }
        foreach ($newNotes as $newNote) {
            if (!$this->notes->contains($newNote)) {
                $this->addNote($newNote);
            }
        }
    }

    public function removeDetail(?ContactDetail $detail): void
    {
        if (null !== $detail && $this->getDetails()->removeElement($detail)) {
            $detail->setContact(null);
        }
    }

    public function removeAddress(?ContactAddress $address): void
    {
        if (null !== $address && $this->getAddresses()->removeElement($address)) {
            $address->setContact(null);
        }
    }

    public function getAddresses(): Collection
    {
        return $this->addresses ?? new ArrayCollection();
    }

    public function setAddresses(?Collection $newAddresses): void
    {
        $this->addresses ??= new ArrayCollection();
        $newAddresses ??= new ArrayCollection();
        foreach ($this->addresses as $oldAddress) {
            if (!$newAddresses->contains($oldAddress)) {
                $this->removeAddress($oldAddress);
            }
        }
        foreach ($newAddresses as $newAddress) {
            if (!$this->addresses->contains($newAddress)) {
                $this->addAddress($newAddress);
            }
        }
    }

    public function addDetail(?ContactDetail $detail): void
    {
        if (null !== $detail && !$this->getDetails()->contains($detail)) {
            $this->details->add($detail);
            $detail->setContact($this);
        }
    }

    public function addNote(?ContactNote $note): void
    {
        if (null !== $note && !$this->getNotes()->contains($note)) {
            $this->notes->add($note);
            $note->setContact($this);
        }
    }

    public function addAddress(?ContactAddress $address): void
    {
        if (null !== $address && !$this->getAddresses()->contains($address)) {
            $this->addresses->add($address);
            $address->setContact($this);
        }
    }

    /**
     * Remove notes where no content is present.
     */
    public function removeEmptyNotes(): void
    {
        $this->setNotes(
            $this->getNotes()->filter(fn(ContactNote $note) => empty($note->getContent()))
        );
    }

    /**
     * @ApiProperty(iri="http://schema.org/url")
     * @return string All urls in one string.
     */
    public function getUrlsAsString(): ?string
    {
        return implode(
            ', ',
            $this->getUrls()->toArray()
        );
    }

    /**
     * @ApiProperty(iri="http://schema.org/url")
     * @return Collection Collection of URL addresses from contact details.
     */
    public function getUrls(): Collection
    {
        return $this->getDetails(ContactDetailType::TYPE_URL);
    }

    /**
     * @param DateTime|null $dateTime
     * @param bool|false    $onlyWithActivatedUser
     *
     * @return Collection
     * @noinspection PhpUnusedParameterInspection
     */
    public function getContactPersons(?DateTime $dateTime = null, bool $onlyWithActivatedUser = false): Collection
    {
        if ($onlyWithActivatedUser) {
            return $this->getAppUser() && $this->getAppUser()->isActive() ? new ArrayCollection([$this]) : new ArrayCollection();
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

    public function hasActivatedUser(): bool
    {
        return $this->getAppUser() && $this->getAppUser()->isActivated();
    }

    public function getStudies(?DateTime $dateTime = null, bool $recursive = false): Collection
    {
        return $this->getPositions($dateTime, Position::STUDY_POSITION_TYPES, $recursive);
    }

    public function getPositions(?DateTime $dateTime = null, ?array $types = null, bool $recursive = false): Collection
    {
        $out = $this->positions ?? new ArrayCollection();
        if (null !== $dateTime) {
            $out = $out->filter(fn(Position $p): bool => $p->isInDateRange($dateTime));
        }
        if (!empty($types)) {
            $out = $out->filter(fn(Position $position) => in_array($position->getType(), $types, true));
        }
        if (true === $recursive && $this instanceof Organization) {
            foreach ($this->getSubOrganizations() as $subOrganization) {
                if ($subOrganization instanceof self) {
                    $subOrganization->getPositions($dateTime, $types, true)->map(fn(Position $p) => $out->add($p));
                }
            }
        }

        return $out;
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

    public function getMemberPositions(?DateTime $dateTime = null, bool $recursive = false): Collection
    {
        return $this->getPositions($dateTime, Position::MEMBER_POSITION_TYPES, $recursive);
    }

    public function getMemberAndEmployeePositions(?DateTime $dateTime = null, bool $recursive = false): Collection
    {
        return $this->getPositions($dateTime, Position::EMPLOYEE_MEMBER_POSITION_TYPES, $recursive);
    }

    public function getEmployeePositions(?DateTime $dateTime = null, bool $recursive = false): Collection
    {
        return $this->getPositions($dateTime, Position::EMPLOYEE_POSITION_TYPES, $recursive);
    }

    public function getManagerPositions(?DateTime $dateTime = null, bool $recursive = false): Collection
    {
        return $this->getPositions($dateTime, Position::STUDY_POSITION_TYPES, $recursive);
    }

    public function getUrl(): ?string
    {
        return $this->getContactDetailContent(ContactDetailType::TYPE_URL);
    }

    public function getContactDetailContent(?string $typeString = null): ?string
    {
        $detail = $this->getDetails($typeString)->first();

        return !empty($detail) && $detail instanceof ContactDetail ? $detail->getContent() : null;
    }

    public function getPhone(): ?string
    {
        return $this->getContactDetailContent(ContactDetailType::TYPE_PHONE);
    }

    /**
     * @ApiProperty(iri="http://schema.org/telephone")
     * @return Collection Collection of telephone numbers of AbstractContact (Person or Organization)
     */
    public function getPhones(): Collection
    {
        return $this->getDetails(ContactDetailType::TYPE_PHONE);
    }

    public function getAddress(): ?string
    {
        return $this->details->first();
    }

    /**
     * @ApiProperty(iri="http://schema.org/legalName")
     * @return string (Official) Name of AbstractContact (Person or Organization)
     */
    public function getLegalName(): ?string
    {
        return $this->getName();
    }

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
    public function getMailerAddress(): ?Address
    {
        $name = $this->getName() ?? ($this->getAppUser() ? $this->getAppUser()->getFullName() : '') ?? '';
        $eMail = ($this->getAppUser() ? $this->getAppUser()->getEmail() : $this->getEmail()) ?? '';
        if (empty($eMail)) {
            $eMail = $this->getEmail();
        }

        return empty($eMail) ? null : new Address($eMail, $name);
    }

    public function getEmail(): ?string
    {
        return $this->getContactDetailContent(ContactDetailType::TYPE_EMAIL);
    }

    /**
     * @ApiProperty(iri="http://schema.org/email")
     * @return Collection Collection of e-mail addresses from contact details
     */
    public function getEmails(): Collection
    {
        return $this->getDetails(ContactDetailType::TYPE_EMAIL);
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

    public function getUsersOfPersons(): Collection
    {
        return $this->getPersons()->map(fn(Person $person) => $person->getAppUser());
    }

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

    public function getManagedDepartments(): Collection
    {
        // TODO: Return managed departments.
        return new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getName() ?? '';
    }

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
        if (null !== $position && !$position->isStudy()) {
            throw new InvalidArgumentException('Špatný typ pozice ('.$position->getType().' není typ studia)');
        }
        $this->removePosition($position);
    }

    abstract public function removePosition(?Position $position): void;

    /**
     * @param Position|null $position
     *
     * @throws InvalidArgumentException
     */
    public function removeRegularPosition(?Position $position): void
    {
        if (null !== $position && !$position->isRegularPosition()) {
            throw new InvalidArgumentException('Špatný typ pozice ('.$position->getType().' není typ zaměstnání)');
        }
        $this->removePosition($position);
    }

    public function getGender(): string
    {
        return self::GENDER_UNISEX;
    }

    public function destroyRevisions(): void
    {
    }
}
