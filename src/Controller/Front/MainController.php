<?php

namespace App\Controller\Front;

use App\Repository\RequestRepository;
use App\Repository\PropositionRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MainController extends AbstractController
{
    /**
     * @Route("/front/requests", name="front_requests", methods={"GET"})
     */
    public function browseRequests(RequestRepository $requestRepository): Response
    {
        $requests = $requestRepository->findAllOrderedByIdDesc();
        return $this->render('front/main/request.html.twig', [
            'requests' => $requests,
        ]);
    }

    /**
     * @Route("/front/propositions", name="front_propositions", methods={"GET"})
     */
    public function browsePropositions(PropositionRepository $propositionRepository): Response
    {
        $propositions = $propositionRepository->findAllOrderedByIdDesc();
        return $this->render('front/main/proposition.html.twig', [
            'propositions' => $propositions,
        ]);
    }
}
