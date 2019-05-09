<?php

namespace Zakjakub\OswisAddressBookBundle\Form\AbstractClass;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Zakjakub\OswisCoreBundle\Utils\FileUtils;

abstract class AbstractImageType extends AbstractType
{

    final public function buildForm(FormBuilderInterface $builder, array $options): void
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
     * @throws AccessException
     */
    final public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class'      => $this::getImageClassName(),
                'csrf_protection' => false,
            ]
        );
    }

    /**
     * @return string
     */
    abstract public static function getImageClassName(): string;

    final public function getBlockPrefix(): string
    {
        return '';
    }

}