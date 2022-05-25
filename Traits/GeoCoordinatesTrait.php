<?php
/**
 * @noinspection PhpUnused
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Traits;

use Doctrine\ORM\Mapping\Column;

trait GeoCoordinatesTrait
{
    /**
     * Geo latitude (WGS 84).
     * @example 37.42242
     */
    #[Column(type: 'decimal', precision: 10, scale: 7, nullable: true)]
    protected ?float $geoLatitude = null;

    /**
     * Geo longitude (WGS 84).
     * @example -122.08585
     */
    #[Column(type: 'decimal', precision: 10, scale: 7, nullable: true)]
    protected ?float $geoLongitude = null;

    /**
     * Geo elevation (WGS 84, in meters).
     * @example 1000
     */
    #[Column(type: 'integer', nullable: true)]
    protected ?int $geoElevation = null;

    public function getGeoLon(): ?float
    {
        return $this->getGeoLongitude();
    }

    public function getGeoLongitude(): ?float
    {
        return $this->geoLongitude;
    }

    public function setGeoLongitude(?float $geoLongitude): void
    {
        $this->geoLongitude = $geoLongitude;
    }

    public function getGeoLat(): ?float
    {
        return $this->getGeoLatitude();
    }

    public function getGeoLatitude(): ?float
    {
        return $this->geoLatitude;
    }

    public function setGeoLatitude(?float $geoLatitude): void
    {
        $this->geoLatitude = $geoLatitude;
    }

    public function getGeoEle(): ?int
    {
        return $this->getGeoElevation();
    }

    public function getGeoElevation(): ?int
    {
        return $this->geoElevation;
    }

    public function setGeoElevation(?int $geoElevation): void
    {
        $this->geoElevation = $geoElevation;
    }
}
