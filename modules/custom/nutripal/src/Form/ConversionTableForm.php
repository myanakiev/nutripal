<?php

namespace Drupal\nutripal\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\nutripal\Tools\ConversionTable;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\HtmlCommand;

/**
 * https://api.drupal.org/api/drupal/core%21core.api.php/group/form_api/8.4.x
 * https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Render%21Element%21RenderElement.php/class/RenderElement/8.4.x
 * https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Render%21Element%21Select.php/class/Select/8.4.x
 * #id: (string) The HTML ID on the element. This is automatically set for form elements, but not for all render elements; you can override the default value or add an ID by setting this property.
 */
class ConversionTableForm extends FormBase {

    public function getFormId() {
        return 'conversion-table';
    }

    public function getTitle() {
        return t('Conversion Table');
    }

    public function buildForm(array $form, FormStateInterface $form_state) {
        $conversion = ConversionTable::getConversionsDefault();

        $id_fr = $form_state->getValue('conv-fr-u');
        $id_to = $form_state->getValue('conv-to-u');
        $vl_fr = isset($form_state->getRebuildInfo()['conv-fr-v']) ? $form_state->getRebuildInfo()['conv-fr-v'] : 100;
        $vl_to = isset($form_state->getRebuildInfo()['conv-to-v']) ? $form_state->getRebuildInfo()['conv-to-v'] : '';

        $form['#title'] = $this->getTitle();
        $form['#id'] = $this->getFormId();

        $convert_opts = [];
        foreach ($conversion->getConversionIDs() as $opid) {
            $convert_opts[$opid] = $this->t($conversion->getConversionName($opid));
        }
        $df_fr = isset($id_fr) ? $id_fr : key($convert_opts);
        $form['conv-fr-u'] = [
            '#type' => 'select',
            '#id' => 'conv-fr-u',
            '#options' => $convert_opts,
            '#default_value' => $df_fr,
            '#ajax' => [
                'callback' => [$this, 'buildFormSelectTo'],
                'event' => 'change',
            ],
        ];
        $form['conv-fr-v'] = [
            '#type' => 'number',
            '#title' => $this->t('Quantity'),
            '#default_value' => $vl_fr,
        ];

        $convert_opts = [];
        foreach ($conversion->getConversionMapIDs($df_fr) as $opid) {
            $convert_opts[$opid] = $this->t($conversion->getConversionName($opid));
        }
        $df_to = isset($convert_opts[$id_to]) ? $id_to : key($convert_opts);
        $form['conv-to-u'] = [
            '#type' => 'select',
            '#id' => 'conv-to-u',
            '#options' => $convert_opts,
            '#default_value' => $df_to,
        ];
        $form['conv-to-v'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Quantity'),
            '#value' => $vl_to,
            '#disabled' => TRUE,
        ];

        $form['save'] = [
            '#type' => 'submit',
            '#value' => $this->t('Convert Units'),
        ];

        return $form;
    }

    public function buildFormSelectTo(array &$form, FormStateInterface $form_state) {
        $conversion = ConversionTable::getConversionsDefault();

        $id_fr = $form_state->getValue('conv-fr-u');
        $id_to = $form_state->getValue('conv-to-u');

        $convert_opts = [];
        foreach ($conversion->getConversionMapIDs($id_fr) as $opid) {
            $convert_opts[$opid] = $this->t($conversion->getConversionName($opid));
        }

        $css = ['border-color' => 'green'];
        $htm = [
            '#type' => 'select',
            '#id' => 'conv-to-u',
            '#options' => $convert_opts,
            '#theme_wrappers' => [],
        ];

        $res = new AjaxResponse();
        $res->addCommand(new HtmlCommand('.form-item-conv-to-u', $htm));
        $res->addCommand(new CssCommand('#conv-to-u', $css));

        return $res;
    }

    public function validateForm(array &$form, FormStateInterface $form_state) {
        $conversion = ConversionTable::getConversionsDefault();

        $id_fr = $form_state->getValue('conv-fr-u');
        $id_to = $form_state->getValue('conv-to-u');
        $vl_fr = $form_state->getValue('conv-fr-v');

        if ($conversion->getConversionMap($id_fr, $id_to) == NULL) {
            $form_state->setErrorByName('conv-to-u', $this->t('Could not convert to this unit.'));
        }

        if (!is_numeric($vl_fr)) {
            $form_state->setErrorByName('conv-fr-v', $this->t('Value must be numeric.'));
        }
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
        $conversion = ConversionTable::getConversionsDefault();

        $id_fr = $form_state->getValue('conv-fr-u');
        $id_to = $form_state->getValue('conv-to-u');
        $vl_fr = $form_state->getValue('conv-fr-v');

        $resut = $conversion->getConversion($id_fr, $id_to, $vl_fr);

        // On passe le résultat.
        $form_state->addRebuildInfo('conv-fr-v', $vl_fr);
        $form_state->addRebuildInfo('conv-to-v', round($resut, 2));
        // Reconstruction du formulaire avec les valeurs saisies.
        $form_state->setRebuild();
    }

}
