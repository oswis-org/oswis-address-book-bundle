<?php

namespace Zakjakub\OswisAccommodationBundle\Api\Dto;

use ApiPlatform\Core\Annotation\ApiResource;

/**
 * @ApiResource(
 *  attributes={
 *      "access_control"="is_granted('ROLE_MANAGER')",
 *  },
 *  collectionOperations={
 *      "post"={
 *          "path"="/reservation_payment_action",
 *      },
 *  },
 *  itemOperations={},
 *  outputClass=false
 * )
 */
final class ReservationPaymentActionRequest
{

    /**
     * @var int[]|null
     */
    public $identifiers;

    /**
     * @var string|null
     * @Symfony\Component\Validator\Constraints\NotNull()
     * @Symfony\Component\Validator\Constraints\NotBlank()
     */
    public $type;

}
