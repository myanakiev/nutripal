<?php

namespace Drupal\nutripal\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a block with the Nutrition values calculator.
 *
 * @Block(
 *  id = "nutripal_nutrition_values_calculator",
 *  admin_label = @Translation("Nutripal Nutrition values calculator")
 * )
 */
class NutritionValuesCalculatorBlock extends BlockBase {

    public function build() {
        return \Drupal::formBuilder()->getForm('Drupal\nutripal\Form\NutritionValuesCalculator');
    }

}