<?php

namespace Zakjakub\OswisAddressBookBundle\Entity\AbstractClass;

use Zakjakub\OswisCoreBundle\Interfaces\PersonInterface;
use Zakjakub\OswisCoreBundle\Traits\Entity\PersonAdvancedTrait;

abstract class AbstractPersonRevision extends AbstractContactRevision implements PersonInterface
{

    use PersonAdvancedTrait;

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
