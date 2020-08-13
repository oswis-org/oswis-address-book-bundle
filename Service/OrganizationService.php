<?php
/**
 * @noinspection MethodShouldBeFinalInspection
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

    protected LoggerInterface $logger;

    protected OrganizationRepository $organizationRepository;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger, OrganizationRepository $organizationRepository)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->organizationRepository = $organizationRepository;
    }

    public function create(Organization $organization): ?Organization
    {
        try {
            $this->em->persist($organization);
            $this->em->flush();
            $infoMessage = 'Created organization: '.$organization->getId().' '.$organization->getName().'.';
            $this->logger->info($infoMessage);

            return $organization;
        } catch (Exception $e) {
            $this->logger->info('ERROR: Organization not created: '.$e->getMessage());

            return null;
        }
    }

    public function getRepository(): OrganizationRepository
    {
        return $this->organizationRepository;
    }

    public function updateActiveRevisions(): void
    {
    }
}
