<?php

namespace Drupal\nutripal\Tools;

use Psr\Log\LoggerInterface;
use Drupal\fatsecret\Fatsecret;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;

class ImportFoodData {

    const FILE_HEAD_ENCODING_UTF8 = "\xEF\xBB\xBF";
    const FILE_HEAD_ENCODING_UNICODE = "\xFF\xFE";

    private $logger;

    public function __construct(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    public function logMessage($message) {
        $this->logger->notice($message);
    }

    public function parseFoodData($input_file, $input_head, $nbr_fields, $input_del = ' ', $input_enc = '"', $input_mod = 'rb', $rows_max = 0, $rows_offset = 0) {
        if ($rows_max < 0) {
            $rows_max = 0;
        }
        if ($rows_offset < 0) {
            $rows_offset = 0;
        }
        if (($han_i = @fopen($input_file, $input_mod)) !== FALSE) {
            $row = 0;

            for ($headdump = 0; $headdump < strlen($input_head); $headdump++) {
                $this->logMessage("$input_file $row: head dump " . fgetc($han_i));
            }

            while ((($data = fgetcsv($han_i, 0, $input_del, $input_enc)) !== FALSE) && (($rows_max == 0) || ($row < $rows_offset + $rows_max))) {
                $row++;

                $key_ind = 0;
                $key = (isset($data[$key_ind]) && !empty($data[$key_ind])) ? $data[$key_ind] : '?';
                $this->logMessage("$input_file $row: $key");

                if ($row == 1) {
                    $rc = 'skipping header at row 1...';
                    $this->logMessage("$input_file $row: $rc");
                    continue;
                } elseif ($row < $rows_offset + 1) {
                    continue;
                } elseif (empty($data)) {
                    continue;
                } elseif (count($data) != $nbr_fields) {
                    $rc = "count $nbr_fields != " . count($data);
                    $this->logMessage("$input_file $row: $rc");
                    continue;
                }

                $id_import = $this->parseOneData($row, $data);
            }
            fclose($han_i);
        } else {
            $this->logMessage("$input_file: failed to open stream.");
        }
    }

    public function parseOneData($id, $data) {
        $tax_name = $data[2];
        $ing_name = $data[3];
        $pictureu = $data[4];
        $picturet = $data[5];
        $content = $data[11];

        // Get the Fatsecret rest API consumer key and secret from configuration 
        $key = \Drupal::config('fatsecret.config')->get('consumerkey');
        $secret = \Drupal::config('fatsecret.config')->get('sharedsecret');
        $expression = $ing_name;

        // The results page number we want to access 
        $page = 0;
        $result = Fatsecret::search($expression, $key, $secret, $page);
        $result = json_decode($result, TRUE);

        if (!(isset($result['foods']['food']) && is_array($result))) {
            $this->logMessage("$ing_name: no found array!");
            return;
        }

        // get the exact match | the first starting with | the first containing
        $id_em = 0;
        $id_sw = 0;
        $id_ap = 0;
        $ing_name_low = mb_strtolower($ing_name);
        foreach ($result['foods']['food'] as $food) {
            if ($food['food_type'] != 'Generic') {
                continue;
            }

            $fod_name_low = mb_strtolower($food['food_name']);

            if (($fod_name_low == $ing_name_low)) {
                $id_em = $food['food_id'];
                break;
            }
            if ((strpos($fod_name_low, $ing_name_low) === 0) && ($id_sw == 0)) {
                $id_sw = $food['food_id'];
            }
            if ((strpos($fod_name_low, $ing_name_low) > 0) && ($id_ap == 0)) {
                $id_ap = $food['food_id'];
            }
        }

        if ($id_em != 0) {
            $ids = $id_em;
        } elseif ($id_sw != 0) {
            $ids = $id_sw;
        } else {
            $ids = $id_ap;
        }
        if ($ids == 0) {
            $this->logMessage("$ing_name: not found in foodfacts!");
            return;
        }

        $food_info = json_decode(Fatsecret::getFood($ids, $key, $secret), FALSE);
        $this->importOneData($tax_name, $ing_name, $pictureu, $picturet, $content, $food_info);
    }

    public function importOneData($taxonomy_name, $ingredient_name, $picture_url, $picture_name, $html_content, $food_info) {
        // https://api.drupal.org/api/drupal/core%21modules%21node%21src%21Plugin%21views%21argument_default%21Node.php/function/Node%3A%3Acreate/8.2.x
        $tem_id = $this->importOneDataTerm($taxonomy_name, 'food_categories');
        
        $title = $food_info->food->food_name;
        $userid = 1;

        $this->logMessage("$ingredient_name: $title");

        // Creation of the node with the right fields and display of a confirmation message
        $node = Node::create(['type' => 'aliments']);
        $node->set('title', $title);
        $body = [
            'value' => $html_content,
            'format' => 'basic_html',
        ];
        $node->set('body', $body);
        $node->set('uid', $userid);
        
        // set the term reference field
        $node->set('field_food_category', $tem_id);

        // We get the data for "100g" serving
        foreach ($food_info->food->servings->serving as $serving) {
            if ($serving->serving_description == '100 g') {
                if (isset($serving->calories)) {
                    $node->set('field_calories', $serving->calories);
                }
                if (isset($serving->calcium)) {
                    $node->set('field_calcium', $serving->calcium);
                }
                if (isset($serving->carbohydrate)) {
                    $node->set('field_carbohydrate', $serving->carbohydrate);
                }
                if (isset($serving->cholesterol)) {
                    $node->set('field_cholesterol', $serving->cholesterol);
                }
                if (isset($serving->fat)) {
                    $node->set('field_total_fat', $serving->fat);
                }
                if (isset($serving->fiber)) {
                    $node->set('field_fiber', $serving->fiber);
                }
                if (isset($serving->iron)) {
                    $node->set('field_iron', $serving->iron);
                }
                if (isset($serving->monounsaturated_fat)) {
                    $node->set('field_monounsaturated_fat', $serving->monounsaturated_fat);
                }
                if (isset($serving->polyunsaturated_fat)) {
                    $node->set('field_polyunsaturated_fat', $serving->polyunsaturated_fat);
                }
                if (isset($serving->potassium)) {
                    $node->set('field_potassium', $serving->potassium);
                }
                if (isset($serving->protein)) {
                    $node->set('field_protein', $serving->protein);
                }
                if (isset($serving->saturated_fat)) {
                    $node->set('field_saturated_fat', $serving->saturated_fat);
                }
                if (isset($serving->sodium)) {
                    $node->set('field_sodium', $serving->sodium);
                }
                if (isset($serving->sugar)) {
                    $node->set('field_sugar', $serving->sugar);
                }
                if (isset($serving->vitamin_a)) {
                    $node->set('field_vitamin_a', $serving->vitamin_a);
                }
                if (isset($serving->vitamin_b)) {
                    $node->set('field_vitamin_b', $serving->vitamin_b);
                }
                if (isset($serving->vitamin_c)) {
                    $node->set('field_vitamin_c', $serving->vitamin_c);
                }
                if (isset($serving->vitamin_d)) {
                    $node->set('field_vitamin_d', $serving->vitamin_d);
                }
            }
        }
        $node->status = 1;
        $node->setPromoted(FALSE);
        $node->enforceIsNew();
        $node->save();
    }
    
    public function importOneDataTerm($term_name, $vocabulary) {
        $term_name = mb_convert_case($term_name, MB_CASE_TITLE, 'UTF-8');
        
        $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['name' => $term_name]);
        $term  = reset($terms);
        
        if(! empty($term)) {
            $term_id = $term->id();
            $this->logMessage("$vocabulary: $term_name ($term_id) exists.");
            return $term->id();
        }
               
        $term = [
            'name' => $term_name,
            'vid' => $vocabulary,
        ];

        $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->create($term);
        $term->save();
        $term_id = $term->id();
        
        $this->logMessage("$vocabulary: $term_name ($term_id) created.");
        
        return $term_id;
    }

}
