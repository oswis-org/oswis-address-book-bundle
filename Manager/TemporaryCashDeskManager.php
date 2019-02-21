<?php

namespace Zakjakub\OswisAccommodation\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Zakjakub\OswisAccommodationBundle\Entity\Employer;
use Zakjakub\OswisAccommodationBundle\Entity\TemporaryCashDesk;
use Zakjakub\OswisCoreBundle\Entity\Nameable;

class TemporaryCashDeskManager
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
        ?Employer $organization = null,
        ?int $baseValue = null
    ): TemporaryCashDesk {
        $em = $this->em;
        try {
            $entity = new TemporaryCashDesk($nameable, $organization, $baseValue);
            $em->persist($entity);
            $em->flush();
            $infoMessage = 'Created temporary cash desk: '.$entity->getName().'.';
            $this->logger ? $this->logger->info($infoMessage) : null;

            return $entity;
        } catch (\Exception $e) {
            $this->logger ? $this->logger->info($e->getMessage()) : null;

            return null;
        }
    }
}
