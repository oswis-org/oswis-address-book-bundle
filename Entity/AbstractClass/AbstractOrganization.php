<?php

namespace OswisOrg\OswisAddressBookBundle\Entity\AbstractClass;

use Doctrine\Common\Collections\Collection;
use OswisOrg\OswisAddressBookBundle\Traits\IdentificationNumberTrait;
use OswisOrg\OswisCoreBundle\Entity\NonPersistent\Nameable;
use OswisOrg\OswisCoreBundle\Traits\Common\NameableTrait;

abstract class AbstractOrganization extends AbstractContact
{
    public const ALLOWED_TYPES = [...self::ORGANIZATION_TYPES];

    use NameableTrait;
    use IdentificationNumberTrait;

    public function __construct(
        ?Nameable $nameable = null,
        ?string $identificationNumber = null,
        ?string $type = null,
        ?Collection $notes = null,
        ?Collection $contactDetails = null,
        ?Collection $addresses = null,
        ?Collection $addressBooks = null
    ) {
        parent::__construct($nameable, $type, $notes, $contactDetails, $addresses, $addressBooks);
        $this->setIdentificationNumber($identificationNumber);
    }
}
