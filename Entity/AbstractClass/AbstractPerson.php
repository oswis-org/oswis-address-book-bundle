<?php

namespace Zakjakub\OswisAddressBookBundle\Entity\AbstractClass;

use Zakjakub\OswisCoreBundle\Traits\Entity\PersonBasicContainerTrait;

abstract class AbstractPerson extends AbstractContact
{
    public const ALLOWED_TYPES = ['person'];

    use PersonBasicContainerTrait;
}
