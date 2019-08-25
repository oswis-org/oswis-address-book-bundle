<?php

namespace Zakjakub\OswisAddressBookBundle\Entity\AddressBook;

use Zakjakub\OswisCoreBundle\Entity\AbstractClass\AbstractRevision;
use Zakjakub\OswisCoreBundle\Entity\AbstractClass\AbstractRevisionContainer;
use Zakjakub\OswisCoreBundle\Entity\Nameable;
use Zakjakub\OswisCoreBundle\Traits\Entity\BasicEntityTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\NameableBasicTrait;
use function assert;

/**
 * @Doctrine\ORM\Mapping\Entity(repositoryClass="Zakjakub\OswisAddressBookBundle\Repository\AddressBookRevisionRepository")
 * @Doctrine\ORM\Mapping\Table(name="address_book_address_book_revision")
 * @Doctrine\ORM\Mapping\Cache(usage="NONSTRICT_READ_WRITE", region="address_book_address_book")
 */
class AddressBookRevision extends AbstractRevision
{
    use BasicEntityTrait;
    use NameableBasicTrait;

    /**
     * @var AddressBook
     * @Doctrine\ORM\Mapping\ManyToOne(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\AddressBook\AddressBook",
     *     inversedBy="revisions"
     * )
     * @Doctrine\ORM\Mapping\JoinColumn(name="container_id", referencedColumnName="id")
     */
    protected $container;

    /**
     * @param Nameable|null $nameable
     */
    public function __construct(
        ?Nameable $nameable = null
    ) {
        $this->setFieldsFromNameable($nameable);
    }

    /**
     * @return string
     */
    public static function getRevisionContainerClassName(): string
    {
        return AddressBook::class;
    }

    /**
     * @param AbstractRevisionContainer|null $revision
     */
    public static function checkRevisionContainer(?AbstractRevisionContainer $revision): void
    {
        assert($revision instanceof AddressBook);
    }
}
