<?php

namespace OswisOrg\OswisAddressBookBundle\Controller\MediaObject;

use OswisOrg\OswisAddressBookBundle\Entity\MediaObject\ContactImage;
use OswisOrg\OswisCoreBundle\Controller\AbstractClass\AbstractImageAction;
use OswisOrg\OswisCoreBundle\Entity\AbstractClass\AbstractImage;

final class ContactImageAction extends AbstractImageAction
{
    public static function getFileClassName(): string
    {
        return ContactImage::class;
    }

    public static function getFileNewInstance(): AbstractImage
    {
        return new ContactImage();
    }
}
