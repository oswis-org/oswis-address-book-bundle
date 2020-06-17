<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Entity;

use OswisOrg\OswisAddressBookBundle\Entity\MediaObject\ContactFile;
use OswisOrg\OswisAddressBookBundle\Entity\MediaObject\ContactImage;
use OswisOrg\OswisCoreBundle\Entity\NonPersistent\Publicity;
use OswisOrg\OswisCoreBundle\Exceptions\InvalidTypeException;
use OswisOrg\OswisCoreBundle\Interfaces\Common\BasicInterface;
use OswisOrg\OswisCoreBundle\Traits\Common\BasicTrait;
use OswisOrg\OswisCoreBundle\Traits\Common\EntityPublicTrait;
use OswisOrg\OswisCoreBundle\Traits\Common\PriorityTrait;
use OswisOrg\OswisCoreBundle\Traits\Common\TypeTrait;

/**
 * @Doctrine\ORM\Mapping\Entity()
 * @Doctrine\ORM\Mapping\Table(name="address_book_contact_file_connection")
 * @Doctrine\ORM\Mapping\Cache(usage="NONSTRICT_READ_WRITE", region="address_book_contact_file")
 */
class ContactFileConnection implements BasicInterface
{
    use BasicTrait;
    use TypeTrait;
    use PriorityTrait;
    use EntityPublicTrait;

    /**
     * @Doctrine\ORM\Mapping\OneToOne(
     *     targetEntity="OswisOrg\OswisAddressBookBundle\Entity\MediaObject\ContactFile", cascade={"all"}, fetch="EAGER", orphanRemoval=true
     * )
     */
    protected ?ContactFile $file = null;

    /**
     * @Doctrine\ORM\Mapping\OneToOne(
     *     targetEntity="OswisOrg\OswisAddressBookBundle\Entity\MediaObject\ContactImage", cascade={"all"}, fetch="EAGER", orphanRemoval=true
     * )
     */
    protected ?ContactImage $image = null;

    /**
     * @param ContactFile|null $file
     * @param string|null      $type
     * @param int|null         $priority
     * @param Publicity|null   $publicity
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
