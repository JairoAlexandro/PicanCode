<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileEditType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    JsonResponse,
    RedirectResponse
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

        $me = $this->getUser();
        if ($me !== $user) {
            throw $this->createAccessDeniedException();
        }

        $avatarFile = $request->files->get('avatar');
        if ($avatarFile) {
            $newFilename = uniqid() . '.' . $avatarFile->guessExtension();
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

    #[Route('/user/{id}/edit', name: 'user_edit_profile', methods: ['GET', 'POST'])]
    public function editBio(
        User $user,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $me = $this->getUser();
        if ($me !== $user) {
            throw $this->createAccessDeniedException();
        }

        if ($request->isXmlHttpRequest()) {
            if ($request->isMethod('GET')) {
                $avatarUrl = null;
                if ($user->getAvatar()) {
                    $avatarUrl = $request->getSchemeAndHttpHost()
                        . '/uploads/avatars/' . $user->getAvatar();
                }

                return $this->json([
                    'data' => [
                        'user'   => [
                            'username' => $user->getUsername(),
                        ],
                        'bio'    => $user->getBio(),
                        'avatar' => $avatarUrl,
                    ],
                    'apiUrl' => $this->generateUrl('user_edit_profile', ['id' => $user->getId()]),
                ]);
            }

            $form = $this->createForm(ProfileEditType::class, $user, [
                'csrf_protection'    => false,
                'allow_extra_fields' => true,
            ]);
            $form->handleRequest($request);

            if (!$form->isSubmitted() || !$form->isValid()) {
                $errors = [];
                foreach ($form->getErrors(true) as $error) {
                    $errors[] = $error->getMessage();
                }
                return $this->json([
                    'success' => false,
                    'errors'  => $errors,
                ], 400);
            }

            $avatarFile = $form->get('avatar')->getData();
            if ($avatarFile) {
                $newName = uniqid() . '.' . $avatarFile->guessExtension();
                try {
                    $avatarFile->move(
                        $this->getParameter('avatars_directory'),
                        $newName
                    );
                    $user->setAvatar($newName);
                } catch (FileException $e) {
                    return $this->json([
                        'success' => false,
                        'errors'  => ['Error al subir el avatar.'],
                    ], 500);
                }
            }

            $user->setUpdatedAt(new \DateTime());
            $em->flush();

            return $this->json([
                'success'     => true,
                'redirectUrl' => $this->generateUrl('user_profile', ['id' => $user->getId()]),
            ], 200);
        }

        $avatarUrl = null;
        if ($user->getAvatar()) {
            $avatarUrl = $request->getSchemeAndHttpHost()
                . '/uploads/avatars/' . $user->getAvatar();
        }

        $initialData = [
            'user'   => [
                'username' => $user->getUsername(),
            ],
            'bio'    => $user->getBio(),
            'avatar' => $avatarUrl,
        ];
        $apiUrl = $this->generateUrl('user_edit_profile', ['id' => $user->getId()]);

        return $this->render('user/edit_profile.html.twig', [
            'initialData' => $initialData,
            'apiUrl'      => $apiUrl,
        ]);
    }
}
