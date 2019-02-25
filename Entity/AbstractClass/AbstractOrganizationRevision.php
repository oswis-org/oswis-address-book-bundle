<?php

namespace Zakjakub\OswisAddressBookBundle\Entity\AbstractClass;

use Zakjakub\OswisCoreBundle\Entity\Nameable;
use Zakjakub\OswisCoreBundle\Traits\Entity\ColorTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\EmailTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\IdentificationNumberTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\NameableBasicTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\UrlTrait;

abstract class AbstractOrganizationRevision extends AbstractContactRevision
{

    use NameableBasicTrait;
    use IdentificationNumberTrait;
    use UrlTrait;
    use EmailTrait;
    use ColorTrait;

    public function __construct(
        ?Nameable $nameable = null,
        ?string $identificationNumber = null,
        ?string $url = null
    ) {
        $this->setFieldsFromNameable($nameable);
        $this->setIdentificationNumber($identificationNumber);
        $this->setUrl($url);
    }

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
