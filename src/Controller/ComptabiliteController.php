<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ComptabiliteController extends AbstractController
{
    #[Route('/comptabilite', name: 'app_comptabilite')]
    public function index(): Response
    {
        return $this->render('comptabilite/index.html.twig', [
            'controller_name' => 'ComptabiliteController',
        ]);
    }
}
