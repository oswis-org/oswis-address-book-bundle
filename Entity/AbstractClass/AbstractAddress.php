<?php

namespace OswisOrg\OswisAddressBookBundle\Entity\AbstractClass;

use OswisOrg\OswisCoreBundle\Interfaces\BasicEntityInterface;
use OswisOrg\OswisCoreBundle\Traits\Entity\AddressTrait;
use OswisOrg\OswisCoreBundle\Traits\Entity\BasicEntityTrait;
use OswisOrg\OswisCoreBundle\Traits\Entity\NameableBasicTrait;

abstract class AbstractAddress implements BasicEntityInterface
{
    use BasicEntityTrait;
    use NameableBasicTrait;
    use AddressTrait;
}
