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
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'name',
            TextType::class,
            array(
                'label' => 'Celé jméno',
                'attr'  => [
                    'autocomplete' => 'section-student name',
                ],
            )
        )->add(
            'details',
            CollectionType::class,
            [
                'label'         => false,
                'entry_type'    => ContactDetailType::class,
                'entry_options' => ['label' => false],
            ]
        )->add(
            'positions',
            CollectionType::class,
            [
                'label'         => 'Pozice',
                'entry_type'    => PositionType::class,
                'entry_options' => ['label' => false],
            ]
        );
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            static function (FormEvent $event) {
                if ($event->getData() instanceof Person && $event->getData()->getPositions()->isEmpty()) {
                    $event->getForm()->remove('positions');
                }
            }
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
                'data_class' => Person::class,
            )
        );
    }

    public function getName(): string
    {
        return 'address_book_person';
    }
}
