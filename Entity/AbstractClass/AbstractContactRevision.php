<?php

namespace Zakjakub\OswisAddressBookBundle\Entity\AbstractClass;

use Zakjakub\OswisAddressBookBundle\Entity\ContactImage;
use Zakjakub\OswisCoreBundle\Entity\AbstractRevision;
use Zakjakub\OswisCoreBundle\Traits\Entity\BasicEntityTrait;

abstract class AbstractContactRevision extends AbstractRevision
{

    use BasicEntityTrait;

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
