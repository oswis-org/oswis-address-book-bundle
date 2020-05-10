<?php

namespace OswisOrg\OswisAddressBookBundle\Entity\AbstractClass;

use OswisOrg\OswisCoreBundle\Interfaces\BasicInterface;
use OswisOrg\OswisCoreBundle\Interfaces\Common\NameableInterface;
use OswisOrg\OswisCoreBundle\Traits\AddressBook\PostalAddressTrait;
use OswisOrg\OswisCoreBundle\Traits\Common\NameableTrait;

abstract class AbstractAddress implements NameableInterface
{
    use NameableTrait;
    use PostalAddressTrait;
}
