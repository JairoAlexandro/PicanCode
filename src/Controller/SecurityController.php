<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    //Aqui está todo mezclado desde gestion y lo suyo seria tener seguridad separada, y cada cosa en su sitio

    #[Route(path: '/login', name: 'app_login')]
    public function loginApp(AuthenticationUtils $authUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('post_index');
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $authUtils->getLastUsername(),
            'error'         => $authUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logoutApp(): void
    {
        
    }

    #[Route(path: '/gestion', name: 'gestion_home')]
    public function homeGestion(AuthenticationUtils $authUtils): Response
    {
        return $this->redirectToRoute('gestion_login');
}

    #[Route(path: '/gestion/login', name: 'gestion_login')]
    public function loginGestion(AuthenticationUtils $authUtils): Response
    {
        if ($this->getUser() && $this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('gestion_panel');
        }

        return $this->render('security/gestion_login.html.twig', [
            'last_username' => $authUtils->getLastUsername(),
            'error'         => $authUtils->getLastAuthenticationError(),
        ]);
}

    #[Route(path: '/gestion/logout', name: 'gestion_logout')]
    public function logoutGestion(): void
    {
        
    }
}
