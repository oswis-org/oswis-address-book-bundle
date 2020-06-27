<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 * @noinspection PhpUnused
 */

namespace OswisOrg\OswisAddressBookBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use OswisOrg\OswisAddressBookBundle\Entity\ContactDetailType;
use OswisOrg\OswisAddressBookBundle\Repository\ContactDetailTypeRepository;
use Psr\Log\LoggerInterface;

class ContactDetailTypeService
{
    protected EntityManagerInterface $em;

    protected LoggerInterface $logger;

    protected ContactDetailTypeRepository $contactDetailTypeRepository;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger, ContactDetailTypeRepository $contactDetailTypeRepository)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->contactDetailTypeRepository = $contactDetailTypeRepository;
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

    public function getBySlug(string $slug): ?ContactDetailType {
        return $this->contactDetailTypeRepository->findOneBy(['slug' => $slug]);
    }
}
