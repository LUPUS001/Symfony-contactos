<?php

namespace App\Controller;

use App\Entity\Contacto;
use App\Entity\Provincia;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ContactoController extends AbstractController
{
    private $contactos = [
        1=> ["nombre" => "Alberto", "telefono" => "68654125", "email" => "alberto@ieselcaminas.org"],
        2=> ["nombre" => "Julio", "telefono" => "641235636", "email" => "julio@ieselcaminas.org"],
        3=> ["nombre" => "Cristina", "telefono" => "65412568", "email" => "cristina@ieselcaminas.org"],
        4=> ["nombre" => "Maria", "telefono" => "685741234", "email" => "maria@ieselcaminas.org"],
        5=> ["nombre" => "Juan", "telefono" => "645268942", "email" => "juan@ieselcaminas.org"],
    ];

    #[Route('/contacto/insertar/provincia', name: 'insertar_contacto_con_provincia')]
    public function insertarConProvincia(ManagerRegistry $doctrine): Response
    {
        // Recordemos que esta variable es la que nos permite trabajar con Query en este método
        $entityManager = $doctrine->getManager();

        // Creamos la provincia junto a su campo
        $provincia = new Provincia();
        $provincia->setNombre("Ciudad Real");

        // Creamos el contacto junto a sus campos
        $contacto = new Contacto();
        $contacto->setNombre('Jesucristo Garcia');
        $contacto->setEmail('robe@eliluminado.extdur');
        $contacto->setTelefono('666996969');
        $contacto->setProvincia($provincia);

        // Lo guardamos y subimos a la BD
        $entityManager->persist($contacto);
        $entityManager->flush();

        // Al buscar esta ruta / ejecutar este método, luego de subir el contacto, renderizaremos esta plantilla
        return $this->render('ficha_contacto.html.twig', [
            'contacto' => $contacto
        ]);
    }

    #[Route('/contacto/insertar/sinprovincia', name: 'insertar_contacto_sin_provincia')]
    public function insertarSinProvincia(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $repositorio = $doctrine->getRepository(Provincia::class);

        $provincia = $repositorio->findOneBy(["nombre" => "Ciudad Real"]);

        $contacto = new Contacto();

        $contacto->setNombre('Eustaquio Habichuela');
        $contacto->setEmail('eustaquiohabi@agallas_epc.com');
        $contacto->setTelefono('988216548');
        $contacto->setProvincia($provincia);

        $entityManager->persist($contacto);
        $entityManager->flush();

        return $this->render('ficha_contacto.html.twig', [
            'contacto' => $contacto
        ]);
    }
    

    // Ejercicio 2.7.1.2
    #[Route('/contacto/buscar/conprovincia', name: 'buscar_contacto_con_provincia')]
    public function buscarContactoConProvincia(ManagerRegistry $doctrine): Response
    {
        $repositorio = $doctrine->getRepository(Contacto::class);
        
        $contacto = $repositorio->find(11);
        
        // Si lo hicieramos asi: "$provincia = $repositorio->getProvincia();" no encontraria el metodo getProvincia()
        $provincia = $contacto->getProvincia()->getNombre();

        return $this->render('ficha_contacto.html.twig', [
            "contacto" => $contacto,
            "provincia" => $provincia
        ]);
        // NOTA: Si sale 'Call to a member function getNombre() on null', es porque estaremos llamando 
        // a uno de los primeros contactos que creamos sin provincia (porque aún no existia la entidad)
    }

    #[Route('/contacto/buscar/{texto}', name: 'buscar_contacto')]
    public function buscar(ManagerRegistry $doctrine, $texto): Response
    {
        $contactoABuscar = $doctrine->getRepository(Contacto::class)->findByName($texto);

        return $this->render('lista_contactos.html.twig', [
            'contactoABuscar' => $contactoABuscar,
            'textoBuscado' => $texto,
        ]);
    }


    #[Route('/contacto/update/{codigo?1}', name: 'update')]
    public function update(ManagerRegistry $doctrine, $codigo): Response
    {
        $entityManager = $doctrine->getManager();

        // Se coge el repositorio de la entidad 'Contacto' o de la que se quiera
        $repositorio = $doctrine->getRepository(Contacto::class);

        // Buscamos el contacto que tenga el id = $codigo (el codigo que haya escrito el usuario en el buscador 'url')
        // El método 'find' busca por la clave de la tabla 'id'
        $contacto = $repositorio->find($codigo);

        // Cambiamos un dato, en este caso el nombre (aunque podría ser cualquiera)
        $contacto->setNombre("Nombre cambiado");

        // Guardamos este cambio
        $entityManager->persist($contacto);

        try {
            // y los subimos en la base de datos
            $entityManager->flush();
            
            // Mostramos la plantilla pasándole el contacto como parámetro
            return $this->render('ficha_contacto.html.twig', ["contacto" => $contacto]);
        } catch (\Exception $e) {
            return new Response("Se ha producido un error: " . $e->getMessage());
        }
    }

    #[Route('/contacto/delete/{id}', name: 'eliminar_contacto')]
    public function delete(ManagerRegistry $doctrine, $id): Response
    {
        $entityManager = $doctrine->getManager();
        $repositorio = $doctrine->getRepository(Contacto::class);
        $contacto = $repositorio->find($id);

        if ($contacto) {
            try {
                $entityManager->remove($contacto);
                $entityManager->flush();
                return new Response("Contacto eliminado: " . $contacto->getNombre());

            } catch (\Exception $e) {
                return new Response("Error eliminando objeto");
            }
        } else {
            return $this->render('ficha_contacto.html.twig', [
                'contacto' => null
            ]);
        }
    }
   

    #[Route('/contacto/{codigo}', name: 'ficha_contacto')]
    public function ficha(ManagerRegistry $doctrine, $codigo): Response
    {
        $repositorio = $doctrine->getRepository(Contacto::class);
        $contacto = $repositorio->find($codigo);

        return $this->render('ficha_contacto.html.twig', ["contacto" => $contacto]);        
    }
}