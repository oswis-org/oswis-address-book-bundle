<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 * @noinspection PhpUnused
 */

namespace OswisOrg\OswisAddressBookBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use OswisOrg\OswisAddressBookBundle\Entity\ContactDetailType;
use Psr\Log\LoggerInterface;

class ContactDetailTypeService
{
    protected EntityManagerInterface $em;

    protected ?LoggerInterface $logger;

    public function __construct(EntityManagerInterface $em, ?LoggerInterface $logger = null)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    public function create(ContactDetailType $contactDetailType): ?ContactDetailType
    {
        try {
            $this->em->persist($contactDetailType);
            $this->em->flush();
            $infoMessage = 'Created contact detail type: '.$contactDetailType->getId().' '.$contactDetailType->getName().'.';
            $this->logger ? $this->logger->info($infoMessage) : null;

            return $contactDetailType;
        } catch (Exception $e) {
            $this->logger ? $this->logger->info('ERROR: Contact detail type not created: '.$e->getMessage()) : null;

            return null;
        }
    }
}
