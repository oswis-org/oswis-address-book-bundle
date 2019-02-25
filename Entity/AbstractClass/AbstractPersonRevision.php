<?php

namespace Zakjakub\OswisAddressBookBundle\Entity\AbstractClass;

use Zakjakub\OswisCoreBundle\Entity\Address;
use Zakjakub\OswisCoreBundle\Interfaces\PersonInterface;
use Zakjakub\OswisCoreBundle\Traits\Entity\PersonAdvancedTrait;

abstract class AbstractPersonRevision extends AbstractContactRevision implements PersonInterface
{

    use PersonAdvancedTrait;

    public function __construct(
        ?Address $address = null,
        ?string $fullName = null,
        ?string $email = null,
        ?string $phone = null,
        ?string $description = null,
        ?\DateTime $birthDate = null,
        ?string $note = null,
        ?string $url = null
    ) {
        $this->setFieldsFromAddress($address);
        $this->setFullName($fullName);
        $this->setEmail($email);
        $this->setPhone($phone);
        $this->setDescription($description);
        $this->setBirthDate($birthDate);
        $this->setNote($note);
        $this->setUrl($url);
    }

    final public function getContactName(): string
    {
        // TODO: Implement getContactName() method.
        return '';
    }

    final public function setContactName(?string $dummy): void
    {
        // TODO: Implement setContactName() method.
    }

}
