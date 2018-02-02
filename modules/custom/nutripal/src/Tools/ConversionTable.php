<?php

namespace Drupal\nutripal\Tools;

/**
 * Conversion Table
 *
 * @author YANAKIEV
 */
class ConversionTable {

    private $conversionIDs;
    private $conversionMap;

    public function __construct() {
        $this->conversionIDs = [];
        $this->conversionMap = [];
    }

    public function setConversionsDefault() {
        //Units
        $this->addConversionName(0, 'Grammes (g)');
        $this->addConversionName(1, 'Onces (oz)');
        $this->addConversionName(2, 'Livres (lb)');
        $this->addConversionName(3, 'Degrés Celsius (°C)');
        $this->addConversionName(4, 'Degrés Fahrenheit (°F)');
        $this->addConversionName(5, 'Millilitres (ml)');
        $this->addConversionName(6, 'Cuillères');
        $this->addConversionName(7, 'Tasses');

        //Conversions Vol
        $this->addConversionMap(0, 1, 0.035274);    //gr => oz
        $this->addConversionMap(0, 2, 0.00220462);  //gr => lb
        
        //Conversions Temps
        $this->addConversionMap(3, 4, array(__CLASS__, 'getConversionCelsiusToFahrenheit'));
    }

    public function addConversionName($id, $name) {
        $this->conversionIDs[$id] = $name;
    }

    public function addConversionMap($id_fr, $id_to, $coef_fr_to) {
        $this->conversionMap[$id_fr][$id_to] = $coef_fr_to;
    }

    public function getConversionID($name) {
        return ($key = array_search($name, $this->conversionIDs)) !== FALSE ? $key : NULL;
    }
    
    public function getConversionIDs() {
        return array_keys($this->conversionIDs);
    }

    public function getConversionName($id) {
        return isset($this->conversionIDs[$id]) ? $this->conversionIDs[$id] : '---';
    }

    public function getConversionMap($id_fr, $id_to) {
        if (isset($this->conversionMap[$id_fr][$id_to]) && is_numeric($this->conversionMap[$id_fr][$id_to])) {
            return $this->conversionMap[$id_fr][$id_to];
        } elseif (isset($this->conversionMap[$id_to][$id_fr]) && is_numeric($this->conversionMap[$id_to][$id_fr]) && ($this->conversionMap[$id_to][$id_fr] != 0)) {
            return 1 / $this->conversionMap[$id_to][$id_fr];
        } elseif (isset($this->conversionMap[$id_fr][$id_to]) && is_callable($this->conversionMap[$id_fr][$id_to], FALSE, $callable_name)) {
            return $callable_name;
        }

        return NULL;
    }

    public function getConversion($id_fr, $id_to, $value) {
        $conv = $this->getConversionMap($id_fr, $id_to);

        if (is_numeric($conv)) {
            return $conv * $value;
        } elseif (is_callable($conv)) {
            //return call_user_func(array($conv, $value));
            return $conv($value);
        }

        return NULL;
    }

    private static function getConversionCelsiusToFahrenheit($celsius) {
        return ($celsius * 1.8) + 32;
    }

}
