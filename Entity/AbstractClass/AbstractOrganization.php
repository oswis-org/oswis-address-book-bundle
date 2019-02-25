<?php

namespace Zakjakub\OswisAddressBookBundle\Entity\AbstractClass;

use Zakjakub\OswisCoreBundle\Traits\Entity\IdentificationNumberContainerTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\NameableBasicContainerTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\UrlContainerTrait;

abstract class AbstractOrganization extends AbstractContact
{

    use NameableBasicContainerTrait;
    use IdentificationNumberContainerTrait;
    use UrlContainerTrait;

    final public function getContactName(): string
    {
        return $this->getName();
    }

    /**
     * @param string|null $dummy
     *
     * @throws \Zakjakub\OswisCoreBundle\Exceptions\RevisionMissingException
     */
    final public function setContactName(?string $dummy): void
    {
        $this->setName($dummy);
    }

}
