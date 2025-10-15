<?php

namespace App\Form;

use App\Entity\AffaireMaster;
use App\Enum\AffaireStatut;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class AffaireEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['rows' => 3],
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
            ->add('archive', CheckboxType::class, [
                'label' => 'Archivée',
                'required' => false,
                'mapped' => true,
                'property_path' => 'archive',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AffaireMaster::class,
        ]);
    }
}


