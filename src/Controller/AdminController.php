<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Post;
use App\Entity\Comment;
use App\Entity\Category;
use App\Form\PostType;
use App\Form\CommentType;
use App\Form\CategoryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/gestion')]
class AdminController extends AbstractController
{
    private function isAdminLoggedIn(Request $request): bool
    {
        return $request->getSession()->get('is_admin', false) === true;
    }

    #[Route('', name: 'admin_dashboard')]
    public function dashboard(Request $request): Response
    {
        if (!$this->isAdminLoggedIn($request)) {
            return $this->redirectToRoute('gestion_login');
        }
        return $this->render('admin/dashboard.html.twig');
    }

    #[Route('/users', name: 'admin_users')]
    public function users(Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isAdminLoggedIn($request)) {
            return $this->redirectToRoute('gestion_login');
        }
        $users = $em->getRepository(User::class)->findAll();
        return $this->render('admin/users.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/posts', name: 'admin_posts')]
    public function posts(Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isAdminLoggedIn($request)) {
            return $this->redirectToRoute('gestion_login');
        }
        $posts = $em->getRepository(Post::class)->findAll();
        return $this->render('admin/posts.html.twig', [
            'posts' => $posts
        ]);
    }

    #[Route('/posts/new', name: 'admin_post_new')]
    #[Route('/posts/{id}/edit', name: 'admin_post_edit')]
    public function postEdit(Request $request, EntityManagerInterface $em, ?Post $post = null): Response
    {
        if (!$this->isAdminLoggedIn($request)) {
            return $this->redirectToRoute('gestion_login');
        }
        $post = $post ?? new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$post->getId()) {
                $post->setUser($this->getUser());
                $post->setCreatedAt(new \DateTime());
            }
            $post->setUpdatedAt(new \DateTime());
            $em->persist($post);
            $em->flush();
            $this->addFlash('success', 'Publicación guardada correctamente');
            return $this->redirectToRoute('admin_posts');
        }

        return $this->render('admin/post_edit.html.twig', [
            'post' => $post,
            'form' => $form->createView()
        ]);
    }

    #[Route('/comments', name: 'admin_comments')]
    public function comments(Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isAdminLoggedIn($request)) {
            return $this->redirectToRoute('gestion_login');
        }
        $comments = $em->getRepository(Comment::class)->findAll();
        return $this->render('admin/comments.html.twig', [
            'comments' => $comments
        ]);
    }

    #[Route('/comments/{id}/edit', name: 'admin_comment_edit')]
    public function commentEdit(Request $request, EntityManagerInterface $em, Comment $comment): Response
    {
        if (!$this->isAdminLoggedIn($request)) {
            return $this->redirectToRoute('gestion_login');
        }
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Comentario actualizado correctamente');
            return $this->redirectToRoute('admin_comments');
        }

        return $this->render('admin/comment_edit.html.twig', [
            'comment' => $comment,
            'form' => $form->createView()
        ]);
    }

    #[Route('/categories', name: 'admin_categories')]
    public function categories(Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isAdminLoggedIn($request)) {
            return $this->redirectToRoute('gestion_login');
        }
        $page = $request->query->getInt('page', 1);
        $limit = 10;
        $queryBuilder = $em->getRepository(Category::class)->createQueryBuilder('c')
            ->orderBy('c.name', 'ASC');
        $categories = $queryBuilder
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
        $totalCategories = $em->getRepository(Category::class)->count([]);
        $totalPages = ceil($totalCategories / $limit);
        return $this->render('admin/categories.html.twig', [
            'categories' => $categories,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ]);
    }

    #[Route('/categories/new', name: 'admin_category_new')]
    #[Route('/categories/{id}/edit', name: 'admin_category_edit')]
    public function categoryEdit(Request $request, EntityManagerInterface $em, ?Category $category = null): Response
    {
        if (!$this->isAdminLoggedIn($request)) {
            return $this->redirectToRoute('gestion_login');
        }
        $category = $category ?? new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($category);
            $em->flush();
            $this->addFlash('success', 'Categoría guardada correctamente');
            return $this->redirectToRoute('admin_categories');
        }

        return $this->render('admin/category_edit.html.twig', [
            'category' => $category,
            'form' => $form->createView()
        ]);
    }

    #[Route('/users/{id}/delete', name: 'admin_user_delete', methods: ['POST'])]
    public function deleteUser(Request $request, User $user, EntityManagerInterface $em): Response
    {
        if (!$this->isAdminLoggedIn($request)) {
            return $this->redirectToRoute('gestion_login');
        }
        $em->remove($user);
        $em->flush();
        $this->addFlash('success', 'Usuario eliminado correctamente');
        return $this->redirectToRoute('admin_users');
    }

    #[Route('/posts/{id}/delete', name: 'admin_post_delete', methods: ['POST'])]
    public function deletePost(Request $request, Post $post, EntityManagerInterface $em): Response
    {
        if (!$this->isAdminLoggedIn($request)) {
            return $this->redirectToRoute('gestion_login');
        }
        try {
            if ($post->getComments()->count() > 0) {
                $this->addFlash('error', 'No se puede eliminar la publicación porque tiene comentarios asociados. Por favor, elimine los comentarios primero.');
                return $this->redirectToRoute('admin_posts');
            }
            $em->remove($post);
            $em->flush();
            $this->addFlash('success', 'Publicación eliminada correctamente');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error al eliminar la publicación: ' . $e->getMessage());
        }
        return $this->redirectToRoute('admin_posts');
    }

    #[Route('/comments/{id}/delete', name: 'admin_comment_delete', methods: ['POST'])]
    public function deleteComment(Request $request, Comment $comment, EntityManagerInterface $em): Response
    {
        if (!$this->isAdminLoggedIn($request)) {
            return $this->redirectToRoute('gestion_login');
        }
        $em->remove($comment);
        $em->flush();
        $this->addFlash('success', 'Comentario eliminado correctamente');
        return $this->redirectToRoute('admin_comments');
    }

    #[Route('/categories/{id}/delete', name: 'admin_category_delete', methods: ['POST'])]
    public function deleteCategory(Request $request, Category $category, EntityManagerInterface $em): Response
    {
        if (!$this->isAdminLoggedIn($request)) {
            return $this->redirectToRoute('gestion_login');
        }
        try {
            if ($category->getPosts()->count() > 0) {
                $this->addFlash('error', 'No se puede eliminar la categoría porque tiene publicaciones asociadas. Por favor, elimine o reasigne las publicaciones primero.');
                return $this->redirectToRoute('admin_categories');
            }
            $em->remove($category);
            $em->flush();
            $this->addFlash('success', 'Categoría eliminada correctamente');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error al eliminar la categoría: ' . $e->getMessage());
        }
        return $this->redirectToRoute('admin_categories');
    }
} 