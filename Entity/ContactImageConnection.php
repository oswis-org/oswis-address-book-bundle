<?php

namespace Zakjakub\OswisAddressBookBundle\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use Doctrine\ORM\Mapping as ORM;
use Zakjakub\OswisAddressBookBundle\Entity\AbstractClass\AbstractContact;
use Zakjakub\OswisCoreBundle\Filter\SearchAnnotation as Searchable;
use Zakjakub\OswisCoreBundle\Traits\Entity\BasicEntityTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\DescriptionTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\PriorityTrait;

/**
 * Connection between person and skill.
 * @Doctrine\ORM\Mapping\Entity()
 * @Doctrine\ORM\Mapping\Table(name="address_book_contact_image_connection")
 * @ApiResource(
 *   attributes={
 *     "filters"={"search"},
 *     "access_control"="is_granted('ROLE_MANAGER')"
 *   },
 *   collectionOperations={
 *     "get"={
 *       "access_control"="is_granted('ROLE_MANAGER')",
 *       "normalization_context"={"groups"={"address_book_contact_image_connections_get"}},
 *     },
 *     "post"={
 *       "access_control"="is_granted('ROLE_MANAGER')",
 *       "denormalization_context"={"groups"={"address_book_contact_image_connections_post"}}
 *     }
 *   },
 *   itemOperations={
 *     "get"={
 *       "access_control"="is_granted('ROLE_MANAGER')",
 *       "normalization_context"={"groups"={"address_book_contact_image_connection_get"}},
 *     },
 *     "put"={
 *       "access_control"="is_granted('ROLE_MANAGER')",
 *       "denormalization_context"={"groups"={"address_book_contact_image_connection_put"}}
 *     },
 *     "delete"={
 *       "access_control"="is_granted('ROLE_ADMIN')",
 *       "denormalization_context"={"groups"={"address_book_contact_image_connection_delete"}}
 *     }
 *   }
 * )
 * @ApiFilter(OrderFilter::class)
 * @Searchable({
 *     "id",
 *     "name",
 *     "description",
 *     "note"
 * })
 */
class ContactImageConnection
{
    use BasicEntityTrait;
    use PriorityTrait;
    use DescriptionTrait;

    /**
     * Person in this position.
     * @var Person|null $contact
     * @Doctrine\ORM\Mapping\ManyToOne(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\AbstractClass\AbstractContact",
     *     inversedBy="imageConnections",
     *     fetch="EAGER"
     * )
     * @Doctrine\ORM\Mapping\JoinColumn(name="contact_id", referencedColumnName="id")
     */
    private $contact;

    /**
     * Contact image.
     * @var ContactImage|null $contactImage
     * @Doctrine\ORM\Mapping\ManyToOne(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\ContactImage",
     *     fetch="EAGER"
     * )
     * @Doctrine\ORM\Mapping\JoinColumn(name="contact_image_id", referencedColumnName="id")
     */
    private $contactImage;

    /**
     * Is public on website?
     * @var bool|null
     * @ORM\Column(type="boolean")
     */
    private $isPublicOnWebsite;

    /**
     * Is profile photo?
     * @var bool|null
     * @ORM\Column(type="boolean")
     */
    private $isProfilePhoto;

    /**
     * ContactImageConnection constructor.
     *
     * @param AbstractContact|null $contact
     * @param ContactImage|null    $contactImage
     * @param bool|null            $isProfilePhoto
     * @param bool|null            $isPublicOnWebsite
     */
    public function __construct(
        ?AbstractContact $contact = null,
        ?ContactImage $contactImage = null,
        ?bool $isProfilePhoto = null,
        ?bool $isPublicOnWebsite = null
    ) {
        $this->setContact($contact);
        $this->setContactImage($contactImage);
        $this->setIsProfilePhoto($isProfilePhoto);
        $this->setIsPublicOnWebsite($isPublicOnWebsite);
    }

    /**
     * @return bool|null
     */
    final public function getIsPublicOnWebsite(): ?bool
    {
        return $this->isPublicOnWebsite;
    }

    /**
     * @param bool|null $isPublicOnWebsite
     */
    final public function setIsPublicOnWebsite(?bool $isPublicOnWebsite): void
    {
        $this->isPublicOnWebsite = $isPublicOnWebsite;
    }

    /**
     * @return bool|null
     */
    final public function getIsProfilePhoto(): ?bool
    {
        return $this->isProfilePhoto;
    }

    /**
     * @param bool|null $isProfilePhoto
     */
    final public function setIsProfilePhoto(?bool $isProfilePhoto): void
    {
        $this->isProfilePhoto = $isProfilePhoto;
    }

    /**
     * @return Person
     */
    final public function getContact(): ?AbstractContact
    {
        return $this->contact;
    }

    /**
     * @param AbstractContact $contact
     */
    final public function setContact(?AbstractContact $contact): void
    {
        if ($this->contact && $contact !== $this->contact) {
            $this->contact->removeImageConnection($this);
        }
        $this->contact = $contact;
        if ($contact && $this->contact !== $contact) {
            $contact->addImageConnection($this);
        }
    }

    /**
     * @return ContactImage
     */
    final public function getContactImage(): ?ContactImage
    {
        return $this->contactImage;
    }

    /**
     * @param ContactImage|null $contactImage
     */
    final public function setContactImage(?ContactImage $contactImage): void
    {
        $this->contactImage = $contactImage;
    }

}
