<?php

namespace Zakjakub\OswisAddressBookBundle\Entity\AbstractClass;

use Zakjakub\OswisCoreBundle\Entity\Nameable;
use Zakjakub\OswisCoreBundle\Traits\Entity\ColorTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\IdentificationNumberTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\NameableBasicTrait;

abstract class AbstractOrganizationRevision extends AbstractContactRevision
{
    use NameableBasicTrait;
    use IdentificationNumberTrait;
    use ColorTrait;

    public function __construct(
        ?Nameable $nameable = null,
        ?string $identificationNumber = null,
        ?string $color = null
    ) {
        $this->setFieldsFromNameable($nameable);
        $this->setIdentificationNumber($identificationNumber);
        $this->setColor($color);
    }

    final public function getContactName(): string
    {
        return $this->getName();
    }

    final public function setContactName(?string $fullName): void
    {
        $this->setName($fullName);
    }
}
