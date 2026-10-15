<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Ship\Form;

use App\Domain\Ship\ShipClass;
use App\Infrastructure\Http\Ship\Dto\BuildShip as BuildShipDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class BuildShipType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BuildShipDto::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('shipClass', EnumType::class, [
            'class' => ShipClass::class,
            'label' => 'ship.build.label_class',
            'choice_label' => function (ShipClass $shipClass): string {
                return "ship.class.{$shipClass->value}";
            },
            'placeholder' => 'form.select_placeholder',
        ]);
    }
}
