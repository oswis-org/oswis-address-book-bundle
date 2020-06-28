<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 * @noinspection PhpUnused
 */

namespace OswisOrg\OswisAddressBookBundle\Entity\AbstractClass;

use ApiPlatform\Core\Annotation\ApiProperty;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use InvalidArgumentException;
use OswisOrg\OswisAddressBookBundle\Entity\AddressBook\AddressBook;
use OswisOrg\OswisAddressBookBundle\Entity\AddressBook\ContactAddressBook;
use OswisOrg\OswisAddressBookBundle\Entity\ContactAddress;
use OswisOrg\OswisAddressBookBundle\Entity\ContactDetail;
use OswisOrg\OswisAddressBookBundle\Entity\ContactDetailCategory;
use OswisOrg\OswisAddressBookBundle\Entity\ContactNote;
use OswisOrg\OswisAddressBookBundle\Entity\MediaObject\ContactFile;
use OswisOrg\OswisAddressBookBundle\Entity\MediaObject\ContactImage;
use OswisOrg\OswisAddressBookBundle\Entity\Organization;
use OswisOrg\OswisAddressBookBundle\Entity\Person;
use OswisOrg\OswisCoreBundle\Entity\AppUser\AppUser;
use OswisOrg\OswisCoreBundle\Entity\NonPersistent\Nameable;
use OswisOrg\OswisCoreBundle\Interfaces\AddressBook\ContactInterface;
use OswisOrg\OswisCoreBundle\Traits\Common\ColorTrait;
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
    use ColorTrait;

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
     *     name="address_book_address_book_contact_connection",
     *     joinColumns={@Doctrine\ORM\Mapping\JoinColumn(name="participant_id", referencedColumnName="id")},
     *     inverseJoinColumns={@Doctrine\ORM\Mapping\JoinColumn(name="participant_contact_id", referencedColumnName="id", unique=true)}
     * )
     */
    protected ?Collection $contactAddressBooks = null;

    /**
     * @Doctrine\ORM\Mapping\OneToOne(targetEntity="OswisOrg\OswisCoreBundle\Entity\AppUser\AppUser", fetch="EAGER")
     */
    protected ?AppUser $appUser = null;

    /**
     * @Doctrine\ORM\Mapping\OneToMany(
     *     targetEntity="OswisOrg\OswisAddressBookBundle\Entity\MediaObject\ContactImage", mappedBy="contact", cascade={"all"}, orphanRemoval=true
     * )
     */
    protected ?Collection $images = null;

    /**
     * @Doctrine\ORM\Mapping\OneToMany(
     *     targetEntity="OswisOrg\OswisAddressBookBundle\Entity\MediaObject\ContactFile", mappedBy="contact", cascade={"all"}, orphanRemoval=true
     * )
     */
    protected ?Collection $files = null;

    /**
     * @param Nameable|null   $nameable
     * @param string|null     $type
     * @param Collection|null $notes
     * @param Collection|null $details
     * @param Collection|null $addresses
     * @param Collection|null $addressBooks
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        ?Nameable $nameable = null,
        ?string $type = null,
        ?Collection $notes = null,
        ?Collection $details = null,
        ?Collection $addresses = null,
        ?Collection $addressBooks = null
    ) {
        $this->images = new ArrayCollection();
        $this->files = new ArrayCollection();
        $this->setFieldsFromNameable($nameable);
        $this->setType($type);
        $this->setNotes($notes);
        $this->setDetails($details);
        $this->setAddresses($addresses);
        $this->setAddressBooks($addressBooks);
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

    public function setContactAddressBooks(?Collection $newContactAddressBooks): void
    {
        $this->contactAddressBooks = $newContactAddressBooks ?? new ArrayCollection();
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

    public function removeContactAddressBook(?ContactAddressBook $contactAddressBook): void
    {
        $this->contactAddressBooks->removeElement($contactAddressBook);
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

    public function addContactAddressBook(?ContactAddressBook $contactAddressBook): void
    {
        if (null !== $contactAddressBook && !$this->contactAddressBooks->contains($contactAddressBook)) {
            $this->contactAddressBooks->add($contactAddressBook);
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

    public function getImage(?string $type = null): ?ContactImage
    {
        $image = $this->getImages($type)->first();

        return $image instanceof ContactImage ? $image : null;
    }

    public function getImages(?string $type = null): Collection
    {
        $images = $this->images ?? new ArrayCollection();

        return empty($type) ? $images : $images->filter(fn(ContactImage $image) => $image->getType() === $type);
    }

    public function addImage(?ContactImage $image): void
    {
        if (null !== $image && !$this->getImages()->contains($image)) {
            $this->getImages()->add($image);
            $image->setContact($this);
        }
    }

    public function removeImage(?ContactImage $image): void
    {
        if (null !== $image && $this->getImages()->removeElement($image)) {
            $image->setContact(null);
        }
    }

    public function addFile(?ContactFile $file): void
    {
        if (null !== $file && !$this->getFiles()->contains($file)) {
            $this->getFiles()->add($file);
            $file->setContact($this);
        }
    }

    public function getFiles(): Collection
    {
        return $this->files ?? new ArrayCollection();
    }

    public function removeFile(?ContactFile $file): void
    {
        if (null !== $file && $this->getFiles()->removeElement($file)) {
            $file->setContact(null);
        }
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
            return $this->getDetails()->filter(fn(ContactDetail $detail) => $typeString === $detail->getCategoryString());
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
     * @return Collection Collection of URL addresses from contact details.
     */
    public function getUrls(): Collection
    {
        return $this->getDetails(ContactDetailCategory::TYPE_URL);
    }

    abstract public function getContactPersons(bool $onlyWithActivatedUser = false): Collection;

    public function hasActivatedUser(): bool
    {
        return $this->getAppUser() && $this->getAppUser()->isActive();
    }

    public function getAppUser(): ?AppUser
    {
        return $this->appUser;
    }

    public function setAppUser(?AppUser $appUser): void
    {
        $this->appUser = $appUser;
    }

    public function getUrl(): ?string
    {
        return $this->getContactDetailContent(ContactDetailCategory::TYPE_URL);
    }

    public function getContactDetailContent(?string $typeString = null): ?string
    {
        $detail = $this->getDetails($typeString)->first();

        return !empty($detail) && $detail instanceof ContactDetail ? $detail->getContent() : null;
    }

    public function getPhone(): ?string
    {
        return $this->getContactDetailContent(ContactDetailCategory::TYPE_PHONE);
    }

    /**
     * @ApiProperty(iri="http://schema.org/telephone")
     * @return Collection Collection of telephone numbers of AbstractContact (Person or Organization)
     */
    public function getPhones(): Collection
    {
        return $this->getDetails(ContactDetailCategory::TYPE_PHONE);
    }

    public function canRead(AppUser $user): bool
    {
        if (!($user instanceof AppUser)) { // User is not logged in.
            return false;
        }

        return $user->hasRole('ROLE_MEMBER') && $user->hasRole('ROLE_USER') && $user === $this->getAppUser();
    }

    /**
     * @return Address
     * @throws LogicException|RfcComplianceException
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
        return $this->getContactDetailContent(ContactDetailCategory::TYPE_EMAIL);
    }

    /**
     * @ApiProperty(iri="http://schema.org/email")
     * @return Collection Collection of e-mail addresses from contact details
     */
    public function getEmails(): Collection
    {
        return $this->getDetails(ContactDetailCategory::TYPE_EMAIL);
    }

    public function canEdit(AppUser $user): bool
    {
        if (!($user instanceof AppUser)) {// User is not logged in.
            return false;
        }

        return $user->hasRole('ROLE_MEMBER') && $user->hasRole('ROLE_USER') && $user === $this->getAppUser();
    }

    public function __toString(): string
    {
        return ''.$this->getName();
    }

    public function getGender(): string
    {
        return self::GENDER_UNISEX;
    }
}
