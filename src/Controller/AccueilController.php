<?php

namespace App\Controller;

use App\Service\AppService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class AccueilController extends AbstractController
{
    public function __construct(AppService $appService)
    {
        // $appService->checkUrl() ;
    }

    /**
     * @Route("/", name="app_accueil")
     */
    public function index(): Response
    {
        // thanks to the type-hint, the container will instantiate a
        // new MessageGenerator and pass it to you!
        // // ...

        // $message = $appService->getHappyMessage();
        // $this->addFlash('success', $message);
        // // ...

        // // $ses = $this->get('session')->getFlash('success') ;

        // // retrieve the flash bag from the session
        // $flashBag = $this->get('session')->getFlashBag();

        // // retrieve the success flash messages
        // $successMessages = $flashBag->get('success');

        // return $this->render('accueil/index.html.twig',[
        return $this->render('accueil/accueil.html.twig',[
            // 'messageFlash' => $successMessages
        ]);
    }
}
