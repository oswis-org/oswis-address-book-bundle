<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 */

namespace Zakjakub\OswisAddressBookBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zakjakub\OswisAddressBookBundle\Entity\Organization;
use Zakjakub\OswisAddressBookBundle\Form\MediaObject\ContactImageType;
use Zakjakub\OswisCoreBundle\Utils\FileUtils;

class OrganizationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $maxSize = FileUtils::humanReadableFileUploadMaxSize();
        $maxSize = $maxSize ? ' (max. '.$maxSize.')' : '';
        $builder->add(
            'name',
            TextType::class,
            array(
                'label'    => 'Název organizace',
                'required' => true,
                'help'     => 'Zadejte oficiální název organizace.',
                'attr'     => [
                    'autocomplete' => 'organization',
                ],
            )
        )->add(
            'identificationNumber',
            TextType::class,
            array(
                'label'    => 'Identifikační číslo',
                'required' => false,
                'help'     => 'Zadejte IČ (nepovinné).',
            )
        )->add(
            'description',
            TextareaType::class,
            array(
                'label'    => 'Krátký popis',
                'required' => false,
                'help'     => 'Zadejte stručné shrnutí informací o Vaší organizaci. Tyto informace můžou být zveřejněny na webu.',
                'attr'     => [],
            )
        )->add(
            'contactDetails',
            CollectionType::class,
            array(
                'label'         => false,
                'entry_type'    => ContactDetailType::class,
                'entry_options' => ['label' => false, 'content_required' => false],
            )
        )->add(
            'image',
            ContactImageType::class,
            [
                'label'    => 'Logo organizace',
                'required' => false,
                'help'     => 'Nahrajte logo Vaší organizace, nejlépe ve formátu png'.$maxSize.'. Bude uvedeno např. na webových stránkách.',
            ]
        )->add(
            'regularPositions',
            CollectionType::class,
            array(
                'label'         => 'Zástupci/kontaktní osoby organizace',
                'entry_type'    => EmployerPositionType::class,
                'entry_options' => ['label' => false, 'content_required' => false],
                'attr'          => [
                    'class' => 'box',
                ],
                'delete_empty'  => true,
            )
        )->add(
            'notes',
            CollectionType::class,
            array(
                'label'         => false,
                'entry_type'    => ContactNoteType::class,
                'entry_options' => ['label' => false],
            )
        );
    }

    /**
     * @param OptionsResolver $resolver
     *
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            array(
                'data_class' => Organization::class,
            )
        );
    }

    public function getName(): string
    {
        return 'address_book_organization';
    }
}
