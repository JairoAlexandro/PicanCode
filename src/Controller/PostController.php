<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Like;
use App\Entity\Comment;
use App\Form\PostType;
use App\Form\CommentType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, Response, JsonResponse};
use Symfony\Component\Routing\Annotation\Route;

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
            'author'    => $p->getUser()->getUserIdentifier(),
            'authorId'  => $p->getUser()->getId(),
            'createdAt' => $p->getCreatedAt()->format('Y-m-d'),
            'likes'     => count($p->getLikes()),
            'comments'  => count($p->getComments()),
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

    #[Route('/{id}', name: 'post_show', methods: ['GET', 'POST'])]
    public function show(Request $request, Post $post, EntityManagerInterface $em): Response
    {
        if ($request->isXmlHttpRequest()) {
            $user = $this->getUser();

            if ($request->isMethod('GET')) {
                $comments = [];
                foreach ($post->getComments() as $commentEntity) {
                    $comments[] = [
                        'id'        => $commentEntity->getId(),
                        'content'   => $commentEntity->getContent(),
                        'author'    => $commentEntity->getUser()?->getUserIdentifier() ?? 'Anónimo',
                        'createdAt' => $commentEntity->getCreatedAt()?->format('Y-m-d H:i:s'),
                    ];
                }

                $userLiked = $user
                    ? $post->getLikes()->exists(fn($key, $like) => $like->getUser() === $user)
                    : false;

                return $this->json([
                    'data' => [
                        'id'                  => $post->getId(),
                        'title'               => $post->getTitle(),
                        'content'             => $post->getContent(),
                        'media'               => $post->getMedia(),
                        'author'              => $post->getUser()?->getUserIdentifier(),
                        'authorId'            => $post->getUser()?->getId(),
                        'createdAt'           => $post->getCreatedAt()?->format('Y-m-d H:i:s'),
                        'likes'               => count($post->getLikes()),
                        'avatar'              => $post->getUser()->getAvatar(),
                        'likedByCurrentUser'  => $userLiked,
                        'comments'            => $comments,
                    ],
                    'apiUrl' => $this->generateUrl('post_show', ['id' => $post->getId()]),
                ]);
            }

            if ($request->isMethod('POST')) {
                if (!$user) {
                    return $this->json(['success' => false, 'error' => 'No autenticado'], 401);
                }

                $payload = json_decode($request->getContent(), true);

                if (isset($payload['content'])) {
                    if (empty($payload['content'])) {
                        return $this->json(['success' => false, 'error' => 'El comentario no puede estar vacío'], 400);
                    }

                    $commentEntity = new Comment();
                    $commentEntity->setContent($payload['content']);
                    $commentEntity->setPost($post);
                    $commentEntity->setUser($user);
                    $commentEntity->setCreatedAt(new \DateTime());

                    $em->persist($commentEntity);
                    $em->flush();

                    return $this->json([
                        'success' => true,
                        'comment' => [
                            'id'        => $commentEntity->getId(),
                            'content'   => $commentEntity->getContent(),
                            'author'    => $user->getUserIdentifier(),
                            'createdAt' => $commentEntity->getCreatedAt()->format('Y-m-d H:i:s'),
                        ]
                    ], 201);
                }

                $existingLike = $post->getLikes()->filter(fn($like) => $like->getUser() === $user)->first();

                if ($existingLike) {
                    $em->remove($existingLike);
                    $em->flush();
                    return $this->json(['success' => true, 'liked' => false]);
                }

                $likeEntity = new Like();
                $likeEntity->setPost($post);
                $likeEntity->setUser($user);

                $em->persist($likeEntity);
                $em->flush();

                return $this->json(['success' => true, 'liked' => true]);
            }
        }

        $comments = [];
        foreach ($post->getComments() as $commentEntity) {
            $comments[] = [
                'id'        => $commentEntity->getId(),
                'content'   => $commentEntity->getContent(),
                'author'    => $commentEntity->getUser()?->getUserIdentifier() ?? 'Anónimo',
                'createdAt' => $commentEntity->getCreatedAt()?->format('Y-m-d H:i:s'),
            ];
        }

        $user = $this->getUser();
        $userLiked = $user
            ? $post->getLikes()->exists(fn($key, $like) => $like->getUser() === $user)
            : false;

        $data = [
            'id'                  => $post->getId(),
            'title'               => $post->getTitle(),
            'content'             => $post->getContent(),
            'media'               => $post->getMedia(),
            'author'              => $post->getUser()?->getUserIdentifier(),
            'authorId'            => $post->getUser()?->getId(),
            'createdAt'           => $post->getCreatedAt()?->format('Y-m-d H:i:s'),
            'likes'               => count($post->getLikes()),
            'avatar'              => $post->getUser()->getAvatar(),
            'likedByCurrentUser'  => $userLiked,
            'comments'            => $comments,
        ];

        return $this->render('post/show.html.twig', [
            'data'   => $data,
            'apiUrl' => $this->generateUrl('post_show', ['id' => $post->getId()]),
        ]);
    }

    #[Route('/{id}', name: 'post_delete', methods: ['DELETE'])]
    public function deleteFromApi(Post $post, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();

        if (!$user || $user !== $post->getUser()) {
            return $this->json(['success' => false, 'error' => 'No autorizado'], 403);
        }

        $em->remove($post);
        $em->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/{id}/edit', name: 'post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post, EntityManagerInterface $em): Response
    {
        
        if ($request->isXmlHttpRequest()) {
            if ($request->isMethod('GET')) {
                $mediaUrl = null;
                if ($post->getMedia()) {
                    $mediaUrl = $request->getSchemeAndHttpHost() . '/uploads/posts/' . $post->getMedia();
                }

                return $this->json([
                    'data' => [
                        'id'      => $post->getId(),
                        'title'   => $post->getTitle(),
                        'content' => $post->getContent(),
                        'media'   => $mediaUrl,
                    ],
                    'apiUrl' => $this->generateUrl('post_edit', ['id' => $post->getId()]),
                ]);
            }

            $form = $this->createForm(PostType::class, $post, [
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

            $post->setUpdatedAt(new \DateTime());
            $post->setIsPublished(true);

            if ($mediaFile = $form->get('media')->getData()) {
                $newFilename = uniqid() . '.' . $mediaFile->guessExtension();
                $mediaFile->move(
                    $this->getParameter('kernel.project_dir') . '/public/uploads/posts',
                    $newFilename
                );
                $post->setMedia($newFilename);
            }

            $em->flush();

            return $this->json([
                'success'     => true,
                'redirectUrl' => $this->generateUrl('post_show', ['id' => $post->getId()]),
            ], 200);
        }

        $data = [
            'id'      => $post->getId(),
            'title'   => $post->getTitle(),
            'content' => $post->getContent(),
            'media'   => null,
        ];
        $apiUrl = $this->generateUrl('post_edit', ['id' => $post->getId()]);

        return $this->render('post/edit.html.twig', [
            'data'   => $data,
            'apiUrl' => $apiUrl,
        ]);
    }

    #[Route('/{id}/delete', name: 'post_delete_form', methods: ['POST'])]
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
