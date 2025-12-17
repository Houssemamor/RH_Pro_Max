<?php

namespace App\Controller;

use App\Entity\Candidate;
use App\Entity\CandidateSkill;
use App\Entity\CandidateProfileSkill;
use App\Entity\CV;
use App\Form\CandidateSkillEditType;
use App\Form\CandidateProfileSkillType;
use App\Form\CVType;
use App\Repository\CandidateProfileRepository;
use App\Service\SkillMatchingService;
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
    public function showApplication(int $id, CandidateProfileRepository $candidateProfileRepository, SkillMatchingService $skillMatchingService): Response
    {
        $candidate = $this->getUser();

        if (!$candidate instanceof Candidate) {
            throw $this->createAccessDeniedException('Only candidates can access this page.');
        }

        $application = $candidateProfileRepository->find($id);

        if (!$application || $application->getCandidate() !== $candidate) {
            throw $this->createNotFoundException('Application not found.');
        }

        // Calculate skill match with job offer
        $jobOffer = $application->getJobOffer();
        $skillMatch = null;
        
        if ($jobOffer) {
            $skillMatch = $skillMatchingService->calculateMatch($application, $jobOffer);
            $skillMatch['level'] = $skillMatchingService->getMatchLevel($skillMatch['matchPercentage']);
        }

        return $this->render('candidate_dashboard/show_application.html.twig', [
            'application' => $application,
            'skill_match' => $skillMatch,
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

    #[Route('/application/{id}/add-skill', name: 'app_candidate_add_skill', methods: ['GET', 'POST'])]
    public function addSkill(
        int $id,
        Request $request,
        CandidateProfileRepository $candidateProfileRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $candidate = $this->getUser();

        if (!$candidate instanceof Candidate) {
            throw $this->createAccessDeniedException('Only candidates can access this page.');
        }

        $application = $candidateProfileRepository->find($id);

        if (!$application || $application->getCandidate() !== $candidate) {
            throw $this->createNotFoundException('Application not found.');
        }

        $candidateSkill = new CandidateSkill();
        $candidateSkill->setCandidateProfile($application);
        $candidateSkill->setConfidence(1.0); // Manual entry = 100% confidence

        $form = $this->createForm(CandidateSkillEditType::class, $candidateSkill);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Check if skill already exists for this application
            $existingSkill = null;
            foreach ($application->getSkills() as $existing) {
                if ($existing->getSkill()->getId() === $candidateSkill->getSkill()->getId()) {
                    $existingSkill = $existing;
                    break;
                }
            }

            if ($existingSkill) {
                $this->addFlash('warning', 'You already have this skill in your profile.');
            } else {
                $entityManager->persist($candidateSkill);
                $entityManager->flush();

                $this->addFlash('success', 'Skill added successfully!');
            }

            return $this->redirectToRoute('app_candidate_application_show', ['id' => $id]);
        }

        return $this->render('candidate_dashboard/add_skill.html.twig', [
            'application' => $application,
            'form' => $form,
        ]);
    }

    #[Route('/skill/{id}/delete', name: 'app_candidate_delete_skill', methods: ['POST'])]
    public function deleteSkill(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $candidate = $this->getUser();

        if (!$candidate instanceof Candidate) {
            throw $this->createAccessDeniedException('Only candidates can access this page.');
        }

        $candidateSkill = $entityManager->getRepository(CandidateSkill::class)->find($id);

        if (!$candidateSkill || $candidateSkill->getCandidateProfile()->getCandidate() !== $candidate) {
            throw $this->createNotFoundException('Skill not found.');
        }

        $applicationId = $candidateSkill->getCandidateProfile()->getId();

        if ($this->isCsrfTokenValid('delete-skill' . $candidateSkill->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($candidateSkill);
            $entityManager->flush();

            $this->addFlash('success', 'Skill removed successfully!');
        }

        return $this->redirectToRoute('app_candidate_application_show', ['id' => $applicationId]);
    }

    #[Route('/my-profile', name: 'app_candidate_profile', methods: ['GET'])]
    public function profile(): Response
    {
        $candidate = $this->getUser();

        if (!$candidate instanceof Candidate) {
            throw $this->createAccessDeniedException('Only candidates can access this page.');
        }

        return $this->render('candidate_dashboard/profile.html.twig', [
            'candidate' => $candidate,
        ]);
    }

    #[Route('/my-profile/add-skill', name: 'app_candidate_profile_add_skill', methods: ['GET', 'POST'])]
    public function addProfileSkill(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $candidate = $this->getUser();

        if (!$candidate instanceof Candidate) {
            throw $this->createAccessDeniedException('Only candidates can access this page.');
        }

        $profileSkill = new CandidateProfileSkill();
        $profileSkill->setCandidate($candidate);

        $form = $this->createForm(CandidateProfileSkillType::class, $profileSkill);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Check if skill already exists
            $existingSkill = null;
            foreach ($candidate->getProfileSkills() as $existing) {
                if ($existing->getSkill()->getId() === $profileSkill->getSkill()->getId()) {
                    $existingSkill = $existing;
                    break;
                }
            }

            if ($existingSkill) {
                $this->addFlash('warning', 'You already have this skill in your profile.');
            } else {
                $entityManager->persist($profileSkill);
                $entityManager->flush();

                $this->addFlash('success', 'Skill added to your profile!');
            }

            return $this->redirectToRoute('app_candidate_profile');
        }

        return $this->render('candidate_dashboard/add_profile_skill.html.twig', [
            'candidate' => $candidate,
            'form' => $form,
        ]);
    }

    #[Route('/my-profile/skill/{id}/delete', name: 'app_candidate_profile_delete_skill', methods: ['POST'])]
    public function deleteProfileSkill(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $candidate = $this->getUser();

        if (!$candidate instanceof Candidate) {
            throw $this->createAccessDeniedException('Only candidates can access this page.');
        }

        $profileSkill = $entityManager->getRepository(CandidateProfileSkill::class)->find($id);

        if (!$profileSkill || $profileSkill->getCandidate() !== $candidate) {
            throw $this->createNotFoundException('Skill not found.');
        }

        if ($this->isCsrfTokenValid('delete-profile-skill' . $profileSkill->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($profileSkill);
            $entityManager->flush();

            $this->addFlash('success', 'Skill removed from your profile!');
        }

        return $this->redirectToRoute('app_candidate_profile');
    }
}
