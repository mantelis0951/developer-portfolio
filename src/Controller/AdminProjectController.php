<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Project;
use App\Form\ProjectType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
#[IsGranted('ROLE_ADMIN')]
final class AdminProjectController extends AbstractController
{
    #[Route('/admin/projects', name: 'admin_project_index', methods: ['GET'])]
    public function index(ProjectRepository $projectRepository): Response
    {
        return $this->render('admin/project/index.html.twig', [
            'projects' => $projectRepository->findAll(),
        ]);
    }
    #[Route('/admin/projects/new', name: 'admin_project_new', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $project = new Project();
        $project->setPublished(false);

        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($project);
            $entityManager->flush();
            $this->addFlash('success', 'Project created successfully.');
            return $this->redirectToRoute('admin_project_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/project/form.html.twig', [
            'form' => $form,
            'page_title' => 'Add project',
            'submit_label' => 'Save draft',
        ]);
    }
    #[Route('/admin/projects/{id}/edit', name: 'admin_project_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(
        #[MapEntity(id: 'id')] Project $project,
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        $form = $this->createForm(ProjectType::class, $project);
        $form->add('published', CheckboxType::class, [
            'label' => 'Publish on portfolio',
            'required' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Project updated successfully.');

            return $this->redirectToRoute('admin_project_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/project/form.html.twig', [
            'form' => $form,
            'page_title' => 'Edit project',
            'submit_label' => 'Save changes',
        ]);
    }
    #[Route(
        '/admin/projects/{id}/delete',
        name: 'admin_project_delete',
        requirements: ['id' => '\d+'],
        methods: ['GET', 'POST']
    )]
    public function delete(
        #[MapEntity(id: 'id')] Project $project,
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        if ($request->isMethod('POST')) {
            $token = $request->getPayload()->getString('_token');

            if (!$this->isCsrfTokenValid(
                'delete_project_' . $project->getId(),
                $token
            )) {
                throw $this->createAccessDeniedException('Invalid CSRF token.');
            }

            $entityManager->remove($project);
            $entityManager->flush();
            $this->addFlash('success', 'Project deleted successfully.');
            return $this->redirectToRoute(
                'admin_project_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('admin/project/delete.html.twig', [
            'project' => $project,
        ]);
    }
}
