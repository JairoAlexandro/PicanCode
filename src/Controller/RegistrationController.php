<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use App\Security\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;
    private string $avatarsDir;

    public function __construct(EmailVerifier $emailVerifier, string $avatars_directory)
    {
        $this->emailVerifier = $emailVerifier;
        $this->avatarsDir    = $avatars_directory;
    }

    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request                      $request,
        UserPasswordHasherInterface  $passwordHasher,
        UserAuthenticatorInterface   $userAuthenticator,
        LoginFormAuthenticator       $authenticator,
        EntityManagerInterface       $entityManager
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('post_index');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // 1) Hasheamos la contraseña
            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            // 2) Avatar (si la aplicación lo usará)
            /** @var UploadedFile|null $avatarFile */
            $avatarFile = $form->get('avatar')->getData();
            if ($avatarFile) {
                $newFilename = uniqid() . '.' . $avatarFile->guessExtension();
                try {
                    $avatarFile->move($this->avatarsDir, $newFilename);
                    $user->setAvatar($newFilename);
                } catch (FileException $e) {
                    if ($request->isXmlHttpRequest()) {
                        return new JsonResponse([
                            'success' => false,
                            'message' => 'Error al subir el avatar'
                        ], Response::HTTP_BAD_REQUEST);
                    }
                }
            }

            // 3) Bio (si tu form lo mapea directamente, no hace falta código extra)

            // 4) Actualiza el timestamp de "updatedAt"
            $user->setUpdatedAt(new \DateTime());

            // Marcar como verificado por defecto (desarrollo)
            $user->setIsVerified(true);

            // 5) Persistimos el usuario
            $entityManager->persist($user);
            $entityManager->flush();

            // 6) Enviamos email de verificación
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('no-reply@pican-code.local', 'PicanCode'))
                    ->to($user->getEmail())
                    ->subject('Por favor confirma tu email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => true]);
            }

            // 7) Auto-login y redirección
            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        if ($request->isXmlHttpRequest()) {
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $errors[] = $error->getMessage();
            }
            return new JsonResponse([
                'success' => false,
                'message' => implode(', ', $errors)
            ], Response::HTTP_BAD_REQUEST);
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $e) {
            $this->addFlash('verify_email_error', $translator->trans(
                $e->getReason(), [], 'VerifyEmailBundle'
            ));

            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', 'Tu email ha sido verificado correctamente.');

        return $this->redirectToRoute('post_index');
    }
}
