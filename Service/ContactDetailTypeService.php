<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 * @noinspection PhpUnused
 */

namespace OswisOrg\OswisAddressBookBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use OswisOrg\OswisAddressBookBundle\Entity\ContactDetailCategory;
use OswisOrg\OswisAddressBookBundle\Repository\ContactDetailCategoryRepository;
use Psr\Log\LoggerInterface;

class ContactDetailTypeService
{
    protected EntityManagerInterface $em;

    protected LoggerInterface $logger;

    protected ContactDetailCategoryRepository $contactDetailTypeRepository;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger, ContactDetailCategoryRepository $contactDetailTypeRepository)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->contactDetailTypeRepository = $contactDetailTypeRepository;
    }

    public function create(ContactDetailCategory $contactDetailType): ?ContactDetailCategory
    {
        try {
            $this->em->persist($contactDetailType);
            $this->em->flush();
            $infoMessage = 'Created contact detail type: '.$contactDetailType->getId().' '.$contactDetailType->getName().'.';
            $this->logger->info($infoMessage);

            return $contactDetailType;
        } catch (Exception $e) {
            $this->logger->info('ERROR: Contact detail type not created: '.$e->getMessage());

            return null;
        }
    }

    public function getBySlug(string $slug): ?ContactDetailCategory
    {
        return $this->contactDetailTypeRepository->findOneBy(['slug' => $slug]);
    }
}
