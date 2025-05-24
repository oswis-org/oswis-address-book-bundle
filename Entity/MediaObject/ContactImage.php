<?php
/**
 * @noinspection PhpUnused
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Entity\MediaObject;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Gedmo\Mapping\Annotation\Uploadable;
use OswisOrg\OswisAddressBookBundle\Controller\MediaObject\ContactImageAction;
use OswisOrg\OswisAddressBookBundle\Entity\AbstractClass\AbstractContact;
use OswisOrg\OswisCoreBundle\Entity\AbstractClass\AbstractImage;
use OswisOrg\OswisCoreBundle\Entity\NonPersistent\Publicity;
use OswisOrg\OswisCoreBundle\Exceptions\InvalidTypeException;
use OswisOrg\OswisCoreBundle\Traits\Common\BasicTrait;
use OswisOrg\OswisCoreBundle\Traits\Common\EntityPublicTrait;
use OswisOrg\OswisCoreBundle\Traits\Common\PriorityTrait;
use OswisOrg\OswisCoreBundle\Traits\Common\TypeTrait;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints\NotNull;
use Vich\UploaderBundle\Mapping\Annotation\UploadableField;

#[Entity]
#[Table(name: 'address_book_contact_image')]
#[Cache(usage: 'NONSTRICT_READ_WRITE', region: 'address_book_contact_image')]
#[Uploadable]
#[ApiResource(
    operations: [
        new Get(),
        new Post(
            uriTemplate: '/address_book_contact_image',
            controller: ContactImageAction::class,
            deserialize: false,
        ),
    ]
)]
class ContactImage extends AbstractImage
{
    public const string TYPE_PHOTO = 'photo';
    public const string TYPE_LOGO = 'logo';
    use BasicTrait;
    use TypeTrait;
    use PriorityTrait;
    use EntityPublicTrait;

    #[NotNull]
    #[UploadableField(mapping: 'address_book_contact_image', fileNameProperty: 'contentName', mimeType: 'contentMimeType')]
    public ?File $file = null;

    #[ManyToOne(targetEntity: AbstractContact::class, cascade: ['all'], inversedBy: 'images')]
    #[JoinColumn(name: 'contact_id', referencedColumnName: 'id')]
    protected ?AbstractContact $contact = null;

    /**
     * @param  File|null  $file
     * @param  string|null  $type
     * @param  int|null  $priority
     * @param  Publicity|null  $publicity
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

    public static function getAllowedTypes(): array
    {
        return [self::TYPE_LOGO, self::TYPE_PHOTO];
    }

    public function getContact(): ?AbstractContact
    {
        return $this->contact;
    }

    public function setContact(?AbstractContact $contact): void
    {
        if (null !== $this->contact && $contact !== $this->contact) {
            $this->contact->removeImage($this);
        }
        $this->contact = $contact;
    }
}
