<?php

namespace Zakjakub\OswisAddressBookBundle;

use Symfony\Component\DependencyInjection\Extension\Extension;
use ZakJakub\OswisAddressBookBundle\DependencyInjection\ZakjakubOswisAddressBookExtension;

class ZakjakubOswisAddressBookBundle extends Bundle
{
    final public function getContainerExtension(): Extension
    {
        return new ZakjakubOswisAddressBookExtension();
    }
}