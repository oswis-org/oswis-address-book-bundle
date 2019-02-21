<?php

namespace Zakjakub\OswisAccommodation\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Zakjakub\OswisAccommodationBundle\Entity\Facility;
use Zakjakub\OswisAccommodationBundle\Entity\Room;
use Zakjakub\OswisAccommodationBundle\Entity\RoomCategory;
use Zakjakub\OswisCoreBundle\Entity\Nameable;

class RoomManager
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
        ?Facility $facility = null,
        ?RoomCategory $roomCategory = null
    ): Room {
        try {
            $em = $this->em;
            $entity = new Room($nameable, $roomCategory, $facility);
            $em->persist($entity);
            $em->flush();
            $infoMessage = 'Created room: '.$entity->getName().'.';
            $this->logger ? $this->logger->info($infoMessage) : null;

            return $entity;
        } catch (\Exception $e) {
            $this->logger ? $this->logger->info('ERROR: Room not created: '.$e->getMessage()) : null;

            return null;
        }
    }
}
