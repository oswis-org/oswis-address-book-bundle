<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 * @noinspection PhpUnused
 */

namespace OswisOrg\OswisAddressBookBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use OswisOrg\OswisAddressBookBundle\Entity\Organization;
use OswisOrg\OswisAddressBookBundle\Repository\OrganizationRepository;
use Psr\Log\LoggerInterface;

class OrganizationService
{
    protected EntityManagerInterface $em;

    protected ?LoggerInterface $logger;

    public function __construct(EntityManagerInterface $em, ?LoggerInterface $logger = null)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    public function create(Organization $organization): ?Organization
    {
        try {
            $this->em->persist($organization);
            $this->em->flush();
            $infoMessage = 'Created organization: '.$organization->getId().' '.$organization->getName().'.';
            $this->logger ? $this->logger->info($infoMessage) : null;

            return $organization;
        } catch (Exception $e) {
            $this->logger ? $this->logger->info('ERROR: Organization not created: '.$e->getMessage()) : null;

            return null;
        }
    }

    public function getRepository(): OrganizationRepository
    {
        $repo = $this->em->getRepository(Organization::class);
        assert($repo instanceof OrganizationRepository);

        return $repo;
    }

    public function updateActiveRevisions(): void
    {
    }
}
