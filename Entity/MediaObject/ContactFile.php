<?php

namespace OswisOrg\OswisAddressBookBundle\Entity\MediaObject;

use OswisOrg\OswisCoreBundle\Entity\AbstractClass\AbstractFile;
use OswisOrg\OswisCoreBundle\Entity\NonPersistent\Publicity;
use OswisOrg\OswisCoreBundle\Exceptions\InvalidTypeException;
use OswisOrg\OswisCoreBundle\Traits\Common\BasicTrait;
use OswisOrg\OswisCoreBundle\Traits\Common\EntityPublicTrait;
use OswisOrg\OswisCoreBundle\Traits\Common\PriorityTrait;
use OswisOrg\OswisCoreBundle\Traits\Common\TypeTrait;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @Doctrine\ORM\Mapping\Entity()
 * @Doctrine\ORM\Mapping\Table(name="address_book_contact_file")
 * @ApiPlatform\Core\Annotation\ApiResource(
 *   collectionOperations={
 *     "get",
 *     "post"={
 *         "method"="POST",
 *         "path"="/address_book_contact_file",
 *         "controller"=OswisOrg\OswisAddressBookBundle\Controller\MediaObject\ContactFileAction::class,
 *         "defaults"={"_api_receive"=false},
 *     },
 *   }
 * )
 * @Vich\UploaderBundle\Mapping\Annotation\Uploadable()
 * @Doctrine\ORM\Mapping\Cache(usage="NONSTRICT_READ_WRITE", region="address_book_contact_file")
 */
class ContactFile extends AbstractFile
{
    use BasicTrait;
    use TypeTrait;
    use PriorityTrait;
    use EntityPublicTrait;

    /**
     * @Symfony\Component\Validator\Constraints\NotNull()
     * @Vich\UploaderBundle\Mapping\Annotation\UploadableField(
     *     mapping="address_book_contact_file",
     *     fileNameProperty="contentName",
     *     mimeType="contentMimeType"
     * )
     */
    public ?File $file = null;

    /**
     * @param File|null      $file
     * @param string|null    $type
     * @param int|null       $priority
     * @param Publicity|null $publicity
     *
     * @throws InvalidTypeException
     */
    public function __construct(?File $file = null, ?string $type = null, ?int $priority = null, ?Publicity $publicity = null)
    {
        $this->setFile($file);
        $this->setType($type);
        $this->setPriority($priority);
        $this->setFieldsFromPublicity($publicity);
    }
}
