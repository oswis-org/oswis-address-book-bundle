<?php

namespace Zakjakub\OswisAddressBookBundle\Entity\AbstractClass;

use Zakjakub\OswisCoreBundle\Interfaces\PersonInterface;
use Zakjakub\OswisCoreBundle\Traits\Entity\PersonAdvancedTrait;

abstract class AbstractPerson extends AbstractContact implements PersonInterface
{

    use PersonAdvancedTrait;

}
