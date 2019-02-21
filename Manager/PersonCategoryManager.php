<?php

namespace Zakjakub\OswisAccommodation\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Zakjakub\OswisAccommodationBundle\Entity\PersonCategory;
use Zakjakub\OswisCoreBundle\Entity\Nameable;

class PersonCategoryManager
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * PersonAgeRangeManager constructor.
     *
     * @param EntityManagerInterface $em
     * @param LoggerInterface|null   $logger
     */
    public function __construct(
        EntityManagerInterface $em,
        ?LoggerInterface $logger = null
    ) {
        $this->em = $em;
        $this->logger = $logger;
    }

    final public function create(?Nameable $nameable = null): PersonCategory
    {
        $em = $this->em;
        // $personAgeRangeRepo = $em->getRepository(PersonAgeRange::class);
        $personCategory = new PersonCategory($nameable);
        $em->persist($personCategory);
        $em->flush();
        try {
            $infoMessage = 'Created person category: '.$personCategory->getName().'.';
            $this->logger ? $this->logger->info($infoMessage) : null;
        } catch (\Exception $e) {
            $this->logger ? $this->logger->info($e->getMessage()) : null;
        }

        return $personCategory;
    }
}
