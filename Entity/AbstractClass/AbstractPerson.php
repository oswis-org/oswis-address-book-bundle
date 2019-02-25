<?php

namespace Zakjakub\OswisAddressBookBundle\Entity\AbstractClass;

use Zakjakub\OswisCoreBundle\Traits\Entity\PersonAdvancedContainerTrait;

abstract class AbstractPerson extends AbstractContact
{

    public const ALLOWED_TYPES = ['person'];

    use PersonAdvancedContainerTrait;

    final public function getContactName(): string
    {
        // TODO: Implement getContactName() method.
        return '';
    }

    final public function setContactName(?string $dummy): void
    {
        // TODO: Implement setContactName() method.
    }


    final public function checkType(?string $typeName): bool
    {
        if (\in_array($typeName, self::ALLOWED_TYPES, true)) {
            return true;
        }
        throw new \InvalidArgumentException('Typ organizace "'.$typeName.'" není povolen.');
    }

}
