<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Ship\Form;

use App\Application\ShipDesign\Query\ShipDesignView;
use App\Infrastructure\Http\Ship\Dto\BuildShip as BuildShipDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

final class BuildShipType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => BuildShipDto::class,
            ])
            ->setRequired('designs')
            ->setAllowedTypes('designs', ShipDesignView::class . '[]');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $designsById = $this->designsById($options['designs']);

        $builder->add('designId', ChoiceType::class, [
            'choices' => array_keys($designsById),
            'label' => 'ship.build.label_design',
            'choice_label' => static function ($choice) use ($designsById): TranslatableMessage {
                $design = $designsById[$choice];

                return new TranslatableMessage('ship.build.design_choice', [
                    'name' => $design->name,
                    'class' => new TranslatableMessage('ship.class.' . $design->class->value),
                ]);
            },
            'placeholder' => 'ship.build.select_design_placeholder',
            'invalid_message' => 'ship.build.design_no_longer_available',
        ]);
    }

    /**
     * @param ShipDesignView[] $designViews
     * @return array<string, ShipDesignView>
     */
    private function designsById(array $designViews): array
    {
        $designsById = [];
        foreach ($designViews as $designView) {
            $designsById[$designView->id->getValue()] = $designView;
        }

        return $designsById;
    }
}
