<?php
// src/Controller/PanelController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class PanelController extends AbstractController
{
    #[Route(path: '/gestion/panel', name: 'gestion_panel')]
    public function panel(): Response
    {
        // Sólo entra si tiene ROLE_ADMIN
        return $this->render('gestion/panel.html.twig');
    }
}
