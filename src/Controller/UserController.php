<?php
// src/Controller/UserController.php

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
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class UserController extends AbstractController
{
    #[Route('/user/{id}', name: 'user_profile', requirements: ['id' => '\\d+'], methods: ['GET','POST'])]
    public function profile(
        int $id,
        Request $request,
        UserRepository $users,
        EntityManagerInterface $em,
        CsrfTokenManagerInterface $csrfManager
    ): Response {
        $user = $users->find($id);
        if (!$user) {
            throw $this->createNotFoundException('Usuario no encontrado');
        }

        $this->denyAccessUnlessGranted('ROLE_USER');
        /** @var User $me */
        $me = $this->getUser();

        if ($request->isMethod('POST') && $request->isXmlHttpRequest()) {
            if ($me->getId() === $user->getId()) {
                return new JsonResponse(['success' => false, 'errors' => ['No puedes seguirte a ti mismo']], 400);
            }
            $intent = ($request->request->get('follow') === '1' ? 'follow' : 'unfollow') . $user->getId();
            $token  = $request->request->get('_token');
            if (!$csrfManager->isTokenValid(new \Symfony\Component\Security\Csrf\CsrfToken($intent, $token))) {
                return new JsonResponse(['success' => false, 'errors' => ['Token CSRF inválido']], 403);
            }

            $repo   = $em->getRepository(Follower::class);
            $exists = $repo->findOneBy(['follower' => $me, 'followed' => $user]);

            if ($exists) {
                $em->remove($exists);
                $em->flush();
                $isFollowing = false;
            } else {
                $follow = new Follower();
                $follow->setFollower($me)
                       ->setFollowed($user);
                $em->persist($follow);
                $em->flush();
                $isFollowing = true;
            }

            $nextIntent  = ($isFollowing ? 'unfollow' : 'follow') . $user->getId();
            $newCsrfToken = $csrfManager->getToken($nextIntent)->getValue();

            return new JsonResponse([
                'success'     => true,
                'isFollowing' => $isFollowing,
                'csrfToken'   => $newCsrfToken,
            ]);
        }

        $isFollowing = false;
        if ($me->getId() !== $user->getId()) {
            $cnt         = $em->getRepository(Follower::class)
                             ->count(['follower' => $me, 'followed' => $user]);
            $isFollowing = $cnt > 0;
        }
        $canFollow = $me->getId() !== $user->getId();

        $following = $em->getRepository(Follower::class)
            ->count(['follower' => $user]);
        $followers = $em->getRepository(Follower::class)
            ->count(['followed' => $user]);

        $posts = array_map(fn($p) => [
            'id'      => $p->getId(),
            'title'   => $p->getTitle(),
            'media'   => $p->getMedia(),
            'snippet' => mb_substr($p->getContent(), 0, 100) . (mb_strlen($p->getContent()) > 100 ? '…' : ''),
            'likes'   => count($p->getLikes()),
            'comments'     => count($p->getComments()),
        ], $user->getPosts()->toArray());

        $intent    = ($isFollowing ? 'unfollow' : 'follow') . $user->getId();
        $csrfToken = $csrfManager->getToken($intent)->getValue();

        $data = [
            'user' => [
                'id'        => $user->getId(),
                'username'  => $user->getUsername(),
                'email'     => $user->getEmail(),
                'avatar'    => $user->getAvatar(),
                'bio'       => $user->getBio(),
                'createdAt' => $user->getCreatedAt()->format('Y-m-d'),
                'following' => $following,
                'followers'=> $followers,
            ],
            'isFollowing'=> $isFollowing,
            'canFollow'  => $canFollow,
            'posts'      => $posts,
            'csrfToken'  => $csrfToken,
        ];

        return $this->render('user/profile.html.twig', [
            'initialData'=> $data,
            'apiUrl'     => $this->generateUrl('user_profile', ['id' => $id]),
        ]);
    }
}
