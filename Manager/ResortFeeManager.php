<?php

namespace Zakjakub\OswisAccommodation\Manager;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Zakjakub\OswisAccommodationBundle\Entity\ResortFee;
use Zakjakub\OswisCoreBundle\Entity\Nameable;

class ResortFeeManager
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
        ?\DateTime $startDateTime = null,
        ?\DateTime $endDateTime = null,
        ?int $taxRate = null,
        ?int $numericValue = null,
        ?bool $onlyNights = null,
        ?bool $includePets = null,
        ?bool $excludeZtp = null,
        ?bool $excludeZtpAccompaniment = null,
        ?Collection $includedAgeRanges = null,
        ?Collection $excludedAgeRanges = null,
        ?Collection $facilities = null
    ): ResortFee {
        try {
            $em = $this->em;
            $entity = new ResortFee(
                $nameable,
                $startDateTime,
                $endDateTime,
                $taxRate,
                $numericValue,
                $onlyNights,
                $includePets,
                $excludeZtp,
                $excludeZtpAccompaniment,
                $includedAgeRanges,
                $excludedAgeRanges,
                $facilities
            );
            $em->persist($entity);
            $em->flush();
            $infoMessage = 'Created resort fee list: '.$entity->getId().' '.$entity->getName().'.';
            $this->logger ? $this->logger->info($infoMessage) : null;

            return $entity;
        } catch (\Exception $e) {
            $this->logger ? $this->logger->info('ERROR: Resort fee not created: '.$e->getMessage()) : null;

            return null;
        }
    }
}
