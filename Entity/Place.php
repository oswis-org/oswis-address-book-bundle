<?php /** @noinspection PhpUnused */

namespace Zakjakub\OswisAddressBookBundle\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Zakjakub\OswisCoreBundle\Entity\Address;
use Zakjakub\OswisCoreBundle\Entity\Nameable;
use Zakjakub\OswisCoreBundle\Filter\SearchAnnotation as Searchable;
use Zakjakub\OswisCoreBundle\Traits\Entity\AddressTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\BasicEntityTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\GeoCoordinatesTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\NameableBasicTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\UrlTrait;

/**
 * @Doctrine\ORM\Mapping\Entity
 * @Doctrine\ORM\Mapping\Table(name="address_book_place")
 * @ApiResource(
 *   attributes={
 *     "filters"={"search"},
 *     "access_control"="is_granted('ROLE_MANAGER')"
 *   },
 *   collectionOperations={
 *     "get"={
 *       "access_control"="is_granted('ROLE_MANAGER')",
 *       "normalization_context"={"groups"={"address_book_places_get"}},
 *     },
 *     "post"={
 *       "access_control"="is_granted('ROLE_MANAGER')",
 *       "denormalization_context"={"groups"={"address_book_places_post"}}
 *     }
 *   },
 *   itemOperations={
 *     "get"={
 *       "access_control"="is_granted('ROLE_MANAGER')",
 *       "normalization_context"={"groups"={"address_book_place_get"}},
 *     },
 *     "put"={
 *       "access_control"="is_granted('ROLE_MANAGER')",
 *       "denormalization_context"={"groups"={"address_book_place_put"}}
 *     },
 *     "delete"={
 *       "access_control"="is_granted('ROLE_ADMIN')",
 *       "denormalization_context"={"groups"={"address_book_place_delete"}}
 *     }
 *   }
 * )
 * @ApiFilter(OrderFilter::class)
 * @Searchable({
 *     "id",
 *     "name",
 *     "description",
 *     "note",
 *     "roomCode"
 * })
 * @Doctrine\ORM\Mapping\Cache(usage="NONSTRICT_READ_WRITE", region="address_book_contact")
 */
class Place
{
    use BasicEntityTrait;
    use NameableBasicTrait;
    use AddressTrait;
    use UrlTrait;
    use GeoCoordinatesTrait;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $floorNumber;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $roomNumber;

    /**
     * Parent place (if this is not top level place).
     * @var Place|null $parentPlace
     * @Doctrine\ORM\Mapping\ManyToOne(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\Place",
     *     inversedBy="subPlaces",
     *     fetch="EAGER"
     * )
     * @Doctrine\ORM\Mapping\JoinColumn(nullable=true)
     */
    protected $parentPlace;

    /**
     * Sub events.
     * @var Collection|null $subPlaces
     * @Doctrine\ORM\Mapping\OneToMany(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\Place",
     *     mappedBy="parentPlace",
     *     fetch="EAGER"
     * )
     */
    protected $subPlaces;

    /**
     * Place constructor.
     *
     * @param Nameable|null $nameable
     * @param Address|null  $address
     * @param Place|null    $parentPlace
     * @param int|null      $floorNumber
     * @param int|null      $roomNumber
     * @param string|null   $url
     * @param float|null    $geoLatitude
     * @param float|null    $geoLongitude
     * @param int|null      $geoElevation
     */
    public function __construct(
        ?Nameable $nameable = null,
        ?Address $address = null,
        ?Place $parentPlace = null,
        ?int $floorNumber = null,
        ?int $roomNumber = null,
        ?string $url = null,
        ?float $geoLatitude = null,
        ?float $geoLongitude = null,
        ?int $geoElevation = null
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
        $this->setGeoElevation($geoElevation);
    }

    /**
     * @return Collection
     */
    final public function getSubPlaces(): Collection
    {
        return $this->subPlaces;
    }

    /**
     * @return bool
     */
    final public function isRootPlace(): bool
    {
        return $this->parentPlace ? false : true;
    }

    final public function addSubPlace(?Place $event): void
    {
        if ($event && !$this->subPlaces->contains($event)) {
            $this->subPlaces->add($event);
            $event->setParentPlace($this);
        }
    }

    final public function removeSubPlace(?Place $event): void
    {
        if (!$event) {
            return;
        }
        if ($this->subPlaces->removeElement($event)) {
            $event->setParentPlace(null);
        }
    }

    final public function getParentPlace(): ?Place
    {
        return $this->parentPlace;
    }

    final public function setParentPlace(?Place $event): void
    {
        if ($this->parentPlace && $event !== $this->parentPlace) {
            $this->parentPlace->removeSubPlace($this);
        }
        $this->parentPlace = $event;
        if ($this->parentPlace) {
            $this->parentPlace->addSubPlace($this);
        }
    }

    /**
     * @return int|null
     */
    final public function getFloorNumber(): ?int
    {
        return $this->floorNumber;
    }

    /**
     * @param int|null $floorNumber
     */
    final public function setFloorNumber(?int $floorNumber): void
    {
        $this->floorNumber = $floorNumber;
    }

    /**
     * @return int|null
     */
    final public function getRoomNumber(): ?int
    {
        return $this->roomNumber;
    }

    /**
     * @param int|null $roomNumber
     */
    final public function setRoomNumber(?int $roomNumber): void
    {
        $this->roomNumber = $roomNumber;
    }

    final public function getStreetAddress(): string
    {
        $output = $this->getStreet();
        $output .= (!empty($this->getStreet()) && $this->getHouseNumber() !== null) ? ' ' : null;
        $output .= $this->getHouseNumber();

        return $output;
    }
}
