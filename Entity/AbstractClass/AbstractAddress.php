<?php

namespace Zakjakub\OswisAddressBookBundle\Entity\AbstractClass;

use ApiPlatform\Core\Annotation\ApiResource;
use Zakjakub\OswisCoreBundle\Traits\Entity\AddressTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\BasicEntityTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\NameableBasicTrait;

abstract class AbstractAddress
{
    use BasicEntityTrait;
    use NameableBasicTrait;
    use AddressTrait;
}
