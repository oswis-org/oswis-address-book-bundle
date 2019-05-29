<?php

namespace Zakjakub\OswisAddressBookBundle\Entity\AbstractClass;

use DateTime;
use Exception;
use Zakjakub\OswisCoreBundle\Interfaces\PersonInterface;
use Zakjakub\OswisCoreBundle\Traits\Entity\PersonBasicTrait;

abstract class AbstractPersonRevision extends AbstractContactRevision implements PersonInterface
{
    use PersonBasicTrait;

    /**
     * AbstractPersonRevision constructor.
     *
     * @param string|null   $fullName
     * @param string|null   $description
     * @param DateTime|null $birthDate
     *
     * @throws Exception
     */
    public function __construct(
        ?string $fullName = null,
        ?string $description = null,
        ?DateTime $birthDate = null
    ) {
        $this->setFullName($fullName);
        $this->setDescription($description);
        $this->setBirthDate($birthDate);
    }

    final public function getContactName(): string
    {
        return $this->getFullName();
    }

    final public function setContactName(?string $fullName): void
    {
        $this->setFullName($fullName);
    }
}
