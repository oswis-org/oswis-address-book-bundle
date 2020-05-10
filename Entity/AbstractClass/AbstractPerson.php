<?php
/**
 * @noinspection PhpUnused
 */

namespace OswisOrg\OswisAddressBookBundle\Entity\AbstractClass;

use DateTime;
use Doctrine\Common\Collections\Collection;
use OswisOrg\OswisAddressBookBundle\Traits\BirthDateTrait;
use OswisOrg\OswisCoreBundle\Entity\NonPersistent\Nameable;
use OswisOrg\OswisCoreBundle\Entity\NonPersistent\Publicity;
use OswisOrg\OswisCoreBundle\Interfaces\AddressBook\PersonInterface;
use OswisOrg\OswisCoreBundle\Traits\AddressBook\PersonTrait;

abstract class AbstractPerson extends AbstractContact implements PersonInterface
{
    public const ALLOWED_TYPES = [...self::PERSON_TYPES];

    use PersonTrait;
    use BirthDateTrait;

    public function __construct(
        ?Nameable $nameable = null,
        ?string $description = null,
        ?DateTime $birthDate = null,
        ?string $type = null,
        ?Collection $notes = null,
        ?Collection $contactDetails = null,
        ?Collection $addresses = null,
        ?Collection $addressBooks = null,
        ?Collection $positions = null,
        ?Publicity $publicity = null
    ) {
        parent::__construct($nameable, $type, $notes, $contactDetails, $addresses, $addressBooks, $positions, $publicity);
        $this->setDescription($description);
        $this->setBirthDate($birthDate);
    }
}
