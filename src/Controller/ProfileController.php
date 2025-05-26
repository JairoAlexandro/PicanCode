<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Follower;
use App\Repository\UserRepository;
use App\Form\ProfileEditType;  
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse,
    JsonResponse
};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ProfileController extends AbstractController
{
    #[Route('/usuario/{id}', name: 'user_profile', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function profile(
        int $id,
        Request $request,
        UserRepository $users,
        EntityManagerInterface $em
    ): Response {
        // 1) Recuperar usuario o 404
        $user = $users->find($id);
        if (!$user) {
            throw $this->createNotFoundException('Usuario no encontrado');
        }

        // 2) Permisos
        $this->denyAccessUnlessGranted('ROLE_USER');
        /** @var User $me */
        $me = $this->getUser();
        if ($me->getId() !== $user->getId() && !in_array('ROLE_ADMIN', $me->getRoles())) {
            throw $this->createAccessDeniedException('No puedes ver ese perfil.');
        }

        // 3) Estado de seguimiento
        $isFollowing = false;
        if ($me->getId() !== $user->getId()) {
            $count = $em->getRepository(Follower::class)
                ->count(['follower' => $me, 'followed' => $user]);
            $isFollowing = $count > 0;
        }
        $canFollow = $me->getId() !== $user->getId();

        // 4) Mapeo de posts para el frontend
        $posts = array_map(fn($p) => [
            'id'      => $p->getId(),
            'title'   => $p->getTitle(),
            'media'   => $p->getMedia(),
            'snippet' => mb_substr($p->getContent(), 0, 100)
                         . (mb_strlen($p->getContent()) > 100 ? '…' : ''),
            'likes'   => count($p->getLikes()),
        ], $user->getPosts()->toArray());

        // 5) Preparamos el objeto que meteremos en React
        $data = [
            'user'         => [
                'id'        => $user->getId(),
                'username'  => $user->getUsername(),
                'email'     => $user->getEmail(),
                'avatar'    => $user->getAvatar(),
                'bio'       => $user->getBio(),
                'createdAt' => $user->getCreatedAt()->format('Y-m-d'),
            ],
            'isFollowing'  => $isFollowing,
            'canFollow'    => $canFollow,
            'posts'        => $posts,
        ];

        // 6) Si es AJAX ➞ JSON
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse($data);
        }

        // 7) Carga inicial: render con initialData y apiUrl
        return $this->render('user/profile.html.twig', [
            'initialData' => $data,
            'apiUrl'      => $this->generateUrl('user_profile', ['id' => $id]),
        ]);
    }

    #[Route('/usuario/{id}/avatar', name: 'user_change_avatar', methods: ['POST'])]
    public function changeAvatar(
        User $user,
        Request $request,
        EntityManagerInterface $em
    ): RedirectResponse {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User $me */
        $me = $this->getUser();
        if ($me->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException();
        }

        $avatarFile = $request->files->get('avatar');
        if ($avatarFile) {
            $newFilename = uniqid().'.'.$avatarFile->guessExtension();
            try {
                $avatarFile->move(
                    $this->getParameter('avatars_directory'),
                    $newFilename
                );
                $user->setAvatar($newFilename);
                $em->flush();
                $this->addFlash('success', 'Avatar actualizado.');
            } catch (FileException $e) {
                $this->addFlash('error', 'Error al subir el avatar.');
            }
        } else {
            $this->addFlash('warning', 'No seleccionaste ningún archivo.');
        }

        return $this->redirectToRoute('user_profile', ['id' => $user->getId()]);
    }

    #[Route('/usuario/{id}/editar', name:'user_edit_profile', methods:['GET','POST'])]
    public function editBio(
        User $user,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        
        $this->denyAccessUnlessGranted('ROLE_USER');
         /** @var \App\Entity\User $me */
        $me = $this->getUser();
        if ($me->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(ProfileEditType::class, $user, [
            'attr' => ['enctype'=>'multipart/form-data']
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $avatarFile */
            $avatarFile = $form->get('avatar')->getData();
            if ($avatarFile) {
                $newName = uniqid().'.'.$avatarFile->guessExtension();
                try {
                    $avatarFile->move(
                        $this->getParameter('avatars_directory'),
                        $newName
                    );
                    $user->setAvatar($newName);
                } catch (FileException $e) {
                    $this->addFlash('error','Error al subir el avatar.');
                }
            }

            $user->setUpdatedAt(new \DateTime());
            $em->flush();

            $this->addFlash('success','Perfil actualizado.');
            return $this->redirectToRoute('user_profile',['id'=>$user->getId()]);
        }

        return $this->render('user/edit_profile.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
