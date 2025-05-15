<?php
namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    #[Route('/usuario/{id}', name: 'user_profile', requirements: ['id' => '\d+'])]
    public function profile(int $id, UserRepository $users): Response
    {
        $user = $users->find($id);
        if (!$user) {
            throw $this->createNotFoundException('Usuario no encontrado');
        }

        // Sólo puede verlo el propio usuario o un admin
        $this->denyAccessUnlessGranted('ROLE_USER');
        if ($this->getUser()->getId() !== $id && !in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            throw $this->createAccessDeniedException('No puedes ver el perfil de otro usuario.');
        }

        return $this->render('user/profile.html.twig', [
            'user' => $user,
        ]);
    }
}
