<?php
namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    #[Route('/post/{id}/comment', name: 'post_comment', methods: ['GET','POST'])]
    public function comment(Post $post, Request $request, EntityManagerInterface $em): Response
    {
        $comment = new Comment();
        $form    = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment
                ->setUser($this->getUser())
                ->setPost($post)
                ->setCreatedAt(new \DateTime())
            ;
            $em->persist($comment);
            $em->flush();
            return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
        }

        return $this->render('comment/new.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
        ]);
    }
}
