<?php

namespace OswisOrg\OswisAddressBookBundle\Controller\MediaObject;

use OswisOrg\OswisAddressBookBundle\Entity\MediaObject\ContactImage;
use OswisOrg\OswisAddressBookBundle\Form\MediaObject\ContactImageType;
use OswisOrg\OswisCoreBundle\Controller\AbstractClass\AbstractImageAction;

final class ContactImageAction extends AbstractImageAction
{
    public static function getFileClassName(): string
    {
        return ContactImage::class;
    }

    public static function getFileFormClass(): string
    {
        return ContactImageType::class;
    }

    public static function getFileNewInstance(): ContactImage
    {
        return new ContactImage();
    }
}
