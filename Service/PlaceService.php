<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 */

namespace Zakjakub\OswisAddressBookBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Zakjakub\OswisAddressBookBundle\Entity\Place;
use Zakjakub\OswisCoreBundle\Entity\Address;
use Zakjakub\OswisCoreBundle\Entity\Nameable;

class PlaceService
{
    protected EntityManagerInterface $em;

    protected ?LoggerInterface $logger;

    public function __construct(EntityManagerInterface $em, ?LoggerInterface $logger = null)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    public function create(
        ?Nameable $nameable = null,
        ?Address $address = null,
        ?Place $parentPlace = null,
        ?int $floorNumber = null,
        ?int $roomNumber = null,
        ?string $url = null,
        ?float $geoLatitude = null,
        ?float $geoLongitude = null
    ): ?Place {
        try {
            $entity = new Place($nameable, $address, $parentPlace, $floorNumber, $roomNumber, $url, $geoLatitude, $geoLongitude);
            $this->em->persist($entity);
            $this->em->flush();
            $infoMessage = 'Created place: '.$entity->getId().' '.$entity->getName().'.';
            $this->logger ? $this->logger->info($infoMessage) : null;

            return $entity;
        } catch (Exception $e) {
            $this->logger ? $this->logger->info('ERROR: Place not created: '.$e->getMessage()) : null;

            return null;
        }
    }
}
