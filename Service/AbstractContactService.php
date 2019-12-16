<?php /** @noinspection PhpUnused */

namespace Zakjakub\OswisAddressBookBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Zakjakub\OswisAddressBookBundle\Entity\AbstractClass\AbstractContact;
use function assert;

class AbstractContactService
{
    protected EntityManagerInterface $em;

    protected LoggerInterface $logger;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    final public function updateNames(): void
    {
        $contacts = $this->em->getRepository(AbstractContact::class)->findAll();
        foreach ($contacts as $contact) {
            assert($contact instanceof AbstractContact);
            $contact->updateContactName();
            $this->em->persist($contact);
        }
        $this->em->flush();
    }
}
