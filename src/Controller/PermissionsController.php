<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PermissionsController extends AbstractController
{
    #[Route('/permissions', name: 'app_permissions')]
    public function index(): Response
    {
        return $this->render('permissions/index.html.twig', [
            'controller_name' => 'PermissionsController',
        ]);
    }
}
