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

class StudentPersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('fullName', TextType::class, [
            'label' => 'Celé jméno',
            'attr'  => ['autocomplete' => 'section-student name'],
        ])->add('details', CollectionType::class, [
            'label'         => false,
            'entry_type'    => ContactDetailType::class,
            'entry_options' => ['label' => false],
        ])->add('studies', CollectionType::class, [
            'label'         => 'Fakulta',
            'entry_type'    => SchoolPositionType::class,
            'entry_options' => ['label' => false],
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
            'data_class' => Person::class,
        ]);
    }

    public function getName(): string
    {
        return 'address_book_student_person';
    }
}
