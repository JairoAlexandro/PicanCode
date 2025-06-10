<?php

namespace App\Controller\Front\User;

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
           
            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            /** @var UploadedFile|null $avatarFile */
            $avatarFile = $form->get('avatar')->getData();
            if ($avatarFile instanceof UploadedFile) {
                $newFilename = uniqid() . '.' . $avatarFile->guessExtension();
                try {
                    $avatarFile->move($this->avatarsDir, $newFilename);
                    $user->setAvatar($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'No se pudo subir el avatar.');
                }
            }

            $user->setUpdatedAt(new \DateTime());
            $user->setIsVerified(true);
            $entityManager->persist($user);
            $entityManager->flush();

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
