<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, Response, JsonResponse};
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
        $posts = $view === 'following'
            ? $repo->findByFollowing($this->getUser())
            : $repo->findRecent()
        ;

        $mapped = array_map(fn($p) => [
            'id'        => $p->getId(),
            'title'     => $p->getTitle(),
            'snippet'   => $p->getContent(),
            'media'     => $p->getMedia(),
            'author'    => $p->getUser()->getUsername(),
            'authorId'  => $p->getUser()->getId(),
            'createdAt' => $p->getCreatedAt()->format('Y-m-d'),
            'likes'     => count($p->getLikes()),
            'comments'     => count($p->getComments()),
            'avatar'    => $p->getUser()->getAvatar(),
        ], $posts);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'view'  => $view,
                'posts' => $mapped,
            ]);
        }

        return $this->render('post/index.html.twig', [
            'data'   => ['view' => $view, 'posts' => $mapped],
            'apiUrl' => $this->generateUrl('post_index'),
        ]);
    }

    #[Route('/new', name: 'post_new', methods: ['GET','POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
{
    if ($request->isXmlHttpRequest()) {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post, [
            'csrf_protection'   => false,
            'allow_extra_fields'=> true,
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

        $post
            ->setUser($this->getUser())
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime())
            ->setIsPublished(true)
        ;

        if ($mediaFile = $form->get('media')->getData()) {
            $newFilename = uniqid().'.'.$mediaFile->guessExtension();
            $mediaFile->move(
                $this->getParameter('kernel.project_dir').'/public/uploads/posts',
                $newFilename
            );
            $post->setMedia($newFilename);
        }

        $em->persist($post);
        $em->flush();

        $mediaUrl = $post->getMedia()
            ? $request->getSchemeAndHttpHost().'/uploads/posts/'.$post->getMedia()
            : null;

        $data = [
            'id'          => $post->getId(),
            'title'       => $post->getTitle(),
            'content'     => $post->getContent(),
            'mediaUrl'    => $mediaUrl,
            'isPublished' => $post->getIsPublished(),
            'createdAt'   => $post->getCreatedAt()->format(\DateTime::ATOM),
            'updatedAt'   => $post->getUpdatedAt()->format(\DateTime::ATOM),
            'user'        => [
                'id'       => $post->getUser()->getId(),
                'username' => $post->getUser()->getUserIdentifier(),
            ],
        ];

        return $this->json([
            'success'     => true,
            'post'        => $data,
            'redirectUrl' => $this->generateUrl('user_profile', [
                'id' => $post->getUser()->getId(),
            ]),
        ], 201);
    }

    return $this->render('post/new.html.twig', [
        'data'   => [
            'title'   => '',
            'content' => '',
        ],
        'apiUrl' => $this->generateUrl('post_new'),
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
