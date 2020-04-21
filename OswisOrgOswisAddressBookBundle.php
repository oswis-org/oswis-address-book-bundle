<?php

namespace OswisOrg\OswisAddressBookBundle;

use OswisOrg\OswisAddressBookBundle\DependencyInjection\OswisOrgOswisAddressBookExtension;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OswisOrgOswisAddressBookBundle extends Bundle
{
    final public function getContainerExtension(): Extension
    {
        return new OswisOrgOswisAddressBookExtension();
    }
}
