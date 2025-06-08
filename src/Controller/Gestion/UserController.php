<?php
namespace App\Controller\Gestion;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, Response, RedirectResponse};
use Symfony\Component\Routing\Annotation\Route;

#[Route('/gestion/user', name: 'gestion_user_')]
class UserController extends AbstractController
{
    #[Route('/', name:'index')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $idFilter   = $request->query->getInt('id', 0);
        $nameFilter = $request->query->get('username', '');

        $qb = $em->getRepository(User::class)->createQueryBuilder('u')
            ->orderBy('u.id', 'DESC');

        if ($idFilter > 0) {
            $qb->andWhere('u.id = :id')
               ->setParameter('id', $idFilter);
        }
        if ($nameFilter !== '') {
            $qb->andWhere('u.username LIKE :username')
               ->setParameter('username', '%'.$nameFilter.'%');
        }

        $users = $qb->getQuery()->getResult();

        return $this->render('gestion/user/index.html.twig', [
            'users'           => $users,
            'id_filter'       => $idFilter,
            'username_filter' => $nameFilter,
        ]);
    }

    #[Route('/{id}/edit', name:'edit', methods:['GET','POST'])]
    public function edit(User $user, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(UserType::class, $user);

        if ($form->has('roles')) {
            $form->remove('roles');
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Usuario actualizado correctamente.');
            return $this->redirectToRoute('gestion_user_index');
        }

        return $this->render('gestion/user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name:'delete', methods:['POST'])]
    public function delete(User $user, Request $request, EntityManagerInterface $em): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete_user'.$user->getId(), $request->request->get('_token'))) {
            $em->remove($user);
            $em->flush();
            $this->addFlash('warning', 'Usuario eliminado.');
        }

        return $this->redirectToRoute('gestion_user_index');
    }
}
