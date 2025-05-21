<?php
// src/Controller/ProfileController.php
namespace App\Controller;

use App\Entity\User;
use App\Entity\Follower;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    #[Route('/usuario/{id}', name: 'user_profile', requirements: ['id' => '\d+'])]
    public function profile(int $id, EntityManagerInterface $em): Response
    {
        // 1) Busca el usuario
        $user = $em->getRepository(User::class)->find($id);
        if (!$user) {
            throw $this->createNotFoundException('Usuario no encontrado');
        }

        // 2) Restringe acceso a usuarios autenticados
        $this->denyAccessUnlessGranted('ROLE_USER');

        // 3) Comprueba si el usuario logueado sigue al perfil
        /** @var User|null $me */
        $me = $this->getUser();
        $userIsFollowing = false;
        if ($me instanceof User && $me->getId() !== $user->getId()) {
            $userIsFollowing = (bool) $em
                ->getRepository(Follower::class)
                ->findOneBy([
                    'follower' => $me,
                    'followed' => $user,
                ]);
        }

        // 4) Renderiza PASANDO el nuevo flag
        return $this->render('user/profile.html.twig', [
            'user'              => $user,
            'user_is_following' => $userIsFollowing,
        ]);
    }
}
