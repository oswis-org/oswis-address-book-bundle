<?php
/**
 * @noinspection PhpUnused
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Entity\AbstractClass;

use ApiPlatform\Core\Annotation\ApiProperty;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\InverseJoinColumn;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
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
use OswisOrg\OswisCoreBundle\Interfaces\Common\TypeInterface;
use OswisOrg\OswisCoreBundle\Traits\Common\ColorTrait;
use OswisOrg\OswisCoreBundle\Traits\Common\EntityPublicTrait;
use OswisOrg\OswisCoreBundle\Traits\Common\NameableTrait;
use OswisOrg\OswisCoreBundle\Traits\Common\TypeTrait;
use Symfony\Component\Mime\Address;
use Symfony\Component\Serializer\Annotation\DiscriminatorMap;

use function assert;
use function in_array;

/**
 * Class Contact (abstract class for Person, Department, Organization, School...).
 */
#[Entity]
#[Table(name: 'address_book_abstract_contact')]
#[InheritanceType('JOINED')]
#[DiscriminatorColumn(name: 'discriminator', type: 'string')]
#[DiscriminatorMap(typeProperty: 'discriminator', mapping: [
    'address_book_person'       => Person::class,
    'address_book_organization' => Organization::class,
])]
#[Cache(usage: 'NONSTRICT_READ_WRITE', region: 'address_book_contact')]
abstract class AbstractContact implements ContactInterface, TypeInterface
{
    use NameableTrait;
    use TypeTrait;
    use EntityPublicTrait;
    use ColorTrait;

    /** @var Collection<ContactNote> Notes about person. */
    #[OneToMany(mappedBy: 'contact', targetEntity: ContactNote::class, cascade: ['all'], fetch: 'EAGER', orphanRemoval: true)]
    protected Collection $notes;

    /** @var Collection<ContactDetail> Postal addresses of AbstractContact (Person, Organization). */
    #[OneToMany(mappedBy: 'contact', targetEntity: ContactDetail::class, cascade: ['all'], fetch: 'EAGER', orphanRemoval: true)]
    protected Collection $details;

    /** @var Collection<ContactAddress> Postal addresses of AbstractContact (Person, Organization). */
    #[ApiProperty(iri: 'http://schema.org/address')]
    #[OneToMany(mappedBy: 'contact', targetEntity: ContactAddress::class, cascade: ['all'], fetch: 'EAGER', orphanRemoval: true)]
    protected Collection $addresses;

    /** @var Collection<ContactAddressBook> */
    #[ManyToMany(targetEntity: ContactAddressBook::class, cascade: ['all'], fetch: 'EAGER')]
    #[JoinTable(name: 'address_book_address_book_contact_connection')]
    #[JoinColumn(name: "contact_id", referencedColumnName: "id")]
    #[InverseJoinColumn(name: "contact_address_book_id", referencedColumnName: "id", unique: true)]
    protected Collection $contactAddressBooks;

    #[OneToOne(targetEntity: AppUser::class, fetch: 'EAGER')]
    protected ?AppUser $appUser = null;

    /** @var Collection<ContactImage> */
    #[OneToMany(mappedBy: 'contact', targetEntity: ContactImage::class, cascade: ['all'], orphanRemoval: true)]
    protected Collection $images;

    #[OneToMany(mappedBy: 'contact', targetEntity: ContactFile::class, cascade: ['all'], orphanRemoval: true)]
    protected Collection $files;

    /**
     * @param  Nameable|null  $nameable
     * @param  string|null  $type
     * @param  Collection<ContactNote>|null  $notes
     * @param  Collection<ContactDetail>|null  $details
     * @param  Collection<ContactAddress>|null  $addresses
     * @param  Collection<AddressBook>|null  $addressBooks
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
        $this->images              = new ArrayCollection();
        $this->files               = new ArrayCollection();
        $this->details             = new ArrayCollection();
        $this->notes               = new ArrayCollection();
        $this->addresses           = new ArrayCollection();
        $this->contactAddressBooks = new ArrayCollection();
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
        /** @var Collection<AddressBook>|null $newAddressBooks */
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

    /**
     * @return \Doctrine\Common\Collections\Collection<AddressBook>
     */
    public function getAddressBooks(): Collection
    {
        return $this->getContactAddressBooks()->map(function (mixed $addressBookContactConnection) {
            assert($addressBookContactConnection instanceof ContactAddressBook);
            $addressBook = $addressBookContactConnection->getAddressBook();
            assert($addressBook instanceof AddressBook);

            return $addressBook;
        },);
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
        $this->getContactAddressBooks()->removeElement($contactAddressBook);
    }

    public function containsAddressBook(AddressBook $addressBook): bool
    {
        return $this->getAddressBooks()->contains($addressBook);
    }

    public function addAddressBook(AddressBook $addressBook): void
    {
        if (!$this->containsAddressBook($addressBook)) {
            $this->addContactAddressBook(new ContactAddressBook($addressBook));
        }
    }

    public function addContactAddressBook(?ContactAddressBook $contactAddressBook): void
    {
        if (null !== $contactAddressBook && !$this->getContactAddressBooks()->contains($contactAddressBook)) {
            $this->getContactAddressBooks()->add($contactAddressBook);
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

    public function getOneImage(?string $type = null): ?ContactImage
    {
        $image = $this->getImages($type)->first();

        return $image instanceof ContactImage ? $image : null;
    }

    /**
     * @param  string|null  $type
     *
     * @return \Doctrine\Common\Collections\Collection<ContactImage>
     */
    public function getImages(?string $type = null): Collection
    {
        $images = $this->images;
        if (!empty($type)) {
            $images = $this->images->filter(function (mixed $image) use ($type) {
                return $image instanceof ContactImage && $image->getType() === $type;
            },);
        }

        /** @var Collection<ContactImage> $images */
        return $images;
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
        $this->getImages()->removeElement($image);
    }

    public function addFile(?ContactFile $file): void
    {
        if (null !== $file && !$this->getFiles()->contains($file)) {
            $this->getFiles()->add($file);
        }
    }

    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function removeFile(?ContactFile $file): void
    {
        $this->getFiles()->removeElement($file);
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
        $this->setDetails($this->getDetails()->filter(fn(mixed $detail) => $detail instanceof ContactDetail && !empty($detail->getContent())));
    }

    /**
     * @param  string|null  $typeString
     *
     * @return \Doctrine\Common\Collections\Collection<ContactDetail>
     */
    public function getDetails(?string $typeString = null): Collection
    {
        $details = $this->details;
        if (!empty($typeString)) {
            $details = $details->filter(fn(mixed $detail) => $detail instanceof ContactDetail && $typeString === $detail->getCategoryString());
        }

        /** @var Collection<ContactDetail> $details */
        return $details;
    }

    public function setDetails(?Collection $newDetails): void
    {
        /** @var Collection<ContactDetail>|null $newDetails */
        $newDetails ??= new ArrayCollection();
        foreach ($this->details as $oldDetail) {
            if (!$newDetails->contains($oldDetail)) {
                $this->removeDetail($oldDetail);
            }
        }
        foreach ($newDetails as $newDetail) {
            if (!$this->getDetails()->contains($newDetail)) {
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

    /**
     * @return \Doctrine\Common\Collections\Collection<ContactNote>
     */
    public function getNotes(): Collection
    {
        return $this->notes;
    }

    public function setNotes(?Collection $newNotes): void
    {
        /** @var Collection<ContactNote>|null $newNotes */
        $newNotes ??= new ArrayCollection();
        foreach ($this->getNotes() as $oldNote) {
            if (!$newNotes->contains($oldNote)) {
                $this->removeNote($oldNote);
            }
        }
        foreach ($newNotes as $newNote) {
            if (!$this->getNotes()->contains($newNote)) {
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

    /**
     * @return \Doctrine\Common\Collections\Collection<ContactAddress>
     */
    public function getAddresses(): Collection
    {
        return $this->addresses;
    }

    public function setAddresses(?Collection $newAddresses): void
    {
        /** @var Collection<ContactAddress>|null $newAddresses */
        $newAddresses ??= new ArrayCollection();
        foreach ($this->getAddresses() as $oldAddress) {
            if (!$newAddresses->contains($oldAddress)) {
                $this->removeAddress($oldAddress);
            }
        }
        foreach ($newAddresses as $newAddress) {
            if (!$this->getAddresses()->contains($newAddress)) {
                $this->addAddress($newAddress);
            }
        }
    }

    public function addDetail(?ContactDetail $detail): void
    {
        if (null !== $detail && !$this->getDetails()->contains($detail)) {
            $this->getDetails()->add($detail);
            $detail->setContact($this);
        }
    }

    public function addNote(?ContactNote $note): void
    {
        if (null !== $note && !$this->getNotes()->contains($note)) {
            $this->getNotes()->add($note);
            $note->setContact($this);
        }
    }

    public function addAddress(?ContactAddress $address): void
    {
        if (null !== $address && !$this->getAddresses()->contains($address)) {
            $this->getAddresses()->add($address);
            $address->setContact($this);
        }
    }

    /**
     * Remove notes where no content is present.
     */
    public function removeEmptyNotes(): void
    {
        $this->setNotes($this->getNotes()->filter(fn(mixed $note) => $note instanceof ContactNote && empty($note->getContent())));
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
        return $this->getAppUser()?->isActive() ?? false;
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

    public function canRead(?AppUser $user = null): bool
    {
        return $this->canEdit($user);
    }

    public function canEdit(?AppUser $user = null): bool
    {
        if (!($user instanceof AppUser)) {// User is not logged in.
            return false;
        }

        return $user->hasRole('ROLE_MEMBER') && $user->hasRole('ROLE_USER') && $user === $this->getAppUser();
    }

    /**
     * @return \Symfony\Component\Mime\Address|null
     * @throws \Symfony\Component\Mime\Exception\LogicException
     * @throws \Symfony\Component\Mime\Exception\RfcComplianceException
     */
    public function getMailerAddress(): ?Address
    {
        $name  = $this->getName() ?? $this->getAppUser()?->getFullName() ?? '';
        $eMail = $this->getAppUser()?->getEmail() ?? $this->getEmail() ?? '';
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

    public function __toString(): string
    {
        return $this->getName() ?? (string)$this->getId();
    }

    public function getGender(): string
    {
        return self::GENDER_UNISEX;
    }
}
