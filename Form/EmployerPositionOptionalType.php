<?php

namespace Zakjakub\OswisAddressBookBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zakjakub\OswisAddressBookBundle\Entity\Position;

class EmployerPositionOptionalType extends AbstractType
{
    final public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'person',
                EmployeePersonOptionalType::class,
                array(
                    'label'    => false,
                    'required' => false,
                )
            )
            ->add(
                'name',
                null,
                array(
                    'label'    => 'Pozice ve společnosti',
                    'required' => false,
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
                'data_class' => Position::class,
//                'attr' => ['class' => 'col-md-6'],
            )
        );
    }

    final public function getName(): string
    {
        return 'address_book_employer_position_optional';
    }
}
