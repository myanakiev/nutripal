<?php
namespace Drupal\fatsecret\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class FatsecretConfigForm extends ConfigFormBase{

	/**
	* {@inheritdoc}.
	*/
	public function getFormId(){
		return 'Fatsecret_config_form';
	}

	/**
	* {@inheritdoc}.
	*/
	protected function getEditableConfigNames(){
		return['fatsecret.config'];
	}

	/**
	* {@inheritdoc}.
	*/
	public function buildForm(array $form, FormStateInterface $form_state){

		//kint($form_state->getRebuildInfo()['result']);
		
		$consumerkey = $this->config('fatsecret.config')->get('consumerkey');
		$sharedsecret = $this->config('fatsecret.config')->get('sharedsecret');

		$form['consumerkey'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Consumer key'),
			'#required' => TRUE,
			'#default_value' => $consumerkey,
		];

		$form['sharedsecret'] = [
			'#type' => 'textfield',
			'#title' => $this->t('shared_secret'),
			'#required' => TRUE,
			'#default_value' => $sharedsecret,
		];

		$form['submit'] = [
			'#type' => 'submit',
			'#value' => $this->t('Save'),
		];
		
	return $form;
		
	}

	/**
	* {@inheritdoc}.
	*/
	public function submitForm(array &$form, FormStateInterface $form_state){
		$consumerkey = $form_state->getValue('consumerkey');;
		$sharedsecret = $color = $form_state->getValue('sharedsecret');;

		$this->config('fatsecret.config')->set('consumerkey', $consumerkey);
		$this->config('fatsecret.config')->set('sharedsecret', $sharedsecret);

		$this->config('fatsecret.config')->save();
	}

}