<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\CommentType;

#[Route('/posts')]
class PostController extends AbstractController
{
    #[Route('', name: 'post_index', methods: ['GET'])]
    public function index(Request $request, PostRepository $repo): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
    }

        $view = $request->query->get('view', 'recent');

        if ($view === 'following') {
            $posts = $repo->findByFollowing($this->getUser());
        } else {
            $posts = $repo->findRecent();
        }

        return $this->render('post/index.html.twig', [
            'posts' => $posts,
            'view'  => $view,
        ]);
    }

    #[Route('/new', name: 'post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $post
                ->setUser($this->getUser())
                ->setCreatedAt(new \DateTime())
                ->setUpdatedAt(new \DateTime())
                ->setIsPublished(true)
            ;
            $mediaFile = $form->get('media')->getData();
            if ($mediaFile) {
                $newFilename = uniqid().'.'.$mediaFile->guessExtension();
                $mediaFile->move($this->getParameter('kernel.project_dir').'/public/uploads/posts', $newFilename);
                $post->setMedia($newFilename);
            }
            $em->persist($post);
            $em->flush();

            return $this->redirectToRoute('user_profile', [
                'id' => $post->getUser()->getId(),
            ]);
        }

        return $this->render('post/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        $commentForm = $this->createForm(CommentType::class)->createView();
        return $this->render('post/show.html.twig', [
            'post' => $post,
            'commentForm' => $commentForm,
        ]);
    }

    #[Route('/{id}/edit', name: 'post_edit', methods: ['GET', 'POST'])]
    public function edit(Post $post, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $post->setUpdatedAt(new \DateTime());
            $post->setIsPublished(true);
            $mediaFile = $form->get('media')->getData();
            if ($mediaFile) {
                $newFilename = uniqid().'.'.$mediaFile->guessExtension();
                $mediaFile->move($this->getParameter('kernel.project_dir').'/public/uploads/posts', $newFilename);
                $post->setMedia($newFilename);
            }
            $em->flush();

            return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
        }

        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'post_delete', methods: ['POST'])]
    public function delete(Post $post, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $userId = $post->getUser()->getId();
            $em->remove($post);
            $em->flush();

            return $this->redirectToRoute('user_profile', [
                'id' => $userId,
            ]);
        }

        return $this->redirectToRoute('post_index');
    }
}
