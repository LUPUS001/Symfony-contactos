<?php

namespace App\Controller;

use App\Entity\Contacto;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PageController extends AbstractController
{
    #[Route('/page', name: 'app_page')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/PageController.php',
        ]);
    }

    #[Route('/', name: 'inicio')] // Las rutas / y /index redirigen al método inicio()
    #[Route('/index', name: 'index')] // usamos /index simplemente para cumplir con el requisito de que tenga una ruta llamada index
    public function inicio(ManagerRegistry $doctrine): Response
    {
        // Si el usuario no ha iniciado sesión, se le redirigirá al login
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $repositorio = $doctrine->getRepository(Contacto::class);
        $contactos = $repositorio->findAll(); // obtener todos los contactos

        return $this->render('inicio.html.twig', ["contactos" => $contactos]); // le pasamos todos los contactos a la plantilla
    }
}
