<?php

namespace Zakjakub\OswisAddressBookBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
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
     * @var LoggerInterface
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
        ?string $url = null,
        ?string $email = null,
        ?Organization $parentOrganization = null,
        ?string $color = null
    ): Organization {
        try {
            $em = $this->em;
            $entity = new Organization(
                $nameable,
                $identificationNumber,
                $url,
                $email,
                $parentOrganization,
                $color
            );
            $em->persist($entity);
            $em->flush();
            $infoMessage = 'Created organization: '.$entity->getId().' '.$entity->getName().'.';
            $this->logger ? $this->logger->info($infoMessage) : null;

            return $entity;
        } catch (\Exception $e) {
            $this->logger ? $this->logger->info('ERROR: Organization not created: '.$e->getMessage()) : null;

            return null;
        }
    }

}
