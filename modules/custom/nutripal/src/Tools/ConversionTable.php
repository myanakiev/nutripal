<?php

namespace Drupal\nutripal\Tools;

class ConversionTable {
    
    private $conversionIDs;
    private $conversionMap;
    
    public function __construct() {
        $this->conversionIDs = [];
        $this->conversionMap = [];
    }
    
    public function addConversion($id, $name) {
        $this->conversionIDs[$id] = $name;
    }

    public function addMapValue($id_fr, $id_to, $coef_fr_to) {
        $this->conversionMap[$id_fr][$id_to] = $coef_fr_to;
    }

    public static function getConversionID($name) {
        switch ($name) {
            case $this->getConversionName(0):
                return 0;
            case $this->getConversionName(1):
                return 1;
            case $this->getConversionName(2):
                return 2;
            case $this->getConversionName(3):
                return 3;
            case $this->getConversionName(4):
                return 4;
            case $this->getConversionName(5):
                return 5;
            case $this->getConversionName(6):
                return 6;
            case $this->getConversionName(7):
                return 7;
            default:
                return -1;
        }
    }

    public static function getConversionName($id) {
        switch ($id) {
            case 0:
                return 'Grammes (g)';
            case 1:
                return 'Onces (oz)';
            case 2:
                return 'Livres (lb)';
            case 3:
                return 'DegrÃ©s Celsius (Â°C)';
            case 4:
                return 'DegrÃ©s Fahrenheit (Â°F)';
            case 5:
                return 'Millilitres (ml)';
            case 6:
                return 'CuillÃ¨re';
            case 7:
                return 'Tasse';
            default:
                return '---';
        }
    }

    public static function getConversionMapID($id) {
        switch ($id) {
            case 0:
                return [0];
            case 1:
                return [1];
            case 2:
                return [2];
            case 3:
                return [3];
            case 4:
                return [4];
            case 5:
                return [5];
            case 6:
                return [6];
            case 7:
                return [7];
            default:
                return [];
        }
    }

    public static function getConversionMap($id_fr, $id_to) {
        //conversion map
        $map = [
            0 => [//gr
                1 => 0.035274, //gr => oz
                2 => 0.00220462, //gr => lb
            ]
        ];

        $this->addMapValue($id_fr, $id_to, $coef_fr_to);
        $this->addMapValue(0, 1, 0.035274);
        $this->addMapValue(0, 1, 0.00220462);

        if (isset($map[$id_fr][$id_to]) && !empty($map[$id_fr][$id_to])) {
            return $map[$id_fr][$id_to];
        }

        return NULL;
    }

}
