<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 * @noinspection PhpUnused
 */

namespace OswisOrg\OswisAddressBookBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use OswisOrg\OswisAddressBookBundle\Entity\ContactDetailType;
use OswisOrg\OswisCoreBundle\Entity\NonPersistent\Nameable;
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

    public function create(
        ?Nameable $nameable = null,
        ?string $schema = null,
        ?bool $showInPreview = null,
        ?string $type = null,
        ?string $formLabel = null,
        ?string $formHelp = null
    ): ?ContactDetailType {
        try {
            $entity = new ContactDetailType($nameable, $schema, $showInPreview, $type, $formLabel, $formHelp);
            $this->em->persist($entity);
            $this->em->flush();
            $infoMessage = 'Created contact detail type: '.$entity->getId().' '.$entity->getName().'.';
            $this->logger ? $this->logger->info($infoMessage) : null;

            return $entity;
        } catch (Exception $e) {
            $this->logger ? $this->logger->info('ERROR: Contact detail type not created: '.$e->getMessage()) : null;

            return null;
        }
    }
}
