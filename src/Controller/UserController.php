<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Follower;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    JsonResponse
};
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/user/{id}', name: 'user_profile', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function profile(
        int $id,
        Request $request,
        UserRepository $users,
        EntityManagerInterface $em
    ): Response {
        $user = $users->find($id);
        if (!$user) {
            throw $this->createNotFoundException('Usuario no encontrado');
        }

        $this->denyAccessUnlessGranted('ROLE_USER');
        /** @var User $me */
        $me = $this->getUser();
        if ($me->getId() !== $user->getId() && !in_array('ROLE_ADMIN', $me->getRoles())) {
            throw $this->createAccessDeniedException('No puedes ver ese perfil.');
        }

        $isFollowing = false;
        if ($me->getId() !== $user->getId()) {
            $count = $em->getRepository(Follower::class)
                ->count(['follower' => $me, 'followed' => $user]);
            $isFollowing = $count > 0;
        }
        $canFollow = $me->getId() !== $user->getId();

        $posts = array_map(fn($p) => [
            'id'      => $p->getId(),
            'title'   => $p->getTitle(),
            'media'   => $p->getMedia(),
            'snippet' => mb_substr($p->getContent(), 0, 100)
                         . (mb_strlen($p->getContent()) > 100 ? '…' : ''),
            'likes'   => count($p->getLikes()),
        ], $user->getPosts()->toArray());

        $data = [
            'user' => [
                'id'        => $user->getId(),
                'username'  => $user->getUsername(),
                'email'     => $user->getEmail(),
                'avatar'    => $user->getAvatar(),
                'bio'       => $user->getBio(),
                'createdAt' => $user->getCreatedAt()->format('Y-m-d'),
            ],
            'isFollowing' => $isFollowing,
            'canFollow'   => $canFollow,
            'posts'       => $posts,
        ];

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse($data);
        }

        return $this->render('user/profile.html.twig', [
            'initialData' => $data,
            'apiUrl'      => $this->generateUrl('user_profile', ['id' => $id]),
        ]);
    }
}
