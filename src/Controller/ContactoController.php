<?php

namespace App\Controller;

use App\Entity\Contacto;
use App\Entity\Provincia;
use App\Form\ContactoType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ContactoController extends AbstractController
{
    #[Route('/contacto/insertar/provincia', name: 'insertar_contacto_con_provincia')]
    public function insertarConProvincia(ManagerRegistry $doctrine): Response
    {
        // Se ha de comprobar que el usuario está logueado y enviarlo a /index en caso contrario
        if (!$this->getUser()) {
            return $this->redirectToRoute('index');
        }

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
        // También serviría enviarlo a inicio
        if (!$this->getUser()) {
            return $this->redirectToRoute('inicio');
        }

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
        // Se ha de comprobar que el usuario está logueado y enviarlo a /index en caso contrario
        if (!$this->getUser()) {
            return $this->redirectToRoute('index');
        }

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
        // Se ha de comprobar que el usuario está logueado y enviarlo a /index en caso contrario
        if (!$this->getUser()) {
            return $this->redirectToRoute('index');
        }

        $contactoABuscar = $doctrine->getRepository(Contacto::class)->findByName($texto);

        return $this->render('lista_contactos.html.twig', [
            'contactoABuscar' => $contactoABuscar,
            'textoBuscado' => $texto,
        ]);
    }


    #[Route('/contacto/update/{codigo?1}', name: 'update')]
    public function update(ManagerRegistry $doctrine, $codigo): Response
    {
        // Se ha de comprobar que el usuario está logueado y enviarlo a /index en caso contrario
        if (!$this->getUser()) {
            return $this->redirectToRoute('index');
        }

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
        // Se ha de comprobar que el usuario está logueado y enviarlo a /index en caso contrario
        if (!$this->getUser()) {
            return $this->redirectToRoute('index');
        }

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

    #[Route('/contacto/nuevo', name: 'nuevo')]
    public function nuevo(ManagerRegistry $doctrine, Request $request): Response
    {
        // Se ha de comprobar que el usuario está logueado y enviarlo a /index en caso contrario
        if (!$this->getUser()) {
            return $this->redirectToRoute('index');
        }

        $contacto = new Contacto(); // Creamos un objeto contacto (que representara el contacto que subiremos)

        $formulario = $this->createForm(ContactoType::class, $contacto); // Creamos un formulario que usará de plantilla lo que hemos definido en ContactoType y
        // vinculamos este formulario con el contacto que estamos creando, para que todos los datos que se escriban en el formulario se guarden en este contacto

        $formulario->handleRequest($request);
        // 'request' contiene toda la información que se envio (POST) desde el navegador, en este caso al enviar el formulario
        // extrae estos datos (nombre, teléfono, email) y los "mapea" (escribe los datos dentro de la entidad, tal y como hemos configurado antes)
        // si en el formulario escribio "Mateo", el handleRequest ejecutá '$contacto->setNombre("Mateo")'

        // Si el formulario se ha enviado y es válido 
        if ($formulario->isSubmitted() && $formulario->isValid()) {
            $contacto = $formulario->getData(); // Metemos los datos introducidos en el formulario, dentro del contacto

            // Guardamos estos datos en la base de datos
            $entityManager = $doctrine->getManager();
            $entityManager->persist($contacto);
            $entityManager->flush();

            // Si se pulsa el botón 'saveAndAdd', redirigimos de nuevo a 'nuevo' para añadir otro
            if ($formulario->get('saveAndAdd')->isClicked()) {
                return $this->redirectToRoute('nuevo');
            }

            // Una vez se haya enviado el formulario, redirigimos a otra plantilla que mostrará el contacto que hemos creado
            return $this->redirectToRoute('ficha_contacto', ['contacto' => $contacto->getId()]);
        }

        // Renderizamos esta plantilla con el formulario, en el momento en el que el usuario entra en la URL '/contacto/nuevo' 
        // cuando entra por primera vez o si hay errores y tiene que volver a enviar el formulario
        return $this->render('nuevo.html.twig', array(
            'formulario' => $formulario->createView()
        ));
        // array() -> lo renderizamos con un array porque es la manera más fácil de enviar varios datos a la vez (ya que un formulario es un conjunto de datos)
        // createView() -> para que el usuario pueda ver el formulario (sin createView, twig no podría dibujar/renderizar el formulario)
    }

    #[Route('/contacto/editar/{contactoId}', name: 'contacto_editar', requirements: ["contactoId" => "\d+"])]
    // requirements -> es un array de restricciones
    // contactoId -> es el parámetro que vamos a recibir
    // \d+ -> es una expresión regular que significa que el parámetro debe ser un número (1 o más dígitos)

    public function contacto_editar(ManagerRegistry $doctrine, Request $request, int $contactoId): Response
    // 'int $contactoId' -> para convertirlo en entero (solo funciona si 'contactoId' es un número, por eso 'requirements' contiene '\d+')
    {
        // Se ha de comprobar que el usuario está logueado y enviarlo a /index en caso contrario
        if (!$this->getUser()) {
            return $this->redirectToRoute('index');
        }

        // En este caso, obtenemos los datos del repositorio de contactos (la tabla Contactos con los datos que le hayamos puesto hasta ahora)
        $repositorio = $doctrine->getRepository(Contacto::class);

        $contacto = $repositorio->find($contactoId);
        if ($contacto) {
            $formulario = $this->createForm(ContactoType::class, $contacto);
            $formulario->handleRequest($request);

            if ($formulario->isSubmitted() && $formulario->isValid()) {
                // Si se pulsa el botón 'eliminar', redirigimos a la ruta de borrado
                if ($formulario->get('eliminar')->isClicked()) {
                    return $this->redirectToRoute('eliminar_contacto', ['id' => $contacto->getId()]);
                }

                // insertar los datos en la base de datos es igual que en /contacto/nuevo
                $contacto = $formulario->getData();

                $entityManager = $doctrine->getManager();
                $entityManager->persist($contacto);
                $entityManager->flush();

                return $this->redirectToRoute('ficha_contacto', ["contacto" => $contacto->getId()]);
            }
            return $this->render('nuevo.html.twig', array(
                'formulario' => $formulario->createView(),
            ));
        } else {
            return $this->render('ficha_contacto.html.twig', [
                'contacto' => NULL // Al devolver NULL, nos mostrará 'Contacto no encontrado'
            ]);
        }
    }

    #[Route('/contacto/{contacto?1}', name: 'ficha_contacto')] // he cambiado codigo por contacto porque sino sobreescribe el 
    // nombre que le hemos dado a la variable/parametro en el return (ficha_contacto', ['contacto' ...])
    public function ficha(ManagerRegistry $doctrine, $contacto): Response
    {
        // Se ha de comprobar que el usuario está logueado y enviarlo a /index en caso contrario
        if (!$this->getUser()) {
            return $this->redirectToRoute('index');
        }

        $repositorio = $doctrine->getRepository(Contacto::class);
        $contacto = $repositorio->find($contacto);

        return $this->render('ficha_contacto.html.twig', ["contacto" => $contacto]);
    }
}