<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 */

namespace Zakjakub\OswisAddressBookBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zakjakub\OswisAddressBookBundle\Entity\Position;

class EmployerPositionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'person',
            EmployeePersonType::class,
            array('label' => false, 'required' => false)
        )->add(
            'name',
            null,
            array(
                'label'    => 'Pozice ve společnosti',
                'required' => false,
                'attr'     => [
                    'autocomplete' => 'organization-title',
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
                'data_class' => Position::class,
//                'attr' => ['class' => 'col-md-6'],
            )
        );
    }

    public function getName(): string
    {
        return 'address_book_employer_position';
    }
}
