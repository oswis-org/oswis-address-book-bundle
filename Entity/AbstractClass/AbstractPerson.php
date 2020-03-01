<?php
/**
 * @noinspection PhpUnused
 * @noinspection MethodShouldBeFinalInspection
 */

namespace Zakjakub\OswisAddressBookBundle\Entity\AbstractClass;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Exception;
use Zakjakub\OswisCoreBundle\Entity\Publicity;
use Zakjakub\OswisCoreBundle\Traits\Entity\PersonBasicTrait;

abstract class AbstractPerson extends AbstractContact
{
    public const ALLOWED_TYPES = ['person'];

    use PersonBasicTrait;

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
