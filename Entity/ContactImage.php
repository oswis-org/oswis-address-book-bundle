<?php
// src/Entity/MediaObject.php

namespace Zakjakub\OswisAddressBookBundle\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\HttpFoundation\File\File;
use Zakjakub\OswisAddressBookBundle\Controller\CreateContactImageAction;

/**
 * @Doctrine\ORM\Mapping\Entity
 * @Doctrine\ORM\Mapping\Table(name="address_book_contact_image")
 * @ApiResource(iri="http://schema.org/ImageObject", collectionOperations={
 *     "get",
 *     "post"={
 *         "method"="POST",
 *         "path"="/contact_images",
 *         "controller"=CreateContactImageAction::class,
 *         "defaults"={"_api_receive"=false},
 *     },
 * })
 * @Vich\UploaderBundle\Mapping\Annotation\Uploadable
 */
class ContactImage
{

    /**
     * @Doctrine\ORM\Mapping\Id
     * @Doctrine\ORM\Mapping\GeneratedValue(strategy="AUTO")
     * @Doctrine\ORM\Mapping\Column(type="integer")
     */
    public $id;

    /**
     * @var File|null
     * @Symfony\Component\Validator\Constraints\NotNull()
     * @Vich\UploaderBundle\Mapping\Annotation\UploadableField(mapping="contact_image", fileNameProperty="contentUrl")
     */
    public $file;

    /**
     * @var string|null
     * @Doctrine\ORM\Mapping\Column(nullable=true)
     * @ApiProperty(iri="http://schema.org/contentUrl")
     */
    public $contentUrl;

    final public function __toString(): string
    {
        return $this->contentUrl ?? '';
    }

}
