<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CreditController extends AbstractController
{
    #[Route('/credit', name: 'app_credit')]
    public function index(): Response
    {
        return $this->render('credit/index.html.twig', [
            'controller_name' => 'CreditController',
        ]);
    }
}
