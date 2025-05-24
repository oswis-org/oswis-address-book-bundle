<?php
/**
 * @noinspection PhpUnused
 * @noinspection PropertyCanBePrivateInspection
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Entity;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use OswisOrg\OswisAddressBookBundle\Traits\GeoCoordinatesTrait;
use OswisOrg\OswisCoreBundle\Entity\NonPersistent\Nameable;
use OswisOrg\OswisCoreBundle\Entity\NonPersistent\PostalAddress;
use OswisOrg\OswisCoreBundle\Interfaces\Common\NameableInterface;
use OswisOrg\OswisCoreBundle\Traits\AddressBook\PostalAddressTrait;
use OswisOrg\OswisCoreBundle\Traits\AddressBook\UrlTrait;
use OswisOrg\OswisCoreBundle\Traits\Common\EntityPublicTrait;
use OswisOrg\OswisCoreBundle\Traits\Common\NameableTrait;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @OswisOrg\OswisCoreBundle\Filter\SearchAnnotation({
 *     "id",
 *     "name",
 *     "shortName",
 *     "description",
 *     "note"
 * })
 */
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['entities_get', 'address_book_places_get']],
            security: "is_granted('ROLE_CUSTOMER')"
        ),
        new Post(
            denormalizationContext: ['groups' => ['entities_post', 'address_book_places_post']],
            security: "is_granted('ROLE_MANAGER')"
        ),
        new Get(
            normalizationContext: ['groups' => ['entity_get', 'address_book_place_get']],
            security: "is_granted('ROLE_CUSTOMER')"
        ),
        new Put(
            denormalizationContext: ['groups' => ['entity_put', 'address_book_place_put']],
            security: "is_granted('ROLE_MANAGER')"
        ),
        new Delete(),
    ],
    security: "is_granted('ROLE_MANAGER')"
)]
#[Entity]
#[Table(name: 'address_book_place')]
#[Cache(usage: 'NONSTRICT_READ_WRITE', region: 'address_book_contact')]
#[ApiFilter(SearchFilter::class, properties: [
    'id'             => 'exact',
    'parentPlace.id' => 'exact',
])]
#[ApiFilter(BooleanFilter::class, properties: [
    'publicOnWeb',
])]
#[ApiFilter(OrderFilter::class)]
class Place implements NameableInterface
{
    use NameableTrait;
    use PostalAddressTrait;
    use UrlTrait;
    use GeoCoordinatesTrait;
    use EntityPublicTrait;

    #[Column(type: 'integer', nullable: true)]
    protected ?int $floorNumber = null;

    #[Column(type: 'integer', nullable: true)]
    protected ?int $roomNumber = null;

    #[Column(type: 'string', nullable: true)]
    protected ?string $ionIcon = null;

    #[ManyToOne(targetEntity: self::class, fetch: 'EAGER', inversedBy: 'subPlaces')]
    #[JoinColumn(nullable: true)]
    #[MaxDepth(3)]
    protected ?Place $parentPlace = null;

    #[OneToMany(targetEntity: self::class, mappedBy: 'parentPlace')]
    #[MaxDepth(3)]
    protected ?Collection $subPlaces = null;

    public function __construct(
        ?Nameable $nameable = null,
        ?PostalAddress $address = null,
        ?self $parentPlace = null,
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

    public function addSubPlace(?self $event): void
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

    public function removeSubPlace(?self $event): void
    {
        if ($event && $this->getSubPlaces()->removeElement($event)) {
            $event->setParentPlace(null);
        }
    }

    public function getParentPlace(): ?self
    {
        return $this->parentPlace;
    }

    public function setParentPlace(?self $event): void
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

    public function getIonIcon(): ?string
    {
        return $this->ionIcon;
    }

    public function setIonIcon(?string $ionIcon): void
    {
        $this->ionIcon = $ionIcon;
    }

    public function getStreetAddress(): string
    {
        return $this->getStreet().((!empty($this->getStreet()) && $this->getHouseNumber() !== null) ? ' ' : null).$this->getHouseNumber();
    }
}
