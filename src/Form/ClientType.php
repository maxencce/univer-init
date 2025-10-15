<?php

namespace App\Form;

use App\Entity\Client;
use App\Enum\ClientType as ClientTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'constraints' => [new NotBlank()]
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'choices' => array_combine(array_map(fn($t) => $t->value, ClientTypeEnum::cases()), ClientTypeEnum::cases()),
                'choice_label' => fn($type) => $type->value,
                'choice_value' => fn($type) => $type?->value,
                'constraints' => [new NotBlank()]
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Adresse',
                'constraints' => [new NotBlank()]
            ])
            ->add('siren', TextType::class, [
                'label' => 'SIREN',
                'constraints' => [new NotBlank()]
            ])
            ->add('contactNom', TextType::class, [
                'label' => 'Nom du contact',
                'constraints' => [new NotBlank()]
            ])
            ->add('contactEmail', EmailType::class, [
                'label' => 'Email du contact',
                'constraints' => [new NotBlank()]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }
}
