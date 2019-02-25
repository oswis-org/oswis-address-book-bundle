<?php

namespace Zakjakub\OswisAddressBookBundle\Entity\AbstractClass;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Zakjakub\OswisAddressBookBundle\Entity\ContactAddress;
use Zakjakub\OswisAddressBookBundle\Entity\ContactDetail;
use Zakjakub\OswisAddressBookBundle\Entity\ContactNote;
use Zakjakub\OswisAddressBookBundle\Entity\Person;
use Zakjakub\OswisAddressBookBundle\Entity\Place;
use Zakjakub\OswisCoreBundle\Entity\AbstractRevisionContainer;
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
 * @ApiResource()
 */
abstract class AbstractContact extends AbstractRevisionContainer
{
    use BasicEntityTrait;

    /**
     * @var string|null $type Type of contact (person, organization, school, department...)
     *
     * @Doctrine\ORM\Mapping\Column(type="string", nullable=true)
     */
    private $type;

    /**
     * Notes about person.
     *
     * @var Collection|null
     * @Doctrine\ORM\Mapping\OneToMany(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\ContactNote",
     *     mappedBy="contact",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    private $internalNotes;

    /**
     *  Contact details (e-mails, phones...)
     *
     * @var Collection|null
     * @Doctrine\ORM\Mapping\OneToMany(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\ContactDetail",
     *     mappedBy="contact",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    private $contactDetails;

    /**
     * Postal addresses of AbstractContact (Person, Organization)
     *
     * @var Collection|null
     * @Doctrine\ORM\Mapping\OneToMany(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\ContactAddress",
     *     mappedBy="contact",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     * @ApiProperty(iri="http://schema.org/address")
     */
    private $addresses;

    abstract public function setContactName(?string $dummy): void;

    /**
     * @param ContactNote|null $personNote
     */
    final public function addInternalNote(?ContactNote $personNote): void
    {
        if (!$personNote) {
            return;
        }
        if (!$this->internalNotes->contains($personNote)) {
            $this->internalNotes->add($personNote);
        }
        $personNote->setContact($this);
    }

    /**
     * @param ContactNote|null $personNote
     */
    final public function removeInternalNote(?ContactNote $personNote): void
    {
        if ($personNote && $this->internalNotes->removeElement($personNote)) {
            $personNote->setContact(null);
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
        return $this->contactDetails;
    }

    /**
     * @return Collection
     */
    final public function getAddresses(): Collection
    {
        return $this->addresses;
    }

    /**
     * @return Collection
     */
    final public function getInternalNotes(): Collection
    {
        return $this->internalNotes;
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

    /**
     * @ApiProperty(iri="http://schema.org/legalName")
     * @return string (Official) Name of AbstractContact (Person or Organization)
     */
    final public function getLegalName(): string
    {
        return $this->getContactName();
    }

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

    final public function containsUserInPersons(AppUser $user): bool
    {
        return $this->getUsersOfPersons()->contains($user);
    }

    final public function getUsersOfPersons(): Collection
    {
        $users = new ArrayCollection();
        $this->getPersons()->forAll(
            function (Person $person) use ($users) {
                $users->add($person->getAppUser());
            }
        );

        return $users;
    }

    final public function getPersons(): Collection
    {
        $persons = new ArrayCollection();
        if ($this instanceof Person) {
            $persons->add($this);
        } elseif ($this instanceof OrganizationRevision) {
            $this->getPositions()->forAll(
                function (Person $person) use ($persons) {
                    $persons->add($person);
                }
            );
        }

        return $persons;
    }

    final public function getManagedDepartments(): ArrayCollection
    {
        // TODO: Return managed departmenmts.
        return new ArrayCollection();
    }


    /**
     * @return null|string
     */
    final public function getType(): string
    {
        return $this->type ?? '';
    }

    /**
     * @param null|string $type
     */
    final public function setType(?string $type): void
    {
        $this->type = $type ?? '';
    }


}
