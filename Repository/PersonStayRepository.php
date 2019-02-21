<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Zakjakub\OswisAccommodationBundle\Entity\PersonStay;
use Zakjakub\OswisCoreBundle\Utils\DateTimeUtils;

/**
 * Class PersonStayRepository
 * @package App\Repository
 */
class PersonStayRepository extends EntityRepository
{

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     *
     * @return PersonStay[]
     * @throws \Exception
     */
    final public function findBetween(?\DateTime $start = null, ?\DateTime $end = null): array
    {
        $start = $start ?? new \DateTime(DateTimeUtils::MIN_DATE_TIME_STRING);
        $end = $end ?? new \DateTime(DateTimeUtils::MAX_DATE_TIME_STRING);

        return $this->createQueryBuilder('personStay')
            ->andWhere('personStay.startDateTime < :end')
            ->andWhere('personStay.endDateTime > :start')
            ->setParameter('start', $start->format('Y-m-d'))
            ->setParameter('end', $end->format('Y-m-d'))
            ->getQuery()
            ->execute([], Query::HYDRATE_OBJECT);
    }

    /**
     * @param \DateTime|null $start
     * @param \DateTime|null $end
     *
     * @return array
     */
    final public function getRoomsOccupancy(?\DateTime $start = null, ?\DateTime $end = null): array
    {
        $start = $start ?? DateTimeUtils::MIN_DATE_TIME_STRING;
        $end = $end ?? DateTimeUtils::MAX_DATE_TIME_STRING;
        $personStays = $this->personStayRepository->findBetween($startDateTime, $endDateTime);
        $output = [];
        $id = 0;
        foreach ($personStays as $personStay) {
            $createNew = true;
            \assert($personStay instanceof PersonStay);
            $start = $personStay->getStartDateTime()->setTime(12, 0);
            $end = $personStay->getEndDateTime()->setTime(12, 0);
            foreach ($output as &$stay) {
                if ($stay['reservationId'] === $personStay->getReservation()->getId()
                    && $stay['roomId'] === $personStay->getRoom()->getId()) {
                    if ($start >= $stay['start'] || $end <= $stay['end']
                        || ($start <= $stay['start'] && $end >= $stay['end'])) {
                        if ($start < $stay['start']) {
                            $stay['start'] = $start;
                        }
                        if ($end > $stay['end']) {
                            $stay['end'] = $end;
                        }
                        $createNew = false;
                    }
                }
            }
            if ($createNew && $personStay->getReservation()) {
                $output[] = [
                    'id'              => $id,
                    'reservationId'   => $personStay->getReservation()->getId(),
                    'title'           => $personStay->getRoom()->getName().'('.$personStay->getReservation()->getId().')',
                    'roomId'          => $personStay->getRoom()->getId(),
                    'start'           => $start,
                    'end'             => $end,
                    'backgroundColor' => $personStay->getRoom()->getColor() ?? '#404040',
                    'textColor'       => $personStay->getRoom()->isForegroundWhite() ? '#006FAD' : '#FF00FF',
                ];

                $id++;
            }
        }

        return $output;



    }

}
