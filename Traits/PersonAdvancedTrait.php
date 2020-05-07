<?php

namespace OswisOrg\OswisAddressBookBundle\Traits;

use OswisOrg\OswisCoreBundle\Traits\AddressBook\BirthDateTrait;

trait PersonAdvancedTrait
{
    use PersonBasicTrait;
    use EmailTrait;
    use PhoneTrait;
    use UrlTrait;
    use AddressTrait;
    use BirthDateTrait;
}
