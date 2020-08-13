<?php

namespace OswisOrg\OswisAddressBookBundle\Entity\AbstractClass;

use Doctrine\Common\Collections\Collection;
use OswisOrg\OswisAddressBookBundle\Traits\BirthDateTrait;
use OswisOrg\OswisCoreBundle\Entity\NonPersistent\Nameable;
use OswisOrg\OswisCoreBundle\Interfaces\AddressBook\PersonInterface;
use OswisOrg\OswisCoreBundle\Traits\AddressBook\PersonTrait;

abstract class AbstractPerson extends AbstractContact implements PersonInterface
{
    public const ALLOWED_TYPES = [...self::PERSON_TYPES];

    use PersonTrait;
    use BirthDateTrait;

    public function __construct(
        ?Nameable $nameable = null,
        ?Collection $notes = null,
        ?Collection $details = null,
        ?Collection $addresses = null,
        ?Collection $addressBooks = null
    ) {
        parent::__construct($nameable, self::TYPE_PERSON, $notes, $details, $addresses, $addressBooks);
    }

    public static function getAllowedTypesDefault(): array
    {
        return self::ALLOWED_TYPES;
    }
}
