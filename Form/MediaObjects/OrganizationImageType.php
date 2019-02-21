<?php

namespace Zakjakub\OswisAccommodationBundle\Form\MediaObjects;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zakjakub\OswisAccommodationBundle\Entity\MediaObjects\OrganizationImage;

/**
 * Class OrganizationImageType
 * @package Zakjakub\OswisAccommodationBundle\Form\MediaObjects
 */
final class OrganizationImageType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'file',
            FileType::class,
            [
                'label'    => 'label.file',
                'required' => false,
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
                'data_class'      => OrganizationImage::class,
                'csrf_protection' => false,
            ]
        );
    }

    /**
     * @return string|null
     */
    public function getBlockPrefix(): string
    {
        return '';
    }
}
