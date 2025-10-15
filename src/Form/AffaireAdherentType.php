<?php

namespace App\Form;

use App\Entity\AffaireAdherent;
use App\Entity\Adherent;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class AffaireAdherentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('adherent', EntityType::class, [
                'class' => Adherent::class,
                'choice_label' => 'nom',
                'label' => 'Adhérent',
                'constraints' => [new NotBlank()]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AffaireAdherent::class,
        ]);
    }
}
