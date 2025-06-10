<?php

namespace App\Controller\Front\Post;

use App\Dto\PostDto;
use App\Entity\Post;
use App\Form\PostType;
use App\Service\MediaUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, Response, JsonResponse};
use Symfony\Component\Routing\Annotation\Route;

#[Route('/posts')]
class PostController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MediaUploader $mediaUploader
    ) {}

    #[Route('', name: 'post_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $view  = $request->query->get('view', 'recent');
        $repo  = $this->em->getRepository(Post::class);
        $posts = $view === 'following'
            ? $repo->findByFollowing($this->getUser())
            : $repo->findRecent();

        $dtos = array_map(fn(Post $p) => PostDto::fromPost($p, $this->getUser()), $posts);

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'view'  => $view,
                'posts' => $dtos,
            ]);
        }

        return $this->render('post/index.html.twig', [
            'data'   => ['view'  => $view, 'posts' => $dtos],
            'apiUrl' => $this->generateUrl('post_index'),
        ]);
    }

    #[Route('/new', name: 'post_new', methods: ['GET','POST'])]
    public function new(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->render('post/new.html.twig', [
                'data'   => ['title' => '', 'content' => ''],
                'apiUrl' => $this->generateUrl('post_new'),
            ]);
        }

        $post = new Post();
        $form = $this->createForm(PostType::class, $post, [
            'csrf_protection'    => false,
            'allow_extra_fields' => true,
        ]);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $errors = array_map(fn($e) => $e->getMessage(), iterator_to_array($form->getErrors(true)));
            return $this->json(['success' => false, 'errors' => $errors], 400);
        }

        $post->setUser($this->getUser())
             ->setCreatedAt(new \DateTime())
             ->setUpdatedAt(new \DateTime())
             ->setIsPublished(true);

        if ($mediaFile = $form->get('media')->getData()) {
            $filename = $this->mediaUploader->upload($mediaFile);
            $post->setMedia($filename);
        }

        $this->em->persist($post);
        $this->em->flush();

        $dto = PostDto::fromPost($post, $this->getUser());

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (! $user) {
            throw $this->createAccessDeniedException();
        }
        return $this->json([
            'success'=> true,
            'post'=> $dto,
            'redirectUrl' => $this->generateUrl('user_profile', [
            'id'=> $user->getId(),
    ]),
], 201);
    }

    #[Route('/{id}', name: 'post_show', methods: ['GET', 'POST'])]
    public function show(Request $request, Post $post): Response
    {
        if ($request->isXmlHttpRequest()) {
            if ($request->isMethod('GET')) {
                $dto = PostDto::fromPost($post, $this->getUser());
                return $this->json([
                    'data'   => $dto,
                    'apiUrl' => $this->generateUrl('post_show', ['id' => $post->getId()]),
                ]);
            }

            $user = $this->getUser();
            if (!$user) {
                return $this->json(['success' => false, 'error' => 'No autenticado'], 401);
            }

            $data = json_decode($request->getContent(), true);
            if (isset($data['content'])) {
                if (empty(trim($data['content']))) {
                    return $this->json(['success' => false, 'error' => 'El comentario no puede estar vacío'], 400);
                }
                $comment = new \App\Entity\Comment();
                $comment->setContent($data['content'])
                        ->setPost($post)
                        ->setUser($user)
                        ->setCreatedAt(new \DateTime());
                $this->em->persist($comment);
                $this->em->flush();
                $new = [
                    'id'        => $comment->getId(),
                    'content'   => $comment->getContent(),
                    'author'    => $user->getUserIdentifier(),
                    'createdAt' => $comment->getCreatedAt()->format('Y-m-d H:i:s'),
                ];

                return $this->json(['success' => true,'comment' => $new,], 201);
            }

            $exist = $post->getLikes()->filter(fn($like) => $like->getUser() === $user)->first();
            if ($exist) {
                $this->em->remove($exist);
                $this->em->flush();
                return $this->json(['success' => true, 'liked' => false]);
            }

            $like = new \App\Entity\Like();
            $like->setPost($post)->setUser($user);
            $this->em->persist($like);
            $this->em->flush();
            return $this->json(['success' => true, 'liked' => true]);
        }

        $dto = PostDto::fromPost($post, $this->getUser());
        return $this->render('post/show.html.twig', [
            'data'   => $dto,
            'apiUrl' => $this->generateUrl('post_show', ['id' => $post->getId()]),
        ]);
    }

    #[Route('/{id}', name: 'post_delete', methods: ['DELETE'])]
    public function deleteFromApi(Post $post): JsonResponse
    {
        $user = $this->getUser();
        if (!$user || $user !== $post->getUser()) {
            return $this->json(['success' => false, 'error' => 'No autorizado'], 403);
        }
        $this->em->remove($post);
        $this->em->flush();
        return $this->json(['success' => true]);
    }

    #[Route('/{id}/edit', name: 'post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post): Response
    {
        if ($request->isXmlHttpRequest()) {
            if ($request->isMethod('GET')) {
                $dto = [
                    'id'      => $post->getId(),
                    'title'   => $post->getTitle(),
                    'content' => $post->getContent(),
                    'media'   => $post->getMedia()
                        ? $request->getSchemeAndHttpHost().'/uploads/posts/'.$post->getMedia()
                        : null,
                ];
                return $this->json(['data' => $dto, 'apiUrl' => $this->generateUrl('post_edit', ['id' => $post->getId()])]);
            }

            $form = $this->createForm(PostType::class, $post, ['csrf_protection'=>false,'allow_extra_fields'=>true]);
            $form->handleRequest($request);
            if (!$form->isSubmitted() || !$form->isValid()) {
                $errors = array_map(fn($e) => $e->getMessage(), iterator_to_array($form->getErrors(true)));
                return $this->json(['success'=>false,'errors'=>$errors],400);
            }

            $post->setUpdatedAt(new \DateTime())->setIsPublished(true);
            if ($mf = $form->get('media')->getData()) {
                $fn = $this->mediaUploader->upload($mf);
                $post->setMedia($fn);
            }
            $this->em->flush();
            return $this->json(['success'=>true,'redirectUrl'=>$this->generateUrl('post_show',['id'=>$post->getId()])],200);
        }

        return $this->render('post/edit.html.twig', [
            'data'   => ['id'=>$post->getId(),'title'=>$post->getTitle(),'content'=>$post->getContent(),'media'=>null],
            'apiUrl' => $this->generateUrl('post_edit',['id'=>$post->getId()]),
        ]);
    }

    #[Route('/{id}/delete', name: 'post_delete_form', methods: ['POST'])]
    public function delete(Post $post, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $userId = $post->getUser()->getId();
            $this->em->remove($post);
            $this->em->flush();
            return $this->redirectToRoute('user_profile',['id'=>$userId]);
        }
        return $this->redirectToRoute('post_index');
    }
}
