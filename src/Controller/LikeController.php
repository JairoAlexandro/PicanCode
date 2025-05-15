<?php
namespace App\Controller;

use App\Entity\Like;
use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class LikeController extends AbstractController
{
    #[Route('/post/{id}/like', name: 'post_like', methods: ['POST'])]
    public function toggleLike(Post $post, EntityManagerInterface $em, Security $security): Response
    {
        $user = $security->getUser();
        $repo = $em->getRepository(Like::class);
        $existing = $repo->findOneBy(['post' => $post, 'user' => $user]);

        if ($existing) {
            $em->remove($existing);
        } else {
            $like = new Like();
            $like
                ->setPost($post)
                ->setUser($user)
                ->setCreatedAt(new \DateTime())
            ;
            $em->persist($like);
        }
        $em->flush();

        return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
    }
}
