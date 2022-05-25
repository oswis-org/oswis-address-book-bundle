<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use OswisOrg\OswisAddressBookBundle\Entity\MediaObject\ContactFile;
use OswisOrg\OswisAddressBookBundle\Entity\MediaObject\ContactImage;
use OswisOrg\OswisCoreBundle\Entity\NonPersistent\Publicity;
use OswisOrg\OswisCoreBundle\Exceptions\InvalidTypeException;
use OswisOrg\OswisCoreBundle\Interfaces\Common\BasicInterface;
use OswisOrg\OswisCoreBundle\Interfaces\Common\PriorityInterface;
use OswisOrg\OswisCoreBundle\Interfaces\Common\TypeInterface;
use OswisOrg\OswisCoreBundle\Traits\Common\BasicTrait;
use OswisOrg\OswisCoreBundle\Traits\Common\EntityPublicTrait;
use OswisOrg\OswisCoreBundle\Traits\Common\PriorityTrait;
use OswisOrg\OswisCoreBundle\Traits\Common\TypeTrait;

#[Entity]
#[Table(name: 'address_book_contact_file_connection')]
#[Cache(usage: 'NONSTRICT_READ_WRITE', region: 'address_book_contact_file')]
class ContactFileConnection implements BasicInterface, TypeInterface, PriorityInterface
{
    use BasicTrait;
    use TypeTrait;
    use PriorityTrait;
    use EntityPublicTrait;

    #[OneToOne(targetEntity: ContactFile::class, cascade: ['all'], fetch: 'EAGER', orphanRemoval: true)]
    protected ?ContactFile $file = null;

    #[OneToOne(targetEntity: ContactImage::class, cascade: ['all'], fetch: 'EAGER', orphanRemoval: true)]
    protected ?ContactImage $image = null;

    /**
     * @param  ContactFile|null  $file
     * @param  string|null  $type
     * @param  int|null  $priority
     * @param  Publicity|null  $publicity
     *
     * @throws InvalidTypeException
     */
    public function __construct(?ContactFile $file = null, ?string $type = null, ?int $priority = null, ?Publicity $publicity = null)
    {
        $this->setType($type);
        $this->setPriority($priority);
        $this->setFieldsFromPublicity($publicity);
        $this->setFile($file);
    }

    public function getFile(): ?ContactFile
    {
        return $this->file;
    }

    public function setFile(?ContactFile $file): void
    {
        $this->file = $file;
    }
}
