<?php

namespace App\Form;

use App\Entity\Contacto;
use App\Entity\Provincia;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class ContactoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre')
            ->add('telefono')
            ->add('email', EmailType::class, array('label' => 'Correo electrónico')) // ya no servirá escribir cualquier texto en este campo, ahora tendrá que tener formato correo 'x@x.com'
            ->add('provincia', EntityType::class, [
                'class' => Provincia::class,
                'choice_label' => 'id',
            ])
            ->add('save', SubmitType::class, array('label' => 'Enviar'));
        ;
        // El formulario renderiza los datos que hemos configurado aquí y en el mismo orden
    }

    // configureOptions: Metodo donde configuramos las opciones del formulario (los campos que queremos mostrar en el formulario)
    // setDefaults: Establece un conjunto de valores por defecto para las opciones del formulario 
    // data_class: Es la clase que se utiliza para mapear los datos del formulario (indica la tabla (Contacto) de la BD en la que se guardarán los datos del formulario)
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contacto::class,
        ]);
    }
}
