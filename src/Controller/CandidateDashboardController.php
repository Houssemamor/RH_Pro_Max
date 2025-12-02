<?php

namespace App\Controller;

use App\Entity\Candidate;
use App\Entity\CV;
use App\Form\CVType;
use App\Repository\CandidateProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/candidate')]
#[IsGranted('ROLE_CANDIDATE')]
class CandidateDashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_candidate_dashboard', methods: ['GET'])]
    public function dashboard(CandidateProfileRepository $candidateProfileRepository): Response
    {
        $candidate = $this->getUser();

        if (!$candidate instanceof Candidate) {
            throw $this->createAccessDeniedException('Only candidates can access this page.');
        }

        // Get all applications (CandidateProfiles) for this candidate
        $applications = $candidateProfileRepository->findBy(
            ['candidate' => $candidate],
            ['createdAt' => 'DESC']
        );

        return $this->render('candidate_dashboard/index.html.twig', [
            'candidate' => $candidate,
            'applications' => $applications,
        ]);
    }

    #[Route('/application/{id}', name: 'app_candidate_application_show', methods: ['GET'])]
    public function showApplication(int $id, CandidateProfileRepository $candidateProfileRepository): Response
    {
        $candidate = $this->getUser();

        if (!$candidate instanceof Candidate) {
            throw $this->createAccessDeniedException('Only candidates can access this page.');
        }

        $application = $candidateProfileRepository->find($id);

        if (!$application || $application->getCandidate() !== $candidate) {
            throw $this->createNotFoundException('Application not found.');
        }

        return $this->render('candidate_dashboard/show_application.html.twig', [
            'application' => $application,
        ]);
    }

    #[Route('/application/{id}/upload-cv', name: 'app_candidate_upload_cv', methods: ['GET', 'POST'])]
    public function uploadCv(
        int $id,
        Request $request,
        CandidateProfileRepository $candidateProfileRepository,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        string $cvUploadDirectory
    ): Response {
        $candidate = $this->getUser();

        if (!$candidate instanceof Candidate) {
            throw $this->createAccessDeniedException('Only candidates can access this page.');
        }

        $application = $candidateProfileRepository->find($id);

        if (!$application || $application->getCandidate() !== $candidate) {
            throw $this->createNotFoundException('Application not found.');
        }

        $cv = new CV();
        $form = $this->createForm(CVType::class, $cv);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cvFile = $form->get('cvFile')->getData();

            if ($cvFile) {
                $originalFilename = pathinfo($cvFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $cvFile->guessExtension();

                try {
                    // Create directory if it doesn't exist
                    if (!is_dir($cvUploadDirectory)) {
                        mkdir($cvUploadDirectory, 0755, true);
                    }

                    $cvFile->move($cvUploadDirectory, $newFilename);

                    $cv->setFilePath($newFilename);
                    $cv->setCandidateProfile($application);
                    $cv->setUploadedAt(new \DateTime());

                    $entityManager->persist($cv);
                    $entityManager->flush();

                    $this->addFlash('success', 'CV uploaded successfully!');

                    return $this->redirectToRoute('app_candidate_application_show', ['id' => $id]);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Failed to upload CV. Please try again.');
                }
            }
        }

        return $this->render('candidate_dashboard/upload_cv.html.twig', [
            'application' => $application,
            'form' => $form,
        ]);
    }

    #[Route('/cv/{id}/delete', name: 'app_candidate_delete_cv', methods: ['POST'])]
    public function deleteCv(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        string $cvUploadDirectory
    ): Response {
        $candidate = $this->getUser();

        if (!$candidate instanceof Candidate) {
            throw $this->createAccessDeniedException('Only candidates can access this page.');
        }

        $cv = $entityManager->getRepository(CV::class)->find($id);

        if (!$cv || $cv->getCandidateProfile()->getCandidate() !== $candidate) {
            throw $this->createNotFoundException('CV not found.');
        }

        $applicationId = $cv->getCandidateProfile()->getId();

        if ($this->isCsrfTokenValid('delete-cv' . $cv->getId(), $request->getPayload()->getString('_token'))) {
            // Delete the file
            $filePath = $cvUploadDirectory . '/' . $cv->getFilePath();
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $entityManager->remove($cv);
            $entityManager->flush();

            $this->addFlash('success', 'CV deleted successfully!');
        }

        return $this->redirectToRoute('app_candidate_application_show', ['id' => $applicationId]);
    }
}
