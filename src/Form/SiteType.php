<?php

namespace App\Form;

use App\Entity\Site;
use App\Entity\Client;
use App\Enum\SiteStatut;
use App\Enum\Activite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class SiteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

            $builder
            ->add('codeSite', TextType::class, [
                'label' => 'Code Site',
                'constraints' => [new NotBlank()]
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom du site',
                'required' => false
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Adresse',
                'constraints' => [new NotBlank()]
            ])
            ->add('codePostal', TextType::class, [
                'label' => 'Code postal',
                'required' => false
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville',
                'required' => false
            ])
            ->add('regionClient', TextType::class, [
                'label' => 'Région client',
                'required' => false
            ])
            ->add('contactNom', TextType::class, [
                'label' => 'Nom du contact',
                'required' => false
            ])
            ->add('contactEmail', EmailType::class, [
                'label' => 'Email du contact',
                'required' => false
            ])
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'choice_label' => 'nom',
                'label' => 'Client',
                'constraints' => [new NotBlank()]
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => array_combine(array_map(fn($s) => $s->value, SiteStatut::cases()), SiteStatut::cases()),
                'choice_label' => fn($statut) => $statut->value,
                'choice_value' => fn($statut) => $statut?->value,
                'data' => SiteStatut::ACTIF,
            ])
            ->add('activite', ChoiceType::class, [
                'label' => 'Activité',
                'choices' => array_combine(array_map(fn($a) => $a->value, Activite::cases()), Activite::cases()),
                'choice_label' => fn($activite) => $activite->value,
                'choice_value' => fn($activite) => $activite?->value,
                'data' => Activite::MULTITECH,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Site::class,
            'is_edit' => false,
        ]);
    }
}
