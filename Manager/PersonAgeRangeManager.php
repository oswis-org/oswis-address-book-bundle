<?php

namespace Zakjakub\OswisAccommodation\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Zakjakub\OswisAccommodationBundle\Entity\Person;
use Zakjakub\OswisAccommodationBundle\Entity\PersonAgeRange;
use Zakjakub\OswisCoreBundle\Entity\Nameable;

/**
 * Class PersonAgeRangeManager
 * @package Zakjakub\OswisAccommodation\Manager
 */
class PersonAgeRangeManager
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

    /**
     * @param Person    $person
     * @param \DateTime $dateTime
     *
     * @return PersonAgeRange
     * @throws \Exception
     */
    final public function getAgeRangeByPerson(Person $person, \DateTime $dateTime): PersonAgeRange
    {
        $finalAgeRange = null;
        $agesDiff = \PHP_INT_MAX;
        $birthDate = $person->getBirthDate();
        $ageRangeRepository = $this->em->getRepository(PersonAgeRange::class);
        $ageRanges = $ageRangeRepository->findAll();
        foreach ($ageRanges as $ageRange) {
            \assert($ageRange instanceof PersonAgeRange);
            $diff = $ageRange->agesDiff($dateTime);
            if ($diff < $agesDiff && $ageRange->containsBirthDate($birthDate, $dateTime)) {
                $finalAgeRange = $ageRange;
                $agesDiff = $diff;
            }
        }

        return $finalAgeRange;
    }

    /**
     * @param Nameable|null  $nameable
     * @param int|null       $minAge
     * @param int|null       $maxAge
     * @param \DateTime|null $dateTime
     *
     * @return PersonAgeRange
     * @throws \Exception
     */
    final public function create(
        ?Nameable $nameable = null,
        int $minAge = null,
        int $maxAge = null,
        \DateTime $dateTime = null
    ): PersonAgeRange {
        $em = $this->em;
        // $personAgeRangeRepo = $em->getRepository(PersonAgeRange::class);
        $personAgeRange = new PersonAgeRange($nameable, $minAge, $maxAge, $dateTime);
        $em->persist($personAgeRange);
        $em->flush();
        try {
            $infoMessage = 'Created age range: '.$personAgeRange->getName().'.';
            $this->logger ? $this->logger->info($infoMessage) : null;
        } catch (\Exception $e) {
            $this->logger ? $this->logger->info($e->getMessage()) : null;
        }

        return $personAgeRange;
    }
}
