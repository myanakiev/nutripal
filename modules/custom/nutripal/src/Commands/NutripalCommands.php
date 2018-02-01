<?php

namespace Drupal\nutripal\Commands;

use Drush\Commands\DrushCommands;
use Drupal\nutripal\Tools\ImportFoodData;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
 */
class NutripalCommands extends DrushCommands {

    /**
     * Nutripal import food from CSV file.
     *
     * @param $filename
     *   File name containing the CSV data.
     * @param array $options
     *   An associative array of options whose values come from cli, aliases, config, etc.
     * @option file-encoding
     *   Description
     * @usage nutripal-importfood ntpif
     *   Usage description
     *
     * @command nutripal:importfood
     * @aliases ntpif
     */
    public function importfood($filename, $options = [
        'input-head' => ImportFoodData::FILE_HEAD_ENCODING_UTF8,
        'input-fields' => 0,
        'input-del' => '	',
        'input-enc' => '"',
        'input-mod' => 'rb',
    ]) {
        // drush nutripal:importfood food.csv --file-encoding=UTF-8
        // drush nutripal:importfood data.csv --file-encoding=UNICODE
        // drush nutripal:importfood modules/custom/nutripal/data/CaloriesPOEI.txt --input-fields=12
        $this->logger()->success(t('Import food data...'));
        
        $fdi = new ImportFoodData($this->logger());
        $fdi->parseFoodData($filename, $options['input-head'], $options['input-fields'], $options['input-del'], $options['input-enc'], $options['input-mod']);
        
        $this->logger()->success(t('Import food data done.'));
    }

}
