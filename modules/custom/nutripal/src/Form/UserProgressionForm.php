<?php

namespace Drupal\nutripal\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


class UserProgressionForm extends FormBase {
  public function getFormId() {
    
    // Unique ID of the form.
    return 'user_progression_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    
    // Create a $form API array.
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
    
    // Validate submitted form data.
  }
  public function submitForm(array &$form, FormStateInterface $form_state) {
    
    $user = \Drupal::currentUser()->id();
    $date = time();
    $weight = $form_state->getValue('weight');

    $database = \Drupal::database();
    $query = $database->insert('nutripal_user_progression')->fields(['uid' => $user, 'weight' => $weight, 'date' => $date])->execute();

    drupal_set_message('Your current weight was saved.');

  }

}