<?php
namespace Drupal\fatsecret\Form;

	use Drupal\Core\Form\FormBase;
	use Drupal\Core\Form\FormStateInterface;
	use Drupal\Core\Ajax\AjaxResponse;
	use Drupal\Core\Ajax\CssCommand;
	use Drupal\Core\Ajax\HtmlCommand;
	use Drupal\node\Entity\Node;
	include 'modules/custom/fatsecret/fatsecretclass.php';

class FatsecretSearchForm extends FormBase{

	/**
	* {@inheritdoc}.
	*/

	public function getFormId(){
		return 'fatsecret_search';
	}

	/**
	* {@inheritdoc}.
	*/
	public function buildForm(array $form, FormStateInterface $form_state){

		$form['expression'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Ingredient\'s name'),
			'#prefix' =>'<span class="text-message"></span>',
			'#required' => TRUE,
		];

		

		$form['search'] = [
			'#type' => 'submit',
			'#value' => $this->t('Search'),
			'#ajax' => [
				'callback'=> [$this,'SearchResultAjax'],
			]
		];

		$items = [];

		if(isset($form_state->getRebuildInfo()['result'])){

			$result = ($form_state->getRebuildInfo()['result']);
			$result = $result['foods']['food'];

			foreach ($result as $ingredient) {
				if($ingredient['food_type'] == 'Generic'){
					$items[$ingredient['food_id']] = $ingredient['food_name'];
				}
			}

			$form['select_ingredients'] = [
					  '#type' => 'checkboxes',
					  '#options' => $items,
					  '#title' => $this->t('What ingredients do you wish to add'),
					];

			$form['create_content'] = [
				'#type' => 'submit',
				'#value' => $this->t('Add content'),
			];
		}
		return $form;
	}

	/**
	* {@inheritdoc}.
	*/
	public function validateForm(array &$form, FormStateInterface $form_state){

	}

	public function SearchResultAjax(array &$form, FormStateInterface $form_state){

		$res = new AjaxResponse();

		$message = $this->t('This search did not return any results, try another word');
		$css = ['color'=>'red'];

		$res->addCommand(new HtmlCommand('#block-seven-content', $form));

		if($form_state->getRebuildInfo()['result'] == NULL){
        	$res-> addCommand(new CssCommand('.text-message',$css));
			$res-> addCommand(new HtmlCommand('.text-message',$message));
		}
        return $res;
}

	/**
	* {@inheritdoc}.
	*/
	public function submitForm(array &$form, FormStateInterface $form_state){

		$key = \Drupal::config('fatsecret.config')->get('consumerkey');
		$secret = \Drupal::config('fatsecret.config')->get('sharedsecret');
		$fatsecret = new \Drupal\fatsecret\Fatsecret();

		if($form_state->getValue('search')!= NULL){

			$expression = $form_state->getValue('expression');
			$page = 0;
			$result = $fatsecret->search($expression, $key, $secret, $page);
			$result = json_decode($result,TRUE);
			$total_results = $result['foods']['total_results'];
			
			if($total_results > 0){
				$form_state->addRebuildInfo('result', $result);
				$form_state->setRebuild();
			}
			else{
				$form_state->addRebuildInfo('result', NULL);
				$form_state->setRebuild();
			}
		}

		if($form_state->getValue('create_content')!= NULL){

			$ids = $form_state->getValue('select_ingredients');
			$data = [];

			foreach ($ids as $id){
				if($id != 0){
					$data = json_decode($fatsecret->getFood($id, $key, $secret));
					$title =$data->food->food_name;

					foreach ($data->food->servings->serving as $serving) {
						if($serving->serving_description == '100 g'){
							$calories = $serving->calories;
							$calcium = $serving->calcium;
							$carbohydrate = $serving->carbohydrate;
							$cholesterol = $serving->cholesterol;
							$fat = $serving->fat;
							$fiber = $serving->fiber;
							$iron = $serving->iron;
							$monounsaturated_fat = $serving->monounsaturated_fat;
							$polyunsaturated_fat = $serving->polyunsaturated_fat;
							$potassium = $serving->potassium;
							$protein = $serving->protein;
							$saturated_fat = $serving->saturated_fat;
							$sodium = $serving->sodium;
							$sugar = $serving->sugar;
							//$vitamin_a = $serving->vitamin_a;
							//$vitamin_b = $serving->vitamin_b;
							//$vitamin_c = $serving->vitamin_c;
							//$vitamin_d = $serving->vitamin_d;
						}
					}

					$id = \Drupal::currentUser()->id();

					if(1==1){
					
					$node = Node::create(['type' => 'aliment']);
					$node->set('title', $title);
					$body = [
						'value' => 'Now that we know who you are, I know who I am. I\'m not a mistake! It all makes sense! In a comic, you know how you can tell who the arch-villain\'s going to be? He\'s the exact opposite of the hero. And most times they\'re friends, like you and me! I should\'ve known way back when... You know why, David? Because of the kids. They called me Mr Glass.', 
						'format' => 'basic_html',
					];
					$node->set('body', $body);
					$node->set('uid', $id);
					$node->status = 1;
					$node->enforceIsNew();
					$node->save();
					drupal_set_message( "Node " . $node->getTitle() . " saved!\n");
					}
				}
			}
			$form_state->addRebuildInfo('result', NULL);
			$form_state->setRebuild();
		}
	}
}