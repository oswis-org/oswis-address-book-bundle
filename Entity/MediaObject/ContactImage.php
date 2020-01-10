<?php

namespace Zakjakub\OswisAddressBookBundle\Entity\MediaObject;

use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\HttpFoundation\File\File;
use Zakjakub\OswisAddressBookBundle\Controller\MediaObject\ContactImageAction;
use Zakjakub\OswisCoreBundle\Entity\AbstractClass\AbstractImage;

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
