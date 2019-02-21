<?php

namespace Zakjakub\OswisAccommodationBundle\Traits\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Zakjakub\OswisAccommodationBundle\Entity\Room;
use Zakjakub\OswisAccommodationBundle\Entity\RoomRev;

trait RoomsFromRevisionsTrait
{

    /**
     * @param \DateTime|null $dateTime
     *
     * @return int
     */
    final public function getNumberOfRooms(?\DateTime $dateTime = null): int
    {
        return count($this->getRooms($dateTime));
    }

    /**
     * Managed rooms from rooms revisions.
     *
     * @param \DateTime|null $dateTime
     *
     * @return Collection
     */
    final public function getRooms(?\DateTime $dateTime = null): Collection
    {
        $rooms = new ArrayCollection();
        /** @noinspection PhpUndefinedMethodInspection */
        foreach ($this->getRoomRevisions() as $roomRevision) {
            try {
                \assert($roomRevision instanceof RoomRev);
                if (!$roomRevision->getContainer()) {
                    continue;
                }
                if ($roomRevision->getContainer()->getRevision($dateTime) === $roomRevision) {
                    $room = $roomRevision->getContainer();
                    \assert($room instanceof Room);
                    $rooms->add($room);
                }
            } catch (\Exception $e) {
            }
        }

        return $rooms;
    }
}
