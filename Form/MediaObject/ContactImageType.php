<?php

namespace Zakjakub\OswisAddressBookBundle\Form\MediaObject;

use Zakjakub\OswisAddressBookBundle\Entity\MediaObject\ContactImage;
use Zakjakub\OswisCoreBundle\Form\AbstractClass\AbstractImageType;

class ContactImageType extends AbstractImageType
{
    public static function getImageClassName(): string
    {
        return ContactImage::class;
    }
}
