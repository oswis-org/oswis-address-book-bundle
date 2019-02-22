<?php

namespace Zakjakub\OswisAddressBookBundle\Entity\AbstractClass;

abstract class AbstractOrganization extends AbstractContact
{


    final public function getContactName(): string
    {
        // TODO: Implement getContactName() method.
        return '';
    }

    final public function setContactName(?string $dummy): void
    {
        // TODO: Implement setContactName() method.
    }

}
