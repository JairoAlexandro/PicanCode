<?php
namespace App\Controller;

use App\Entity\Follower;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class FollowController extends AbstractController
{
    #[Route('/user/{id}/follow', name: 'user_follow', methods: ['POST'])]
    public function toggleFollow(User $toFollow, EntityManagerInterface $em, Security $security): Response
    {
        $me   = $security->getUser();
        $repo = $em->getRepository(Follower::class);
        $ex   = $repo->findOneBy(['follower' => $me, 'followed' => $toFollow]);

        if ($ex) {
            $em->remove($ex);
        } else {
            $f = new Follower();
            $f
                ->setFollower($me)
                ->setFollowed($toFollow)
                ->setCreatedAt(new \DateTime())
            ;
            $em->persist($f);
        }
        $em->flush();

        return $this->redirectToRoute('user_profile', ['id' => $toFollow->getId()]);
    }
}
