<?php
namespace App\Controller\Gestion;

use App\Entity\Post;
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request,Response,RedirectResponse};
use Symfony\Component\Routing\Annotation\Route;

#[Route('/gestion/post', name: 'gestion_post_')]
class PostController extends AbstractController
{
    #[Route('/', name:'index')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $idFilter    = $request->query->getInt('id', 0);
        $titleFilter = $request->query->get('title', '');

        $qb = $em->getRepository(Post::class)->createQueryBuilder('p')
            ->orderBy('p.id', 'DESC');

        if ($idFilter > 0) {
            $qb->andWhere('p.id = :id')
               ->setParameter('id', $idFilter);
        }
        if ($titleFilter !== '') {
            $qb->andWhere('p.title LIKE :title')
               ->setParameter('title', '%'.$titleFilter.'%');
        }

        $posts = $qb->getQuery()->getResult();

        return $this->render('gestion/post/index.html.twig', [
            'posts'       => $posts,
            'id_filter'   => $idFilter,
            'title_filter'=> $titleFilter,
        ]);
    }

    #[Route('/{id}/edit', name:'edit', methods:['GET','POST'])]
    public function edit(Post $post, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $media = $form->get('media')->getData();
            if ($media) {
                $filename = uniqid().'.'.$media->guessExtension();
                $media->move($this->getParameter('posts_directory'), $filename);
                $post->setMedia($filename);
            }

            $em->flush();
            $this->addFlash('success','Post actualizado correctamente.');
            return $this->redirectToRoute('gestion_post_index');
        }

        return $this->render('gestion/post/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name:'delete', methods:['POST'])]
    public function delete(Post $post, Request $request, EntityManagerInterface $em): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete_post'.$post->getId(), $request->request->get('_token'))) {
            $em->remove($post);
            $em->flush();
            $this->addFlash('warning','Post eliminado.');
        }

        return $this->redirectToRoute('gestion_post_index');
    }
}
