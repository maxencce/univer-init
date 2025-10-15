<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Enum\UserRole;
use App\Form\UtilisateurType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Psr\Log\LoggerInterface;

#[Route('/admin/users')]
#[IsGranted('ROLE_ADMIN')]
class UserController extends AbstractController
{
    #[Route('/', name: 'user_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em, Request $request): Response
    {
        $role = $request->query->get('role');
        $status = $request->query->get('status', 'active');
        $search = $request->query->get('search');
        $query = $em->getRepository(Utilisateur::class)->createQueryBuilder('u');
        if ($status === 'active') {
            $query->andWhere('u.enabled = true');
        } elseif ($status === 'inactive') {
            $query->andWhere('u.enabled = false');
        }
        if ($role && in_array($role, array_column(UserRole::cases(), 'value'))) {
            $query->andWhere('u.role = :role')->setParameter('role', $role);
        }
        if ($search) {
            $query->andWhere('LOWER(u.nom) LIKE :search OR LOWER(u.email) LIKE :search')
                ->setParameter('search', '%' . strtolower($search) . '%');
        }
        $users = $query->getQuery()->getResult();
        return $this->render('user/index.html.twig', [
            'users' => $users,
            'roles' => UserRole::cases(),
            'selected_role' => $role,
            'selected_status' => $status,
            'search' => $search,
        ]);
    }

    #[Route('/new', name: 'user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher, MailerInterface $mailer): Response
    {
        $user = new Utilisateur();
        $form = $this->createForm(UtilisateurType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Générer un mot de passe temporaire aléatoire
            $plainPassword = bin2hex(random_bytes(6));
            $user->setMotDePasse(
                $hasher->hashPassword($user, $plainPassword)
            );
            // Si un adhérent a été lié via le formulaire, s'assurer de lier la relation
            if ($form->has('adherent') && $form->get('adherent')->getData()) {
                $user->setAdherent($form->get('adherent')->getData());
            }
            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setUpdatedAt(new \DateTimeImmutable());
            $em->persist($user);
            $em->flush();
            // Envoyer un email à l'utilisateur
            $email = (new Email())
                ->from('alertmanager@ambacia.net')
                ->to($user->getEmail())
                ->subject('Votre compte GESEC a été créé')
                ->text("Bonjour,\n\nVotre compte GESEC a été créé.\n\nIdentifiant : " . $user->getEmail() . "\nMot de passe temporaire : " . $plainPassword . "\n\nMerci de vous connecter et de changer votre mot de passe dès la première connexion.");
            try {
                $mailer->send($email);
            } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e) {
                $this->addFlash('danger', 'Erreur lors de l’envoi de l’email : ' . $e->getMessage());
            }
            $this->addFlash('success', 'Utilisateur créé avec succès. Un email a été envoyé à l\'utilisateur.');
            return $this->redirectToRoute('user_index');
        }
        return $this->render('user/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'user_edit', methods: ['GET', 'POST'])]
    public function edit(Utilisateur $user, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        $form = $this->createForm(UtilisateurType::class, $user, ['edit' => true]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('plainPassword')->getData()) {
                $user->setMotDePasse(
                    $hasher->hashPassword($user, $form->get('plainPassword')->getData())
                );
            }
            $user->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();
            $this->addFlash('success', 'Utilisateur modifié avec succès.');
            return $this->redirectToRoute('user_index');
        }
        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/{id}/disable', name: 'user_disable', methods: ['POST'])]
    public function disable(Utilisateur $user, EntityManagerInterface $em): Response
    {
        $user->setEnabled(false);
        $user->setUpdatedAt(new \DateTimeImmutable());
        $em->flush();
        $this->addFlash('success', 'Utilisateur désactivé.');
        return $this->redirectToRoute('user_index');
    }
}