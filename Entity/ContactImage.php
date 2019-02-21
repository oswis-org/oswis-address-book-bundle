<?php
// src/Entity/MediaObject.php

namespace Zakjakub\OswisAddressBookBundle\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\CreateContactImageAction;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @Doctrine\ORM\Mapping\Entity
 * @ApiResource(iri="http://schema.org/ImageObject", collectionOperations={
 *     "get",
 *     "post"={
 *         "method"="POST",
 *         "path"="/contact_images",
 *         "controller"=CreateContactImageAction::class,
 *         "defaults"={"_api_receive"=false},
 *     },
 * })
 * @Vich\Uploadable
 */
class ContactImage
{

    /**
     * @Doctrine\ORM\Mapping\Id
     * @Doctrine\ORM\Mapping\GeneratedValue(strategy="AUTO")
     * @Doctrine\ORM\Mapping\Column(type="integer")
     * @Groups({
     *     "persons_post",
     *     "person_get",
     *     "person_put",
     *     "organizations_post",
     *     "organization_get",
     *     "organization_put",
     *     "user_get"
     * })
     */
    public $id;

    /**
     * @var File|null
     * @Assert\NotNull()
     * @Vich\UploadableField(mapping="contact_image", fileNameProperty="contentUrl")
     */
    public $file;

    /**
     * @var string|null
     * @Doctrine\ORM\Mapping\Column(nullable=true)
     * @ApiProperty(iri="http://schema.org/contentUrl")
     * @Groups({
     *     "persons_post",
     *     "person_get",
     *     "person_put",
     *     "organizations_post",
     *     "organization_get",
     *     "organization_put",
     *     "user_get"
     * })
     */
    public $contentUrl;
}
