<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 */

namespace Zakjakub\OswisAddressBookBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zakjakub\OswisAddressBookBundle\Entity\Person;

class EmployeePersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'fullName',
            TextType::class,
            [
                'label'    => 'Celé jméno',
                'required' => $options['content_required'],
                'attr'     => [
                    'autocomplete' => 'section-organization-employee name',
                ],
            ]
        )->add(
            'contactDetails',
            CollectionType::class,
            array(
                'label'         => false,
                'entry_type'    => ContactDetailType::class,
                'entry_options' => ['label' => false, 'content_required' => $options['content_required']],
            )
        )->add(
            'studies',
            CollectionType::class,
            array(
                'label'         => 'Student/absolvent UP',
                'help'          => 'Pokud studoval(a) nebo absolvoval(a) studium na Univerzitě Palackého, vyberte příslušnou fakultu, jinak nechte pole prázdné.',
                'entry_type'    => SchoolPositionType::class,
                'entry_options' => ['label' => false],
                'attr'          => [
                    'autocomplete' => 'section-organization-employee email',
                ],
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
                'data_class'       => Person::class,
                'content_required' => false,
            )
        );
    }

    public function getName(): string
    {
        return 'address_book_employee_person';
    }
}
