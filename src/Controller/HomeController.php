<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ProjectRepository;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ProjectRepository $projectRepository): Response
    {
        $projects = $projectRepository->findBy(['published' => true]);

        $skills = ['PHP', 'Symfony', 'MySQL', 'Docker', 'Git'];

        return $this->render('home/index.html.twig', [
            'skills' => $skills,
            'projects' => $projects,
        ]);
    }
}
