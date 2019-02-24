<?php

namespace Zakjakub\OswisAddressBookBundle\Entity\AbstractClass;

use Zakjakub\OswisCoreBundle\Traits\Entity\IdentificationNumberTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\NameableBasicTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\UrlTrait;

abstract class AbstractOrganizationRevision extends AbstractContactRevision
{

    use NameableBasicTrait;
    use IdentificationNumberTrait;
    use UrlTrait;


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
