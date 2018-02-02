<?php

namespace Drupal\nutripal\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a Hello block.
 *
 * @Block(
 *  id = "nutripal_conversion_table",
 *  admin_label = @Translation("Nutripal Conversion Table Block")
 * )
 */
class ConversionTableBlock extends BlockBase {

    public function build() {
        return \Drupal::formBuilder()->getForm('Drupal\nutripal\Form\ConversionTableForm');
    }

}
