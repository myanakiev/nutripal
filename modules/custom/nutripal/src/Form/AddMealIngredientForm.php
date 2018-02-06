<?php
namespace Drupal\nutripal\Form;

	use Drupal\Core\Form\FormBase;
	use Drupal\Core\Form\FormStateInterface;
	use Drupal\node\Entity\Node;

class AddMealIngredientForm extends FormBase{
	/**
	* {@inheritdoc}.
	*/
	public function getFormId(){
		return 'add_meal_ingredient';
	}
	/**
	* {@inheritdoc}.
	*/
	public function buildForm(array $form, FormStateInterface $form_state){

		$form['meal'] = [
			'#type' => 'select',
			'#title' => $this->t('Select your meal'),
			'#options' => [
		    'breakfast' => $this->t('Breakfast'),
		    'morning snack' => $this->t('Morning snack'),
		    'lunch' => $this->t('Lunch'),
		    'afternoon snack' => $this->t('Afternoon snack'),
		    'diner' => $this->t('Diner'),
		  ],
			'#required' => TRUE,
		];

		$form['serving'] = [
			'#type' => 'number',
			'#title' => $this->t('serving'),
			'#required' => TRUE,
		];

		$form['add'] = [
				'#type' => 'submit',
				'#value' => $this->t('add ingredients'),
			];
		return $form;
	}
	/**
	* {@inheritdoc}.
	*/
	public function validateForm(array &$form, FormStateInterface $form_state){

	}

	/**
	* {@inheritdoc}.
	*/
	public function submitForm(array &$form, FormStateInterface $form_state){
		$node = \Drupal::routeMatch()->getParameter('node');
		if (is_object($node) && $node != null && $node->getType() == 'aliments') {
			$uid = \Drupal::currentUser()->id();

			$query = \Drupal::database()->insert('nutripal_user_meals')
			->fields([
				'aid' => $node->id(),
				'uid' => $uid,
				'meal_name' => $form_state->getValue('meal'),
				'date' => time(),
				'serving' => $form_state->getValue('serving')
			])
			->execute();
		}

	}
}