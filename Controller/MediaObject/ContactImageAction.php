<?php

namespace Zakjakub\OswisAddressBookBundle\Controller\MediaObject;

use Zakjakub\OswisAddressBookBundle\Entity\ContactImage;
use Zakjakub\OswisCoreBundle\Controller\AbstractClass\AbstractImageAction;
use Zakjakub\OswisCoreBundle\Entity\AbstractClass\AbstractImage;

final class ContactImageAction extends AbstractImageAction
{

    /**
     * @return string
     */
    public static function getImageClassName(): string
    {
        return ContactImage::class;
    }

    public static function getImageNewInstance(): AbstractImage
    {
        return new ContactImage();
    }
}
