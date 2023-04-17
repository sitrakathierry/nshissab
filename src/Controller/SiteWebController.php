<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SiteWebController extends AbstractController
{
    #[Route('/site/web', name: 'app_site_web')]
    public function index(): Response
    {
        return $this->render('site_web/index.html.twig', [
            'controller_name' => 'SiteWebController',
        ]);
    }
}
