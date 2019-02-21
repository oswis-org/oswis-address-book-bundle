<?php

namespace Zakjakub\OswisAddressBookBundle\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @Doctrine\ORM\Mapping\Entity
 * @Doctrine\ORM\Mapping\Table(name="place_address")
 * @ApiResource
 */
class PlaceAddress extends AbstractAddress
{

    /**
     * Places situated on this address
     *
     * @var Collection
     * @Doctrine\ORM\Mapping\ManyToMany(targetEntity="Zakjakub\OswisAddressBookBundle\Entity\Place", mappedBy="addressesOfPlace")
     */
    private $placesOnAddress;

    /**
     * PlaceAddress constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->placesOnAddress = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getPlacesOnAddress()
    {
        return $this->placesOnAddress;
    }

    public function addPlacesOnAddress(?Place $newPlace): void
    {
        if ($newPlace && !$this->placesOnAddress->contains($newPlace)) {
            $this->placesOnAddress->add($newPlace);
            $newPlace->addAddressOfPlace($this);
        }
    }

    public function removePlacesOnAddress(Place $oldPlace): void
    {
        if ($oldPlace ?? $this->placesOnAddress->contains($oldPlace)) {
            $this->placesOnAddress->remove($oldPlace);
            $oldPlace->removeAddressOfPlace($this);
        }
    }
}
