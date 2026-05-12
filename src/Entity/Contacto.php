<?php

namespace App\Entity;

use App\Repository\ContactoRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ContactoRepository::class)]
class Contacto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank] // Indicamos que los campos 'nombre, telefono e email' no pueden estar vacíos
    private ?string $nombre = null;

    #[ORM\Column(length: 15)]
    #[Assert\NotBlank]
    private ?string $telefono = null;

    #[ORM\Column(type:"string", length: 255)] // (*1) para que sea un email válido (que solo sea texto y no hacer operaciones con él porque el correo tenga números) ponemos el type 'string'
    #[Assert\NotBlank]
    #[Assert\Email(message: "El email {{ value }} no es válido")] // (*2) creamos un mensaje de error personalizado que saldrá si el email no es válido
    private ?string $email = null;

    #[ORM\ManyToOne(targetEntity:'Provincia', cascade:['persist'])]
    private ?Provincia $provincia = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;
        return $this;
    } 

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(?string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function setTelefono(?string $telefono): static
    {
        $this->telefono = $telefono;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getRandom() :int
    {
        return rand(1,10);
    }

    public function getProvincia(): ?Provincia
    {
        return $this->provincia;
    }

    public function setProvincia(?Provincia $provincia): static
    {
        $this->provincia = $provincia;

        return $this;
    }
}

/*
(*1)
    usamos type:string y NO type:email porque en la base de datos el email es una cadena de texto, dentro
    de la base de datos no hay un tipo "EMAIL" sino que se guardará como "VARCHAR" (string en Doctrine)
*/

/*
(*2)
    Este Assert no lo podremos comprobar porque el navegador ya detectará el campo email (gracias a EmailType::class en ContactoType) 
    y no nos dejará subirlo si el email no es válido, así que si queremos comprobar este Assert, tenemos que "engañar al navegador":

    - En el inspector (F12) casbia el tipo del campo email a "text y entonces envíar el formulario.
    - Haciendo esto, será el servidor PHP que hemos configurado nosotros el que detecte el error y muestre el mensaje de Assert.

    Aunque, todo sea dicho, simplemente con poner '@' ya pasamos la primera barrera que es el navegador y nos deja subir el formulario,
    solo que en lugar de poner correo@x.com ponemos correo@x sin el .com,  .es, ... ya nos dejará subir el formulario, por lo que aunque 
    parezca que no y se válide de forma automática, el nuevo Assert mete una segunda capa de seguridad que realmente es necesaria

    1. Si no tuvieramos ni EmailType::class ni Assert #[Assert\Email()..] podríamos subir cualquier cosa como correo
    2. Solo con EmailType::class si que tiene que seguir un formato correo pero aún tiene una vulnerabilidad (podemos subir a@a como correo válido)
    3. Si añadimos #[Assert\Email()..] junto a EmailType si que tendrá que ser un correo "perfecto" y adecuado.
*/