<?php /** @noinspection PhpUnused */

namespace Zakjakub\OswisAddressBookBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Zakjakub\OswisAddressBookBundle\Entity\Person;

class PersonManager
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    public function __construct(
        EntityManagerInterface $em
    ) {
        $this->em = $em;
    }

    final public function updateActiveRevisions(): void
    {
        $people = $this->em->getRepository(Person::class)->findAll();
        foreach ($people as $person) {
            assert($person instanceof Person);
            // $person->updateActiveRevision();
            $person->destroyRevisions();
            $this->em->persist($person);
        }
        $this->em->flush();
    }
}
