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
   
    #[Route('/user/{id}/avatar', name: 'user_change_avatar', methods: ['POST'])]
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

    #[Route('/user/{id}/edit', name:'user_edit_profile', methods:['GET','POST'])]
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
