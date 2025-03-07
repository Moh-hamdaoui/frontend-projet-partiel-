<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\ORM\EntityManagerInterface;

class AuthController extends AbstractController
{
    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createFormBuilder($user)
            ->add('email')
            ->add('password')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Encodage du mot de passe avant de l'enregistrer
            $user->setPassword($passwordEncoder->encodePassword($user, $user->getPassword()));
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('auth/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

      /**
     * @Route("/api/login", name="api_login", methods={"POST"})
     */
    public function login(Request $request, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $entityManager, JWTManager $jwtManager): Response
    {
    
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            return new Response('Utilisateur non trouvé', 404);
        }
        if (!$passwordEncoder->isPasswordValid($user, $password)) {
            return new Response('Mot de passe incorrect', 401);
        }

        $token = $jwtManager->create($user);

        return new Response(json_encode(['token' => $token]), 200, [
            'Content-Type' => 'application/json'
        ]);
    }
}

