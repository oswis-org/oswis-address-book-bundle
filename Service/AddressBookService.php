<?php /** @noinspection MethodShouldBeFinalInspection */

/** @noinspection PhpUnused */

namespace Zakjakub\OswisAddressBookBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Zakjakub\OswisAddressBookBundle\Entity\AddressBook\AddressBook;
use Zakjakub\OswisAddressBookBundle\Repository\AddressBookRepository;
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

    public function create(?Nameable $nameable = null): ?AddressBook
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

    public function getRepository(): AddressBookRepository
    {
        $repo = $this->em->getRepository(AddressBook::class);
        assert($repo instanceof AddressBookRepository);

        return $repo;
    }

    public function updateActiveRevisions(): void
    {
    }
}
