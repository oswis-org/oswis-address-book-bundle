<?php
/**
 * @noinspection PhpUnused
 */

namespace Zakjakub\OswisAddressBookBundle\Entity\AbstractClass;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Exception;
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
        ?Collection $addressBooks = null
    ) {
        parent::__construct($type, $notes, $contactDetails, $addresses, $addressBooks);
        $this->setFullName($fullName);
        $this->setDescription($description);
        try {
            $this->setBirthDate($birthDate);
        } catch (Exception $e) {
        }
    }

    final public function destroyRevisions(): void
    {
    }

}
