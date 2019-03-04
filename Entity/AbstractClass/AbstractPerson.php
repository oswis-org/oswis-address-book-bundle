<?php

namespace Zakjakub\OswisAddressBookBundle\Entity\AbstractClass;

use Zakjakub\OswisCoreBundle\Traits\Entity\PersonBasicContainerTrait;

abstract class AbstractPerson extends AbstractContact
{
    public const ALLOWED_TYPES = ['person'];

    use PersonBasicContainerTrait;

    final public function getContactName(): string
    {
        return $this->getFullName();
    }

    final public function setContactName(?string $name): void
    {
        $this->setFullName($name);
    }

    /**
     * @param string|null $typeName
     *
     * @return bool
     * @throws \InvalidArgumentException
     */
    final public function checkType(?string $typeName): bool
    {
        if (\in_array($typeName, self::ALLOWED_TYPES, true)) {
            return true;
        }
        throw new \InvalidArgumentException('Typ organizace "'.$typeName.'" není povolen.');
    }
}
