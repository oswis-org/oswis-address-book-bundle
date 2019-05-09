<?php

namespace Zakjakub\OswisAddressBookBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zakjakub\OswisAddressBookBundle\Entity\Organization;
use Zakjakub\OswisCoreBundle\Utils\FileUtils;

class OrganizationType extends AbstractType
{
    // WTF? Asi není nikde využito.

    final public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $maxSize = FileUtils::humanReadableFileUploadMaxSize();
        $maxSize = $maxSize ? ' (max. '.$maxSize.')' : '';

        $builder
            ->add(
                'name',
                null,
                array(
                    'label'    => 'Název organizace',
                    'required' => true,
                    'help'     => 'Zadejte oficiální název organizace tak, jak je uveden v obchodním rejstříku.',
                    'attr'     => [
                        'autocomplete' => 'organization',
                    ],
                )
            )
            ->add(
                'description',
                TextareaType::class,
                array(
                    'label'    => 'Zaměření (krátký popis)',
                    'required' => false,
                    'help'     => 'Zadejte stručné shrnutí informací o Vaší organizaci. Tyto informace budou zveřejněny studentům.',
                    'attr'     => [],
                )
            )
            ->add(
                'contactDetails',
                CollectionType::class,
                array(
                    'label'         => false,
                    'entry_type'    => ContactDetailType::class,
                    'entry_options' => array('label' => false),
                )
            )
            ->add(
                'regularPositions',
                CollectionType::class,
                array(
                    'label'         => 'Zástupci organizace',
                    'entry_type'    => EmployerPositionType::class,
                    'entry_options' => array('label' => false),
                    'attr'          => ['class' => 'row'],
                    'allow_add'     => true,
                    'allow_delete'  => true,
                    'by_reference'  => false,
                    'prototype'     => true,
                    'delete_empty'  => true,
                    'help'          => 'Pokud potřebujete přidat dalšího zástupce, využijte tlačítko na konci formuláře.',
                )
            )
            ->add(
                'image',
                ContactImageType::class,
                array(
                    'label'    => 'Logo organizace',
                    'required' => false,
                    'help'     => 'Nahrajte logo Vaší organizace, nejlépe ve formátu png'.$maxSize.'. Bude uvedeno např. na webových stránkách.',
                    'attr'     => [
                        'autocomplete' => 'photo',
                    ],
                )
            )
            ->add(
                'notes',
                CollectionType::class,
                array(
                    'label'         => false,
                    'entry_type'    => ContactNoteType::class,
                    'entry_options' => array('label' => false),
                )
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
            array(
                'data_class' => Organization::class,
            )
        );
    }

    final public function getName(): string
    {
        return 'address_book_organization';
    }
}
