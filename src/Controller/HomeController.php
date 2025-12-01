<?php

namespace App\Controller;

use App\Repository\JobOfferRepository;
use App\Repository\UserRepository;
use App\Repository\CandidateProfileRepository;
use App\Repository\SkillRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        UserRepository $userRepository,
        JobOfferRepository $jobOfferRepository,
        CandidateProfileRepository $candidateProfileRepository,
        SkillRepository $skillRepository
    ): Response {
        return $this->render('home/index.html.twig', [
            'employeeCount' => $userRepository->count([]),
            'jobOfferCount' => $jobOfferRepository->count([]),
            'candidateCount' => $candidateProfileRepository->count([]),
            'skillCount' => $skillRepository->count([]),
            'recentJobOffers' => $jobOfferRepository->findBy([], ['createdAt' => 'DESC'], 6),
            'recentCandidates' => $candidateProfileRepository->findBy([], ['createdAt' => 'DESC'], 6),
        ]);
    }
}
