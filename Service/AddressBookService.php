<?php /** @noinspection PhpUnused */

namespace Zakjakub\OswisAddressBookBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Zakjakub\OswisAddressBookBundle\Entity\AddressBook\AddressBook;
use Zakjakub\OswisCoreBundle\Entity\Nameable;

class AddressBookService
{
    protected EntityManagerInterface $em;

    protected ?LoggerInterface $logger;

    public function __construct(EntityManagerInterface $em, ?LoggerInterface $logger = null)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    final public function create(?Nameable $nameable = null): ?AddressBook
    {
        try {
            $entity = new AddressBook($nameable);
            $this->em->persist($entity);
            $this->em->flush();
            $infoMessage = 'Created address book (by manager): '.$entity->getId().', '.$entity->getName().'.';
            $this->logger ? $this->logger->info($infoMessage) : null;

            return $entity;
        } catch (Exception $e) {
            $this->logger ? $this->logger->info('ERROR: Address book not created (by manager): '.$e->getMessage()) : null;

            return null;
        }
    }

    final public function updateActiveRevisions(): void
    {
    }
}
