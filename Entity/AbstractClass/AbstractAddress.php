<?php

namespace Zakjakub\OswisAddressBookBundle\Entity\AbstractClass;

use Zakjakub\OswisCoreBundle\Traits\Entity\AddressTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\NameableBasicTrait;

abstract class AbstractAddress
{
    use NameableBasicTrait;
    use AddressTrait;
}
