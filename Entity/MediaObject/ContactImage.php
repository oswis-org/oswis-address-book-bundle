<?php

namespace OswisOrg\OswisAddressBookBundle\Entity\MediaObject;

use ApiPlatform\Core\Annotation\ApiResource;
use OswisOrg\OswisAddressBookBundle\Controller\MediaObject\ContactImageAction;
use OswisOrg\OswisCoreBundle\Entity\AbstractClass\AbstractImage;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @Doctrine\ORM\Mapping\Entity()
 * @Doctrine\ORM\Mapping\Table(name="address_book_contact_image")
 * @ApiResource(
 *   iri="http://schema.org/ImageObject",
 *   collectionOperations={
 *     "get",
 *     "post"={
 *         "method"="POST",
 *         "path"="/address_book_contact_image",
 *         "controller"=ContactImageAction::class,
 *         "defaults"={"_api_receive"=false},
 *     },
 *   }
 * )
 * @Vich\UploaderBundle\Mapping\Annotation\Uploadable()
 * @Doctrine\ORM\Mapping\Cache(usage="NONSTRICT_READ_WRITE", region="address_book_contact")
 */
class ContactImage extends AbstractImage
{
    /**
     * @Symfony\Component\Validator\Constraints\NotNull()
     * @Vich\UploaderBundle\Mapping\Annotation\UploadableField(
     *     mapping="address_book_contact_image",
     *     fileNameProperty="contentUrl",
     *     mimeType="contentMimeType"
     * )
     */
    public ?File $file = null;
}
