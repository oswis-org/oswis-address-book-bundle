<?php
/**
 * @noinspection PhpUnused
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Entity\AbstractClass;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Exception;
use OswisOrg\OswisAddressBookBundle\Traits\BirthDateTrait;
use OswisOrg\OswisCoreBundle\Entity\NonPersistent\Publicity;
use OswisOrg\OswisCoreBundle\Interfaces\AddressBook\PersonInterface;
use OswisOrg\OswisCoreBundle\Traits\AddressBook\PersonBasicTrait;

abstract class AbstractPerson extends AbstractContact implements PersonInterface
{
    public const ALLOWED_TYPES = ['person'];

    use PersonBasicTrait;
    use BirthDateTrait;

    public function __construct(
        ?string $fullName = null,
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
        parent::__construct($type, $notes, $contactDetails, $addresses, $addressBooks, $positions, $publicity);
        $this->setFullName($fullName);
        $this->setDescription($description);
        try {
            $this->setBirthDate($birthDate);
        } catch (Exception $e) {
        }
    }

    public function destroyRevisions(): void
    {
    }
}
