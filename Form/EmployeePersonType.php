<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Form;

use OswisOrg\OswisAddressBookBundle\Entity\Person;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmployeePersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('fullName', TextType::class, [
            'label'    => 'Celé jméno',
            'required' => $options['content_required'],
            'attr'     => [
                'autocomplete' => 'section-organization-employee name',
            ],
        ])->add('details', CollectionType::class, [
            'label'         => false,
            'entry_type'    => ContactDetailType::class,
            'entry_options' => ['label' => false, 'content_required' => $options['content_required']],
        ])->add('positions', CollectionType::class, [
            'label'         => 'Student/absolvent UP',
            'help'          => 'Pokud studoval(a) nebo absolvoval(a) studium na Univerzitě Palackého, vyberte příslušnou fakultu, jinak nechte pole prázdné.',
            'entry_type'    => SchoolPositionType::class,
            'entry_options' => ['label' => false],
            'attr'          => [
                'autocomplete' => 'section-organization-employee email',
            ],
        ]);
    }

    /**
     * @param  OptionsResolver  $resolver
     *
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'       => Person::class,
            'content_required' => false,
        ]);
    }

    public function getName(): string
    {
        return 'address_book_employee_person';
    }
}
