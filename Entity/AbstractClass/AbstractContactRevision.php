<?php

namespace Zakjakub\OswisAddressBookBundle\Entity\AbstractClass;

use Zakjakub\OswisCoreBundle\Entity\AbstractClass\AbstractRevision;
use Zakjakub\OswisCoreBundle\Traits\Entity\BasicEntityTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\InternalNoteTrait;

abstract class AbstractContactRevision extends AbstractRevision
{

    use BasicEntityTrait;
    use InternalNoteTrait;

    /**
     * @inheritdoc
     *
     * @return string
     */
    final public function __toString(): string
    {
        return $this->getContactName();
    }

    abstract public function getContactName(): string;

    abstract public function setContactName(?string $dummy): void;

}
