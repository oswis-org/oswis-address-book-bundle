<?php

namespace Zakjakub\OswisAccommodation\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Zakjakub\OswisAccommodationBundle\Entity\PriceList;
use Zakjakub\OswisCoreBundle\Entity\Nameable;

class PriceListManager
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
        ?\DateTime $dateTime = null
    ): PriceList {
        try {
            $em = $this->em;
            $entity = new PriceList($nameable, $dateTime ?? new \DateTime());
            $em->persist($entity);
            $em->flush();
            $infoMessage = 'Created price list: '.$entity->getName().'.';
            $this->logger ? $this->logger->info($infoMessage) : null;

            return $entity;
        } catch (\Exception $e) {
            $this->logger ? $this->logger->info('ERROR: Price list not created: '.$e->getMessage()) : null;

            return null;
        }
    }
}
