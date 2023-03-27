<?php

namespace App\Controller;

use App\Service\AppService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MenuController extends AbstractController
{
    private $appService ;
    public function __construct(AppService $appService)
    {
        $this->appService = $appService ;
    }
    
    /**
     * @Route("/menu", name="app_menu")
     */
    public function index(): Response
    {
        $allowUrl = $this->appService->checkUrl() ;
        if(!$allowUrl)
        {
            $url = $this->generateUrl('app_login');
            return new RedirectResponse($url);
        }


        return $this->render('menu/index.html.twig', [
            'controller_name' => 'MenuController',
        ]);
    
    }
}
