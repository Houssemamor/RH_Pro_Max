<?php

namespace App\Controller;

use App\Entity\Visitor;
use App\Form\VisitorRegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // If user is already logged in, redirect to home
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        // Get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // Last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // This method can be blank - it will be intercepted by the logout key on your firewall
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        // If user is already logged in, redirect to home
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $visitor = new Visitor();
        $form = $this->createForm(VisitorRegistrationFormType::class, $visitor);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                try {
                    // Hash the password
                    $visitor->setPassword(
                        $userPasswordHasher->hashPassword(
                            $visitor,
                            $form->get('plainPassword')->getData()
                        )
                    );

                    $entityManager->persist($visitor);
                    $entityManager->flush();

                    $this->addFlash('success', 'Your visitor account has been created successfully! You can now log in.');

                    return $this->redirectToRoute('app_login');
                } catch (\Exception $e) {
                    $this->addFlash('danger', 'An error occurred while creating your account: ' . $e->getMessage());
                }
            } else {
                $this->addFlash('danger', 'Please correct the errors in the form.');
                foreach ($form->getErrors(true) as $error) {
                    $this->addFlash('warning', $error->getMessage());
                }
            }
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
