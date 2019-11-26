<?php

namespace Zakjakub\OswisAddressBookBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Zakjakub\OswisAddressBookBundle\Entity\ContactDetailType;
use Zakjakub\OswisCoreBundle\Entity\Nameable;

class ContactDetailTypeManager
{
    /**
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $em;

    /**
     * @var LoggerInterface|null
     */
    protected ?LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $em,
        ?LoggerInterface $logger = null
    ) {
        $this->em = $em;
        $this->logger = $logger;
    }

    final public function create(
        ?Nameable $nameable = null,
        ?string $schema = null,
        ?bool $showInPreview = null,
        ?string $type = null,
        ?string $formLabel = null,
        ?string $formHelp = null
    ): ContactDetailType {
        try {
            $em = $this->em;
            $entity = new ContactDetailType(
                $nameable, $schema, $showInPreview, $type, $formLabel, $formHelp
            );
            $em->persist($entity);
            $em->flush();
            $infoMessage = 'Created contact detail type: '.$entity->getId().' '.$entity->getName().'.';
            $this->logger ? $this->logger->info($infoMessage) : null;

            return $entity;
        } catch (Exception $e) {
            $this->logger ? $this->logger->info('ERROR: Contact detail type not created: '.$e->getMessage()) : null;

            return null;
        }
    }
}
