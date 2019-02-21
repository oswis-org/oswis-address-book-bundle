<?php

namespace Zakjakub\OswisAccommodation\Manager;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Zakjakub\OswisAccommodationBundle\Entity\PriceList;
use Zakjakub\OswisAccommodationBundle\Entity\RoomCategoryPrice;

class RoomCategoryPriceManager
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
        ?string $note = null,
        ?int $priority = null,
        ?int $numericValue = null,
        ?PriceList $priceList = null,
        ?Collection $includedAgeRanges = null,
        ?Collection $includedRoomCategories = null,
        ?Collection $priceSpecialPeriods = null,
        ?Collection $includedPersonCategories = null,
        ?bool $petsOnly = null,
        ?bool $petsExcluded = null,
        ?bool $ztpOnly = null,
        ?bool $ztpExcluded = null,
        ?bool $ztpAccompanimentOnly = null,
        ?bool $ztpAccompanimentExcluded = null
    ): RoomCategoryPrice {
        try {
            $em = $this->em;
            $entity = new RoomCategoryPrice(
                $note,
                $priority,
                $numericValue,
                $priceList,
                $includedAgeRanges,
                $includedRoomCategories,
                $priceSpecialPeriods,
                $includedPersonCategories,
                $petsOnly,
                $petsExcluded,
                $ztpOnly,
                $ztpExcluded,
                $ztpAccompanimentOnly,
                $ztpAccompanimentExcluded
            );
            $em->persist($entity);
            $em->flush();
            $infoMessage = 'Created price list: '.$entity->getId().'.';
            $this->logger ? $this->logger->info($infoMessage) : null;

            return $entity;
        } catch (\Exception $e) {
            $this->logger ? $this->logger->info('ERROR: Price not created: '.$e->getMessage()) : null;

            return null;
        }
    }
}
