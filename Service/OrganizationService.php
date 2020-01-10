<?php
/**
 * @noinspection PhpUnused
 */

namespace Zakjakub\OswisAddressBookBundle\Service;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Zakjakub\OswisAddressBookBundle\Entity\Organization;
use Zakjakub\OswisCoreBundle\Entity\Nameable;

class OrganizationService
{
    protected EntityManagerInterface $em;

    protected ?LoggerInterface $logger;

    public function __construct(EntityManagerInterface $em, ?LoggerInterface $logger = null)
    {
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
    ): ?Organization {
        try {
            $entity = new Organization($nameable, $identificationNumber, $parentOrganization, $color, $type, $addresses, $contactDetails, $notes);
            $this->em->persist($entity);
            $this->em->flush();
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
        foreach ($this->em->getRepository(Organization::class)->findAll() as $organization) {
            assert($organization instanceof Organization);
            $organization->destroyRevisions();
            $this->em->persist($organization);
        }
        $this->em->flush();
    }
}
