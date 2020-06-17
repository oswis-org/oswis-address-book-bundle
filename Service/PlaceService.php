<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use OswisOrg\OswisAddressBookBundle\Entity\Place;
use Psr\Log\LoggerInterface;

class PlaceService
{
    protected EntityManagerInterface $em;

    protected LoggerInterface $logger;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    public function create(Place $place): ?Place
    {
        try {
            $this->em->persist($place);
            $this->em->flush();
            $infoMessage = 'Created place: '.$place->getId().' '.$place->getName().'.';
            $this->logger->info($infoMessage);

            return $place;
        } catch (Exception $e) {
            $this->logger->info('ERROR: Place not created: '.$e->getMessage());

            return null;
        }
    }
}
