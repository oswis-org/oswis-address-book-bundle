<?php

namespace Zakjakub\OswisAccommodationBundle\Api\Dto;

use ApiPlatform\Core\Annotation\ApiResource;

/**
 * @ApiResource(
 *  collectionOperations={
 *      "post"={
 *          "path"="/rooms_occupancy_action",
 *      },
 *  },
 *  itemOperations={},
 *  outputClass=false
 * )
 */
final class RoomsOccupancyActionRequest
{

    /**
     * @var \DateTime|null
     */
    public $startDateTime;

    /**
     * @var \DateTime|null
     */
    public $endDateTime;

}
