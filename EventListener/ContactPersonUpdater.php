<?php

namespace OswisOrg\OswisAddressBookBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use OswisOrg\OswisAddressBookBundle\Entity\Position;
use OswisOrg\OswisAddressBookBundle\Repository\OrganizationRepository;
use OswisOrg\OswisAddressBookBundle\Repository\PositionRepository;

class ContactPersonUpdater
{
    protected PositionRepository $positionRepository;

    protected OrganizationRepository $organizationRepository;

    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em, PositionRepository $positionRepository, OrganizationRepository $organizationRepository)
    {
        $this->em = $em;
        $this->positionRepository = $positionRepository;
        $this->organizationRepository = $organizationRepository;
    }

    final public function __invoke(Position $position, LifecycleEventArgs $event): void
    {
        $isContactPerson = $position->isContactPerson();
        $organization = $position->getOrganization();
        $person = $position->getPerson();
        $id = $position->getId();
        $orgName = $organization?->getName();
        $personName = $person?->getName();
        /** @noinspection ForgottenDebugOutputInspection */
        error_log("Updating contact person. Position: $id, $orgName, $personName, $isContactPerson.");
        if ($organization && $isContactPerson) {
            $organization->addContactPerson($person);
        }
        if ($organization && !$isContactPerson) {
            $organization->removeContactPerson($person);
        }
    }
}
