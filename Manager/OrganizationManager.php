<?php

namespace Zakjakub\OswisAddressBookBundle\Manager;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Zakjakub\OswisAddressBookBundle\Entity\Organization;
use Zakjakub\OswisCoreBundle\Entity\Nameable;

class OrganizationManager
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var LoggerInterface|null
     */
    protected $logger;

    public function __construct(
        EntityManagerInterface $em,
        ?LoggerInterface $logger = null
    ) {
        $this->em = $em;
        $this->logger = $logger;
    }

    final public function create(
        ?Nameable $nameable = null,
        ?string $identificationNumber = null,
        ?Organization $parentOrganization = null,
        ?string $color = null,
        ?string $type = null,
        ?Collection $addresses = null,
        ?Collection $contactDetails = null,
        ?Collection $notes = null
    ): Organization {
        try {
            $em = $this->em;
            $entity = new Organization(
                $nameable,
                $identificationNumber,
                $parentOrganization,
                $color,
                $type,
                $addresses,
                $contactDetails,
                $notes
            );
            $em->persist($entity);
            $em->flush();
            $infoMessage = 'Created organization: '.$entity->getId().' '.$entity->getName().'.';
            $this->logger ? $this->logger->info($infoMessage) : null;

            return $entity;
        } catch (Exception $e) {
            $this->logger ? $this->logger->info('ERROR: Organization not created: '.$e->getMessage()) : null;

            return null;
        }
    }

    final public function updateActiveRevisions(): void
    {
        $organizations = $this->em->getRepository(Organization::class)->findAll();
        foreach ($organizations as $organization) {
            assert($organization instanceof Organization);
            $organization->updateActiveRevision();
        }
        $this->em->flush();
    }

}
