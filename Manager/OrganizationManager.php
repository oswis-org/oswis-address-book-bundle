<?php

namespace Zakjakub\OswisAccommodation\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Zakjakub\OswisAccommodationBundle\Entity\Employer;
use Zakjakub\OswisCoreBundle\Entity\Address;
use Zakjakub\OswisCoreBundle\Entity\Nameable;

class OrganizationManager
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
        ?string $identificationNumber = null,
        ?Address $address = null
    ): Employer {
        $em = $this->em;
        try {
            $entity = new Employer($nameable, $identificationNumber, $address);
            $em->persist($entity);
            $em->flush();
            $infoMessage = 'Created organization: '.$entity->getName().'.';
            $this->logger ? $this->logger->info($infoMessage) : null;

            return $entity;
        } catch (\Exception $e) {
            $this->logger ? $this->logger->info($e->getMessage()) : null;

            return null;
        }
    }
}
