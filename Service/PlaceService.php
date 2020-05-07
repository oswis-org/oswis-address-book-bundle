<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use OswisOrg\OswisAddressBookBundle\Entity\Place;
use OswisOrg\OswisCoreBundle\Entity\NonPersistent\Nameable;
use OswisOrg\OswisCoreBundle\Entity\NonPersistent\PostalAddress;
use Psr\Log\LoggerInterface;

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
        ?PostalAddress $address = null,
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
