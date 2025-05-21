<?php
// src/Controller/FollowController.php
namespace App\Controller;

use App\Entity\User;
use App\Entity\Follower;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class FollowController extends AbstractController
{
    #[Route('/usuario/{id}/follow', name: 'user_follow', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function toggleFollow(
        User $toFollow,
        Request $request,
        EntityManagerInterface $em
    ): RedirectResponse {
        /** @var User|null $me */
        $me = $this->getUser();

        if (!$me) {
            return $this->redirectToRoute('app_login');
        }

        if ($me->getId() === $toFollow->getId()) {
            $this->addFlash('warning', 'No puedes seguirte a ti mismo.');
            return $this->redirectToRoute('user_profile', ['id' => $toFollow->getId()]);
        }

        $intent = $em->getRepository(Follower::class)->findOneBy([
            'follower' => $me,
            'followed' => $toFollow,
        ]) ? 'unfollow' : 'follow';

        if (!$this->isCsrfTokenValid($intent.$toFollow->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token inválido, inténtalo de nuevo.');
            return $this->redirectToRoute('user_profile', ['id' => $toFollow->getId()]);
        }

        $repo = $em->getRepository(Follower::class);
        if ($existing = $repo->findOneBy(['follower' => $me, 'followed' => $toFollow])) {
            $em->remove($existing);
            $this->addFlash('warning', 'Has dejado de seguir a '.$toFollow->getUsername());
        } else {
            $f = new Follower();
            $f->setFollower($me)
              ->setFollowed($toFollow);
            $em->persist($f);
            $this->addFlash('success', 'Ahora sigues a '.$toFollow->getUsername());
        }

        $em->flush();

        return $this->redirectToRoute('user_profile', ['id' => $toFollow->getId()]);
    }
}
