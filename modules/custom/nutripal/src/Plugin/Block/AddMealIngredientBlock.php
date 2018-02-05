<?php

namespace Drupal\nutripal\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a block with the Add meal ingredient form.
 *
 * @Block(
 *  id = "add_meal_ingredient",
 *  admin_label = @Translation("Add meal ingredient form")
 * )
 */
class AddMealIngredientBlock extends BlockBase {

    public function build() {
        return \Drupal::formBuilder()->getForm('Drupal\nutripal\Form\AddMealIngredientForm');
    }

}