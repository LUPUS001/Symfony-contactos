<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

final class SecurityController extends AbstractController
{
    #[Route('/logout', name: 'app_logout', methods:["GET"])]
    public function logout(): void
    {
        /* aunque realmente este controlador (mejor dicho esta función/método) puede estar vacío, ya que nunca se le llamará
        esto porque Symfony intercepta la petición /logout antes de que llegue a este controlador, por lo que esta función logout() nunca se ejecutará
           
            Aunque lanzamos el Exception como medida de seguridad por si Symfony por alguna razón no interceptará la petición /logout y sucediera un error
        */
        throw new \Exception("No te olvides de activar el logout en security.yaml");
    }
}
