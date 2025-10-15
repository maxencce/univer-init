<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Utilisateur;

#[Route('/profile')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class ProfileController extends AbstractController
{
    #[Route('/', name: 'profile_show', methods: ['GET'])]
    public function show(): Response
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();

        return $this->render('profile/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/change-password', name: 'profile_change_password', methods: ['GET','POST'])]
    public function changePassword(Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $em): Response
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            $current = $request->request->get('current_password');
            $new = $request->request->get('new_password');
            $confirm = $request->request->get('confirm_password');

            if ($new !== $confirm) {
                $this->addFlash('error', 'Les nouveaux mots de passe ne correspondent pas.');
                return $this->redirectToRoute('profile_change_password');
            }

            if (!$hasher->isPasswordValid($user, $current)) {
                $this->addFlash('error', 'Mot de passe actuel incorrect.');
                return $this->redirectToRoute('profile_change_password');
            }

            $user->setMotDePasse($hasher->hashPassword($user, $new));
            $user->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();

            $this->addFlash('success', 'Mot de passe changé avec succès.');
            return $this->redirectToRoute('profile_show');
        }

        return $this->render('profile/change_password.html.twig');
    }
}


