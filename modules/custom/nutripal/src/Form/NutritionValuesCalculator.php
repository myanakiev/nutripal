<?php
namespace Drupal\nutripal\Form;

	use Drupal\Core\Form\FormBase;
	use Drupal\Core\Form\FormStateInterface;
	use Drupal\node\Entity\Node;

class NutritionValuesCalculator extends FormBase{
	/**
	* {@inheritdoc}.
	*/
	public function getFormId(){
		return 'nutrition_values_calculator';
	}
	/**
	* {@inheritdoc}.
	*/
	public function buildForm(array $form, FormStateInterface $form_state){

		$form['serving'] = [
			'#type' => 'number',
			'#title' => $this->t('Calculate for ( g ) '),
			/*'#suffix' =>'<span>g</span>',*/
			'#required' => TRUE,
		];

		$form['calculate'] = [
				'#type' => 'submit',
				'#value' => $this->t('Calculate'),
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

			$serving = $form_state->getValue('serving');

			$form_state->setRedirect('entity.node.canonical', [
				'node' => $node->id(),
				'serving' => $serving,
			]);
		    
		}

	}
}