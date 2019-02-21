<?php

namespace Zakjakub\OswisAddressBookBundle\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Zakjakub\OswisAddressBookBundle\Entity\AbstractClass\AbstractContact;
use Zakjakub\OswisCoreBundle\Traits\Entity\BasicEntityTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\NameableBasicTrait;

/**
 * Class Place
 *
 * Represents a place, containing some addresses and belong to contact. There can be organized events in place.
 *
 * @Doctrine\ORM\Mapping\Entity()
 * @Doctrine\ORM\Mapping\Table(name="address_book_place")
 * @ApiResource(
 *   iri="http://schema.org/Place",
 *   attributes={
 *     "access_control"="is_granted('ROLE_MEMBER')",
 *     "normalization_context"={"groups"={"address_book_places_get"}},
 *     "denormalization_context"={"groups"={"address_book_places_post"}}
 *   },
 *   collectionOperations={
 *     "get"={
 *       "access_control"="is_granted('ROLE_MEMBER')",
 *       "normalization_context"={"groups"={"address_book_places_get"}},
 *     },
 *     "post"={
 *       "access_control"="is_granted('ROLE_MEMBER')",
 *       "denormalization_context"={"groups"={"address_book_places_post"}}
 *     }
 *   },
 *   itemOperations={
 *     "get"={
 *       "access_control"="is_granted('ROLE_MEMBER')",
 *       "normalization_context"={"groups"={"address_book_place_get"}},
 *     },
 *     "put"={
 *       "access_control"="is_granted('ROLE_MEMBER')",
 *       "denormalization_context"={"groups"={"address_book_place_put"}}
 *     },
 *     "delete"={
 *       "access_control"="is_granted('ROLE_MEMBER')",
 *       "denormalization_context"={"groups"={"address_book_place_delete"}}
 *     }
 *   }
 * )
 */
class Place
{
    use BasicEntityTrait;
    use NameableBasicTrait;

    /**
     * Addresses of this Place
     *
     * @var Collection|null $organizations Addresses of this place
     * @Doctrine\ORM\Mapping\ManyToMany(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\PlaceAddress",
     *     inversedBy="placesOnAddress",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     * @Doctrine\ORM\Mapping\JoinTable(name="addresses_of_places")
     */
    private $addressesOfPlace;

    /**
     * Contacts linked to this place
     *
     * @var Collection|null $contacts Contacts in this place
     * @Doctrine\ORM\Mapping\ManyToMany(
     *     targetEntity="AbstractContactRevision",
     *     inversedBy="locations"
     * )
     * @Doctrine\ORM\Mapping\JoinTable(name="contacts_places")
     */
    private $contacts;

    /**
     * Place constructor.
     */
    public function __construct()
    {
        $this->addressesOfPlace = new ArrayCollection();
        $this->contacts = new ArrayCollection();
    }

    /**
     * Addresses of this Place
     *
     * @return Collection
     */
    final public function getAddressesOfPlace(): Collection
    {
        return $this->addressesOfPlace;
    }

    /**
     * Add Address to this Place
     *
     * @param PlaceAddress $newAddress
     */
    final public function addAddressOfPlace(?PlaceAddress $newAddress): void
    {
        if ($newAddress && !$this->addressesOfPlace->contains($newAddress)) {
            $this->addressesOfPlace->add($newAddress);
            $newAddress->addPlacesOnAddress($this);
        }
    }

    /**
     * Remove Address from this Place
     *
     * @param PlaceAddress $oldAddress
     */
    final public function removeAddressOfPlace(?PlaceAddress $oldAddress): void
    {
        if ($oldAddress && $this->addressesOfPlace->contains($oldAddress)) {
            $this->addressesOfPlace->removeElement($oldAddress);
            $oldAddress->removePlacesOnAddress($this);
        }
    }

    /**
     * Add Contact to Place
     *
     * @param AbstractContact $newContact
     */
    final public function addContact(AbstractContact $newContact): void
    {
        if ($newContact && !$this->contacts->contains($newContact)) {
            $this->contacts->add($newContact);
            $newContact->addLocation($this);
        }
    }

    /**
     * Remove Contact from Place
     *
     * @param AbstractContact $removeContact
     */
    final public function removeContact(AbstractContact $removeContact): void
    {
        if ($removeContact ?? $this->contacts->contains($removeContact)) {
            $this->contacts->removeElement($removeContact);
            $removeContact->removeLocation($this);
        }
    }

}
