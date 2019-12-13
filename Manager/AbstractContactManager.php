<?php /** @noinspection PhpUnused */

namespace Zakjakub\OswisAddressBookBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Zakjakub\OswisAddressBookBundle\Entity\AbstractClass\AbstractContact;
use function assert;

class AbstractContactManager
{
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
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
