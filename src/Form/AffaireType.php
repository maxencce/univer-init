<?php

namespace App\Form;

use App\Entity\AffaireMaster;
use App\Entity\AffaireVersion;
use App\Entity\Client;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use App\Enum\AffaireStatut;

class AffaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Form pour créer/éditer AffaireMaster + AffaireVersion minimale
        $builder
            ->add('code', TextType::class, [
                'label' => 'Code',
                'attr' => ['placeholder' => 'Code unique de l\'affaire']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => true,
                'attr' => ['rows' => 3],
                // map to currentVersion.description on AffaireMaster
                'mapped' => true,
                'property_path' => 'currentVersion.description',
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => array_combine(array_map(fn($s) => $s->value, AffaireStatut::cases()), AffaireStatut::cases()),
                'choice_label' => fn($statut) => $statut->value,
                'choice_value' => fn($statut) => $statut?->value,
                'mapped' => true,
                'property_path' => 'currentVersion.statut',
            ])
            ->add('dateDebut', DateType::class, [
                'label' => 'Date de début',
                'required' => false,
                'widget' => 'single_text',
                'mapped' => true,
                'property_path' => 'currentVersion.dateDebut',
            ])
            ->add('dateFin', DateType::class, [
                'label' => 'Date de fin',
                'required' => false,
                'widget' => 'single_text',
                'mapped' => true,
                'property_path' => 'currentVersion.dateFin',
            ])
            ->add('version', IntegerType::class, [
                'label' => 'Version',
                'attr' => ['min' => 1],
                'data' => 1,
                'mapped' => true,
                'property_path' => 'currentVersion.versionNumber'
            ])
            ->add('archive', CheckboxType::class, [
                'label' => 'Archivée',
                'required' => false,
                'data' => false,
                'mapped' => true,
                'property_path' => 'archive'
            ])
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'choice_label' => 'nom',
                'label' => 'Client'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // on peut hydrater soit AffaireMaster soit AffaireVersion selon usage
            'data_class' => AffaireMaster::class,
        ]);
    }
}
