<?php

namespace Zakjakub\OswisAddressBookBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Zakjakub\OswisAddressBookBundle\Entity\Place;
use Zakjakub\OswisCoreBundle\Entity\Address;
use Zakjakub\OswisCoreBundle\Entity\Nameable;

class PlaceManager
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        EntityManagerInterface $em,
        ?LoggerInterface $logger = null
    ) {
        $this->em = $em;
        $this->logger = $logger;
    }

    final public function create(
        ?Nameable $nameable = null,
        ?Address $address = null,
        ?Place $parentPlace = null,
        ?int $floorNumber = null,
        ?int $roomNumber = null,
        ?string $url = null,
        ?float $geoLatitude = null,
        ?float $geoLongitude = null,
        ?int $geoElevation = null
    ): Place {
        try {
            $em = $this->em;
            $entity = new Place(
                $nameable,
                $address,
                $parentPlace,
                $floorNumber,
                $roomNumber,
                $url,
                $geoLatitude,
                $geoLongitude,
                $geoElevation
            );
            $em->persist($entity);
            $em->flush();
            $infoMessage = 'Created place: '.$entity->getId().' '.$entity->getName().'.';
            $this->logger ? $this->logger->info($infoMessage) : null;

            return $entity;
        } catch (Exception $e) {
            $this->logger ? $this->logger->info('ERROR: Place not created: '.$e->getMessage()) : null;

            return null;
        }
    }
}
