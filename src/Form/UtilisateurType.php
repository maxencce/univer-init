<?php

namespace App\Form;

use App\Entity\Utilisateur;
use App\Enum\UserRole;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $edit = $options['edit'] ?? false;
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'constraints' => [new NotBlank()]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [new NotBlank()]
            ])
            ->add('role', ChoiceType::class, [
                'label' => 'Rôle',
                'choices' => array_combine(array_map(fn($r) => $r->value, UserRole::cases()), UserRole::cases()),
                'choice_label' => fn($role) => $role->value,
                'choice_value' => fn($role) => $role?->value,
            ]);

        // When editing, add an optional plainPassword field to allow changing password
        if ($edit) {
            $builder->add('plainPassword', PasswordType::class, [
                'label' => 'Nouveau mot de passe',
                'mapped' => false,
                'required' => false,
                'attr' => ['autocomplete' => 'new-password'],
            ]);
        }
        // Si on crée/modifie un utilisateur et qu'il est ADHERENT, permettre de lier à un Adherent
        $builder->add('adherent', EntityType::class, [
            'class' => \App\Entity\Adherent::class,
            'choice_label' => 'nom',
            'label' => 'Adhérent (si rôle ADHERENT)',
            'required' => false,
            'placeholder' => 'Aucun',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
            'edit' => false,
        ]);
    }
}
