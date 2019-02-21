<?php

namespace Zakjakub\OswisAccommodation\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Zakjakub\OswisAccommodationBundle\Entity\JobFairUserCategory;
use Zakjakub\OswisCoreBundle\Entity\Nameable;

class AccommodationUserCategoryManager
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
        ?\DateTime $start = null,
        ?\DateTime $end = null
    ): JobFairUserCategory {
        $em = $this->em;
        try {
            $entity = new JobFairUserCategory($nameable, $start, $end);
            $em->persist($entity);
            $em->flush();
            $infoMessage = 'Created accommodation user category: '.$entity->getName().'.';
            $this->logger ? $this->logger->info($infoMessage) : null;

            return $entity;
        } catch (\Exception $e) {
            $this->logger ? $this->logger->info($e->getMessage()) : null;

            return null;
        }
    }
}
