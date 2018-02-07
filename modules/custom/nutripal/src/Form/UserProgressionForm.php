<?php

namespace Drupal\nutripal\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class UserProgressionForm extends FormBase {

    public function getFormId() {
        return 'user_progression_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state) {
        $form['weight'] = array(
            '#type' => 'number',
            '#title' => $this->t('Your weight'),
            '#step' => 0.01,
        );
        
        $form['add'] = array(
            '#type' => 'submit',
            '#value' => $this->t('add'),
        );
        return $form;
    }

    public function validateForm(array &$form, FormStateInterface $form_state) {
        $weight = $form_state->getValue('weight');
        
        if(! is_numeric($weight)) {
            $form_state->setErrorByName('weight', $this->t('Value must be numeric.'));
        }
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
	$user = \Drupal::routeMatch()->getParameter('user');
	if (! is_object($user) || empty($user)) {
            drupal_set_message($this->t('Your profile ID could not be loaded.'), 'error');
        }
                
        //$user = \Drupal::currentUser()->id();
        $date = time();
        $weight = $form_state->getValue('weight');

        $database = \Drupal::database();
        $query = $database->insert('nutripal_user_progression')->fields(['uid' => $user->id(), 'weight' => $weight, 'date' => $date])->execute();
        
        if($query != NULL)
            drupal_set_message($this->t('Your current weight was saved.'));
        else {
            drupal_set_message($this->t('There was an error saving your current weight!'), 'error');
        }
    }

}
