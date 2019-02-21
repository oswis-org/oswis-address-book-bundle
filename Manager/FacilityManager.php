<?php

namespace Zakjakub\OswisAccommodation\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Zakjakub\OswisAccommodationBundle\Entity\JobFairUser;
use Zakjakub\OswisAccommodationBundle\Entity\Facility;
use Zakjakub\OswisAccommodationBundle\Entity\Employer;
use Zakjakub\OswisAccommodationBundle\Entity\PriceList;
use Zakjakub\OswisCoreBundle\Entity\Address;
use Zakjakub\OswisCoreBundle\Entity\Nameable;

class FacilityManager
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
        ?Employer $organization = null,
        ?JobFairUser $manager = null,
        ?JobFairUser $facilityManager = null,
        ?\DateTime $dateTime = null,
        ?PriceList $priceList = null
    ): Facility {
        $em = $this->em;
        try {
            $entity = new Facility(
                $nameable,
                $address,
                $organization,
                $manager,
                $facilityManager,
                $priceList,
                $dateTime ?? new \DateTime()
            );
            $em->persist($entity);
            $em->flush();
            $infoMessage = 'Created facility: '.$entity->getName().'.';
            $this->logger ? $this->logger->info($infoMessage) : null;

            return $entity;
        } catch (\Exception $e) {
            $this->logger ? $this->logger->info('Not created facility('.$e->getMessage().')') : null;

            return null;
        }
    }
}
