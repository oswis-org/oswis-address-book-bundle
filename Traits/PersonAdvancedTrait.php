<?php

namespace OswisOrg\OswisAddressBookBundle\Traits;

use OswisOrg\OswisCoreBundle\Traits\AddressBook\EmailTrait;
use OswisOrg\OswisCoreBundle\Traits\AddressBook\PersonTrait;
use OswisOrg\OswisCoreBundle\Traits\AddressBook\PhoneTrait;
use OswisOrg\OswisCoreBundle\Traits\AddressBook\PostalAddressTrait;
use OswisOrg\OswisCoreBundle\Traits\AddressBook\UrlTrait;

trait PersonAdvancedTrait
{
    use PersonTrait;
    use EmailTrait;
    use PhoneTrait;
    use UrlTrait;
    use PostalAddressTrait;
    use BirthDateTrait;
}
