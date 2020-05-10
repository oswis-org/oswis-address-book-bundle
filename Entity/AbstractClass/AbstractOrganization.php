<?php
/**
 * @noinspection PhpUnused
 */

namespace OswisOrg\OswisAddressBookBundle\Entity\AbstractClass;

use Doctrine\Common\Collections\Collection;
use OswisOrg\OswisAddressBookBundle\Traits\IdentificationNumberTrait;
use OswisOrg\OswisCoreBundle\Entity\NonPersistent\Nameable;
use OswisOrg\OswisCoreBundle\Entity\NonPersistent\Publicity;
use OswisOrg\OswisCoreBundle\Traits\Common\ColorTrait;
use OswisOrg\OswisCoreBundle\Traits\Common\NameableTrait;

abstract class AbstractOrganization extends AbstractContact
{
    public const ALLOWED_TYPES = [...self::ORGANIZATION_TYPES];

    use NameableTrait;
    use IdentificationNumberTrait;
    use ColorTrait;

    public function __construct(
        ?Nameable $nameable = null,
        ?string $identificationNumber = null,
        ?string $color = null,
        ?string $type = null,
        ?Collection $notes = null,
        ?Collection $contactDetails = null,
        ?Collection $addresses = null,
        ?Collection $addressBooks = null,
        ?Publicity $publicity = null
    ) {
        parent::__construct($nameable, $type, $notes, $contactDetails, $addresses, $addressBooks, null, $publicity);
        $this->setIdentificationNumber($identificationNumber);
        $this->setColor($color);
    }
}
