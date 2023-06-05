<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AgendaController extends AbstractController
{
    #[Route('/agenda/creation', name: 'agd_agenda_creation')]
    public function agdCreationAgenda(): Response
    {
        return $this->render('agenda/creation.html.twig', [
            "filename" => "agenda",
            "titlePage" => "Creation agenda",
            "with_foot" => true,
        ]);
    }

    #[Route('/agenda/consultation', name: 'agd_agenda_consultation')]
    public function agdConsultationAgenda(): Response
    {
        return $this->render('agenda/consultation.html.twig', [
            "filename" => "agenda",
            "titlePage" => "Consultation agenda",
            "with_foot" => false,
        ]);
    }
}
