<?php

namespace OswisOrg\OswisAddressBookBundle\Traits;

use OswisOrg\OswisCoreBundle\Traits\AddressBook\BirthDateTrait;

trait PersonAdvancedTrait
{
    use PersonTrait;
    use EmailTrait;
    use PhoneTrait;
    use UrlTrait;
    use PostalAddressTrait;
    use BirthDateTrait;
}
