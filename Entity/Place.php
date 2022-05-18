<?php
/**
 * @noinspection PropertyCanBePrivateInspection
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use OswisOrg\OswisAddressBookBundle\Traits\GeoCoordinatesTrait;
use OswisOrg\OswisCoreBundle\Entity\NonPersistent\Nameable;
use OswisOrg\OswisCoreBundle\Entity\NonPersistent\PostalAddress;
use OswisOrg\OswisCoreBundle\Interfaces\Common\NameableInterface;
use OswisOrg\OswisCoreBundle\Traits\AddressBook\PostalAddressTrait;
use OswisOrg\OswisCoreBundle\Traits\AddressBook\UrlTrait;
use OswisOrg\OswisCoreBundle\Traits\Common\EntityPublicTrait;
use OswisOrg\OswisCoreBundle\Traits\Common\NameableTrait;

/**
 * @Doctrine\ORM\Mapping\Entity
 * @Doctrine\ORM\Mapping\Table(name="address_book_place")
 * @ApiPlatform\Core\Annotation\ApiResource(
 *   attributes={
 *     "filters"={"search"},
 *     "security"="is_granted('ROLE_MANAGER')"
 *   },
 *   collectionOperations={
 *     "get"={
 *       "security"="is_granted('ROLE_USER')",
 *       "normalization_context"={"groups"={"entities_get", "address_book_places_get"}},
 *     },
 *     "post"={
 *       "security"="is_granted('ROLE_MANAGER')",
 *       "denormalization_context"={"groups"={"entities_post", "address_book_places_post"}}
 *     }
 *   },
 *   itemOperations={
 *     "get"={
 *       "security"="is_granted('ROLE_USER')",
 *       "normalization_context"={"groups"={"entity_get", "address_book_place_get"}},
 *     },
 *     "put"={
 *       "security"="is_granted('ROLE_MANAGER')",
 *       "denormalization_context"={"groups"={"entity_put", "address_book_place_put"}}
 *     },
 *     "delete"={}
 *   }
 * )
 * @ApiPlatform\Core\Annotation\ApiFilter(ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter::class)
 * @OswisOrg\OswisCoreBundle\Filter\SearchAnnotation({
 *     "id",
 *     "name",
 *     "shortName",
 *     "description",
 *     "note"
 * })
 * @Doctrine\ORM\Mapping\Cache(usage="NONSTRICT_READ_WRITE", region="address_book_contact")
 */
#[ApiFilter(SearchFilter::class, properties: [
    'id'             => 'exact',
    'parentPlace.id' => 'exact',
])]
#[ApiFilter(BooleanFilter::class, properties: [
    'publicOnWeb',
])]
class Place implements NameableInterface
{
    use NameableTrait;
    use PostalAddressTrait;
    use UrlTrait;
    use GeoCoordinatesTrait;
    use EntityPublicTrait;

    /**
     * @Doctrine\ORM\Mapping\Column(type="integer", nullable=true)
     */
    protected ?int $floorNumber = null;

    /**
     * @Doctrine\ORM\Mapping\Column(type="integer", nullable=true)
     */
    protected ?int $roomNumber = null;

    /**
     * @Doctrine\ORM\Mapping\ManyToOne(targetEntity="OswisOrg\OswisAddressBookBundle\Entity\Place", inversedBy="subPlaces", fetch="EAGER")
     * @Doctrine\ORM\Mapping\JoinColumn(nullable=true)
     * @Symfony\Component\Serializer\Annotation\MaxDepth(3)
     */
    protected ?Place $parentPlace = null;

    /**
     * @Doctrine\ORM\Mapping\OneToMany(targetEntity="OswisOrg\OswisAddressBookBundle\Entity\Place", mappedBy="parentPlace")
     * @Symfony\Component\Serializer\Annotation\MaxDepth(3)
     */
    protected ?Collection $subPlaces = null;

    public function __construct(
        ?Nameable $nameable = null,
        ?PostalAddress $address = null,
        ?Place $parentPlace = null,
        ?int $floorNumber = null,
        ?int $roomNumber = null,
        ?string $url = null,
        ?float $geoLatitude = null,
        ?float $geoLongitude = null
    ) {
        $this->subPlaces = new ArrayCollection();
        $this->setParentPlace($parentPlace);
        $this->setFieldsFromNameable($nameable);
        $this->setFieldsFromAddress($address);
        $this->setFloorNumber($floorNumber);
        $this->setRoomNumber($roomNumber);
        $this->setUrl($url);
        $this->setGeoLatitude($geoLatitude);
        $this->setGeoLongitude($geoLongitude);
    }

    public function isRootPlace(): bool
    {
        return !$this->parentPlace;
    }

    public function addSubPlace(?Place $event): void
    {
        if ($event && !$this->getSubPlaces()->contains($event)) {
            $this->getSubPlaces()->add($event);
            $event->setParentPlace($this);
        }
    }

    public function getSubPlaces(): Collection
    {
        return $this->subPlaces ?? new ArrayCollection();
    }

    public function removeSubPlace(?Place $event): void
    {
        if ($event && $this->getSubPlaces()->removeElement($event)) {
            $event->setParentPlace(null);
        }
    }

    public function getParentPlace(): ?Place
    {
        return $this->parentPlace;
    }

    public function setParentPlace(?Place $event): void
    {
        if (null !== $this->parentPlace && $event !== $this->parentPlace) {
            $this->parentPlace->removeSubPlace($this);
        }
        $this->parentPlace = $event;
        $this->parentPlace?->addSubPlace($this);
    }

    public function getFloorNumber(): ?int
    {
        return $this->floorNumber;
    }

    public function setFloorNumber(?int $floorNumber): void
    {
        $this->floorNumber = $floorNumber;
    }

    public function getRoomNumber(): ?int
    {
        return $this->roomNumber;
    }

    public function setRoomNumber(?int $roomNumber): void
    {
        $this->roomNumber = $roomNumber;
    }

    public function getStreetAddress(): string
    {
        return $this->getStreet().((!empty($this->getStreet()) && $this->getHouseNumber() !== null) ? ' ' : null).$this->getHouseNumber();
    }
}
