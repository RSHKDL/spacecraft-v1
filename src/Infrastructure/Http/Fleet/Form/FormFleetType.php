<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Fleet\Form;

use App\Application\Ship\Query\ShipView;
use App\Infrastructure\Http\Fleet\Dto\FormFleet as FormFleetDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class FormFleetType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => FormFleetDto::class,
            ])
            ->setRequired('ships')
            ->setAllowedTypes('ships', ShipView::class . '[]')
        ;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $shipsById = $this->shipsById($options['ships']);
        $shipLabel = static fn (string $shipId): string => $shipsById[$shipId]->name ?? 'todo not christened ships';

        $builder
            ->add('name', TextType::class, [
                'label' => 'fleet.form.label_name',
            ])
            ->add('shipIds', ChoiceType::class, [
                'label' => 'fleet.form.label_ships',
                'multiple' => true,
                'choices' => array_keys($shipsById),
                'choice_label' => $shipLabel,
                'invalid_message' => 'fleet.form.ships_no_longer_available',

            ])
            ->add('flagshipId', ChoiceType::class, [
                'label' => 'fleet.form.label_flagship',
                'placeholder' => 'form.select_placeholder',
                'choices' => array_keys($shipsById),
                'choice_label' => $shipLabel,
                'invalid_message' => 'fleet.form.flagship_no_longer_available',
            ])
        ;
    }

    /**
     * @param ShipView[] $shipViews
     * @return array<string, ShipView>
     */
    private function shipsById(array $shipViews): array
    {
        $shipsById = [];
        foreach ($shipViews as $shipView) {
            $shipsById[$shipView->id->getValue()] = $shipView;
        }

        return $shipsById;
    }
}
