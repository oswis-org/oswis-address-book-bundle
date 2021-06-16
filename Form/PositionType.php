<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Form;

use OswisOrg\OswisAddressBookBundle\Entity\Organization;
use OswisOrg\OswisAddressBookBundle\Entity\Position;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PositionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'organization',
            EntityType::class,
            array(
                'label'    => false,
                'required' => false,
                'help'     => 'Vyberte organizaci.',
                'class'    => Organization::class,
            )
        );
    }

    /**
     * @param  OptionsResolver  $resolver
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
        return 'address_book_position';
    }
}
