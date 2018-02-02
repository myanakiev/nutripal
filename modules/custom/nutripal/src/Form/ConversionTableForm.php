<?php

namespace Drupal\nutripal\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\nutripal\Tools\ConversionTable;

/**
 * https://api.drupal.org/api/drupal/core%21core.api.php/group/form_api/8.4.x
 * https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Render%21Element%21RenderElement.php/class/RenderElement/8.4.x
 */
class ConversionTableForm extends FormBase {
    private $conversion;

    public function __construct() {
        $this->conversion = new ConversionTable();
        $this->conversion->setConversionsDefault();
    }

    public function getFormId() {
        return 'conversion_table';
    }

    public function getTitle() {
        return t('Conversion Table');
    }

    public function buildForm(array $form, FormStateInterface $form_state) {
        $conv_fr = '';
        $conv_to = '';
        
        $form['#title'] = $this->getTitle();
        $form['convert'] = array(
            '#type' => 'hidden',
            '#value' => 'qsdqkjdlkqjsdlkqjdsl',
        );

        $convert_opts = [];
        foreach ($this->conversion->getConversionIDs() as $opid) {
            $convert_opts[$opid] = $this->t($this->conversion->getConversionName($opid));
        }

        $form['conv_fr_u'] = [
            '#type' => 'select',
            '#options' => $convert_opts,
        ];
        $form['conv_fr_v'] = array(
            '#type' => 'number',
            '#title' => $this->t('Quantity'),
        );

        $form['conv_to_u'] = [
            '#type' => 'select',
            '#options' => $convert_opts,
        ];
        $form['conv_to_v'] = array(
            '#type' => 'number',
            '#title' => $this->t('Quantity'),
            '#disabled' => TRUE,
        );

        $form['save'] = array(
            '#type' => 'submit',
            '#value' => $this->t('Convert'),
        );

        return $form;
    }

    public function validateForm(array &$form, FormStateInterface $form_state) {
        
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
        
    }

}
