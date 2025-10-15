<?php

namespace App\Form;

use App\Entity\Client;
use App\Enum\AffaireStatut;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AffaireSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Code',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Rechercher par code',
                    'class' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm'
                ]
            ])
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'choice_label' => 'nom',
                'placeholder' => 'Tous les clients',
                'required' => false,
                'attr' => [
                    'class' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm'
                ]
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Tous' => '',
                    'Offre' => AffaireStatut::OFFRE->value,
                    'Contrat' => AffaireStatut::CONTRAT->value
                ],
                'required' => false,
                'attr' => [
                    'class' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm'
                ]
            ])
            ->add('dateDebutMin', DateType::class, [
                'label' => 'Date début (min)',
                'widget' => 'single_text',
                'required' => false,
                'html5' => true,
                'attr' => [
                    'class' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm'
                ]
            ])
            ->add('dateDebutMax', DateType::class, [
                'label' => 'Date début (max)',
                'widget' => 'single_text',
                'required' => false,
                'html5' => true,
                'attr' => [
                    'class' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm'
                ]
            ])
            ->add('archive', ChoiceType::class, [
                'label' => 'Statut archivage',
                'choices' => [
                    'Toutes' => '',
                    'Non archivées' => '0',
                    'Archivées' => '1'
                ],
                'required' => false,
                'data' => '0', // Par défaut, afficher les non-archivées
                'attr' => [
                    'class' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
} 