<?php

namespace App\Form;

use App\Entity\Adherent;
use App\Entity\Site;
use App\Enum\Activite;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AffaireSiteAdherentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $siteChoices = $options['sites'] ?? null;
        $adherentChoices = $options['adherents'] ?? null;

        $builder
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'code',
                'placeholder' => 'Sélectionner un site',
                'required' => true,
                'choices' => $siteChoices,
                'attr' => ['class' => 'block w-full rounded-md border-gray-300 shadow-sm']
            ])
            ->add('adherent', EntityType::class, [
                'class' => Adherent::class,
                'choice_label' => 'nom',
                'placeholder' => 'Sélectionner un adhérent',
                'required' => true,
                'choices' => $adherentChoices,
                'attr' => ['class' => 'block w-full rounded-md border-gray-300 shadow-sm']
            ])
            ->add('activite', ChoiceType::class, [
                'choices' => [
                    'MULTITECH' => Activite::MULTITECH->value,
                    'ELEC' => Activite::ELEC->value,
                    'CVC' => Activite::CVC->value,
                ],
                'required' => true,
                'attr' => ['class' => 'block w-full rounded-md border-gray-300 shadow-sm']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'csrf_protection' => true,
            // allow passing restricted choices from controller
            'sites' => null,
            'adherents' => null,
        ]);
    }
}


