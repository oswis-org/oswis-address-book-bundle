<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use OswisOrg\OswisAddressBookBundle\Entity\AddressBook\AddressBook;
use OswisOrg\OswisAddressBookBundle\Repository\AddressBookRepository;
use OswisOrg\OswisCoreBundle\Entity\NonPersistent\Nameable;
use Psr\Log\LoggerInterface;

class AddressBookService
{
    protected EntityManagerInterface $em;

    protected LoggerInterface $logger;

    protected AddressBookRepository $addressBookRepository;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger, AddressBookRepository $addressBookRepository)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->addressBookRepository = $addressBookRepository;
    }

    public function create(?Nameable $nameable = null): ?AddressBook
    {
        try {
            $entity = new AddressBook($nameable);
            $this->em->persist($entity);
            $this->em->flush();
            $infoMessage = 'Created address book (by manager): '.$entity->getId().', '.$entity->getName().'.';
            $this->logger->info($infoMessage);

            return $entity;
        } catch (Exception $e) {
            $this->logger->info('ERROR: Address book not created (by manager): '.$e->getMessage());

            return null;
        }
    }

    public function getRepository(): AddressBookRepository
    {
        return $this->addressBookRepository;
    }

    public function updateActiveRevisions(): void
    {
    }
}
