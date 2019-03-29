<?php
// src/Form/MediaObjectType.php

namespace Zakjakub\OswisAddressBookBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Zakjakub\OswisAddressBookBundle\Entity\ContactImage;
use Zakjakub\OswisCoreBundle\Utils\FileUtils;

final class ContactImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $maxSize = FileUtils::humanReadableFileUploadMaxSize();
        $maxSize = $maxSize ? ' (max. '.$maxSize.')' : '';

        $builder->add(
            'file',
            VichImageType::class,
            [
                'label'          => false,
                'download_label' => true,
                'download_uri'   => true,
                'required'       => false,
                'attr'           => [
                    'placeholder' => 'Kliknutím vyberte soubor'.$maxSize.'...',
                ],
            ]
        );
    }

    /**
     * @param OptionsResolver $resolver
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class'      => ContactImage::class,
                'csrf_protection' => false,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
