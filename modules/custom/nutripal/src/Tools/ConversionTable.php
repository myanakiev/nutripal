<?php

namespace Drupal\nutripal\Tools;

/**
 * Conversion Table
 *
 * @author YANAKIEV
 */
class ConversionTable {

    private static $conversions;
    private $conversionIDs;
    private $conversionMap;

    public function __construct() {
        $this->conversionIDs = [];
        $this->conversionMap = [];
    }

    public function setConversionsDefault() {
        //Units
        $this->addConversionName(0, 'Grams (g)');
        $this->addConversionName(1, 'Ounces (oz)');
        $this->addConversionName(2, 'Pounds (lb)');
        $this->addConversionName(3, 'Degrees Celsius (°C)');
        $this->addConversionName(4, 'Degrees Fahrenheit (°F)');
        $this->addConversionName(5, 'Milliliters (ml)');
        $this->addConversionName(6, 'Mugs');
        $this->addConversionName(7, 'Teaspoons/Coffee spoons');
        $this->addConversionName(8, 'Tablespoons/Soup spoons');

        //Conversions Weigh
        $this->addConversionMap(0, 1, 0.035274);    //gr => oz
        $this->addConversionMap(0, 2, 0.00220462);  //gr => lb
        $this->addConversionMap(1, 2, 0.0625);      //oz => lb
        
        //Conversions Temps
        $this->addConversionMap(3, 4, array(__CLASS__, 'getConversionCelsiusToFahrenheit'));
        $this->addConversionMap(4, 3, array(__CLASS__, 'getConversionFahrenheitToCelsius'));

        //Conversions Vol
        $this->addConversionMap(6, 5, 250);         //ta => ml
        $this->addConversionMap(7, 5, 5);           //cc => ml
        $this->addConversionMap(8, 5, 15);          //ct => ml
    }

    public static function getConversionsDefault() {
        if (isset(ConversionTable::$conversions))
            return ConversionTable::$conversions;

        ConversionTable::$conversions = new ConversionTable();
        ConversionTable::$conversions->setConversionsDefault();

        return ConversionTable::$conversions;
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

    public function getConversionMapIDs($id_fr) {
        $map = [];

        foreach ($this->getConversionIDs() as $id_to) {
            if ($id_fr == $id_to) {
                continue;
            }

            if (isset($this->conversionMap[$id_fr][$id_to]) || isset($this->conversionMap[$id_to][$id_fr])) {
                $map[] = $id_to;
            }
        }

        return $map;
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
            //return call_user_func([$conv, $value]);
            return $conv($value);
        }

        return NULL;
    }

    private static function getConversionCelsiusToFahrenheit($deg) {
        return ($deg * 1.8) + 32;
    }

    private static function getConversionFahrenheitToCelsius($deg) {
        return ($deg - 32) / 1.8;
    }

}
