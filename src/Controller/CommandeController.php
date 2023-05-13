<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommandeController extends AbstractController
{
    #[Route('/commande/creation', name: 'cmd_creation')]
    public function index(): Response
    {
        

        return $this->render('commande/creation.html.twig', [
            "filename" => "commande",
            "titlePage" => "Création bon de commande",
            "with_foot" => true,
        ]);
    }


}
