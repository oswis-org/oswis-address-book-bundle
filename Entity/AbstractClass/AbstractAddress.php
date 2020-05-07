<?php

namespace OswisOrg\OswisAddressBookBundle\Entity\AbstractClass;

use OswisOrg\OswisCoreBundle\Interfaces\BasicEntityInterface;
use OswisOrg\OswisCoreBundle\Interfaces\Common\NameableEntityInterface;
use OswisOrg\OswisCoreBundle\Traits\AddressBook\AddressTrait;
use OswisOrg\OswisCoreBundle\Traits\Common\NameableBasicTrait;

abstract class AbstractAddress implements NameableEntityInterface
{
    use NameableBasicTrait;
    use AddressTrait;
}
