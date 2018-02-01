<?php
namespace Drupal\fatsecret\Form;

	use Drupal\Core\Form\FormBase;
	use Drupal\Core\Form\FormStateInterface;
	use Drupal\Core\Ajax\AjaxResponse;
	use Drupal\Core\Ajax\CssCommand;
	use Drupal\Core\Ajax\HtmlCommand;
	use Drupal\node\Entity\Node;
	use Drupal\fatsecret\Fatsecret;

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
			'#submit' => array([$this,'searchSubmit']),
			/*'#ajax' => [
				'callback'=> [$this,'SearchResultAjax'],
			]*/
		];

		// Condition for the display of the select element
		if(isset($form_state->getRebuildInfo()['result']) && !isset($form_state->getRebuildInfo()['checked'])){
			$options = [];
			$node_titles = [];

			$result = ($form_state->getRebuildInfo()['result']);
			$result = $result['foods']['food'];

			// Query to get the title of all content already existing in Aliments to add a message to options

			$database = \Drupal::database();
			$query = $database->select('node', 'n');
			$query->leftJoin('node_field_data', 't', 't.nid = n.nid');
			$query->fields('t', array('title'));
			$query->condition('n.type', 'aliments', '=');
			$query_result = $query->execute()->fetchAll();

			foreach ($query_result as $aliment) {
				$node_titles[] = $aliment->title;
			}

			// Displaying the results only for generic aliments
			foreach ($result as $ingredient) {
				if($ingredient['food_type'] == 'Generic'){
					$food_name = $ingredient["food_name"];
					if(in_array($food_name, $node_titles)){
						$options[$ingredient['food_id']] = $food_name." (Already exist)";
					}
					else{
						$options[$ingredient['food_id']] = $food_name;
					}
				}
			}
			if(!empty($options)){
				$form['select_ingredients'] = [
						  '#type' => 'checkboxes',
						  '#options' => $options,
						  '#title' => $this->t('What ingredients do you wish to add'),
						];

				$form['next'] = [
					'#type' => 'submit',
					'#value' => $this->t('next'),
					'#submit' => array([$this,'nextSubmit']),
				];
			}
		}
		if(isset($form_state->getRebuildInfo()['checked'])){
			foreach ($form_state->getRebuildInfo()['checked'] as $title) {
				$form['description_'.$title] = [
					'#type' => 'textarea',
					'#title' => $this->t('Description for '.$title),
				];
			}
			$form['create_content'] = [
				'#type' => 'submit',
				'#value' => $this->t('Add content'),
				'#submit' => array([$this,'createContentSubmit']),
			];

			$form_state->set('final_data', $form_state->getRebuildInfo()['final_data']);
		}
		return $form;
	}

	/**
	* {@inheritdoc}.
	*/
	public function validateForm(array &$form, FormStateInterface $form_state){

	}
	//Ajax validation function to add red text if the research returns nothing
	public function SearchResultAjax(array &$form, FormStateInterface $form_state){

		$res = new AjaxResponse();

		$message = $this->t('This search did not return any results, try another word');
		$css = ['color'=>'red'];

		$res->addCommand(new HtmlCommand('#block-seven-content', $form));

		if(!isset($form_state->getRebuildInfo()['result'])){
			$res-> addCommand(new CssCommand('.text-message',$css));
			$res-> addCommand(new HtmlCommand('.text-message',$message));
		}
		return $res;
	}

	// Search submit
	public function searchSubmit(array &$form, FormStateInterface $form_state){
		// Get the Fatsecret rest API consumer key and secret from configuration 
		$key = \Drupal::config('fatsecret.config')->get('consumerkey');
		$secret = \Drupal::config('fatsecret.config')->get('sharedsecret');

		$expression = $form_state->getValue('expression');
		// The results page number we want to access 
		$page = 0;
		$result = Fatsecret::search($expression, $key, $secret, $page);
		$result = json_decode($result,TRUE);
		$total_results = $result['foods']['total_results'];
		
		if($total_results > 0){
			$form_state->addRebuildInfo('result', $result);
			$form_state->setRebuild();
		}
	}

	// Get the food infos and store it for the next step
	public function nextSubmit(array &$form, FormStateInterface $form_state){
		// Get the Fatsecret rest API consumer key and secret from configuration
		$key = \Drupal::config('fatsecret.config')->get('consumerkey');
		$secret = \Drupal::config('fatsecret.config')->get('sharedsecret');

		$ids = $form_state->getValue('select_ingredients');

		$data = [];
		$checked = [];
		// For each option checked we get the nutritional infos
		foreach ($ids as $id){
			if($id != 0){
				$food_info = json_decode(Fatsecret::getFood($id, $key, $secret));
				$title =$food_info->food->food_name;
				$data[] = $food_info;
				$checked[] = $title;
			}
		}
		$form_state->addRebuildInfo('checked', $checked);
		$form_state->addRebuildInfo('final_data', $data);
		$form_state->setRebuild();
	}

	// Creation content submit
	public function createContentSubmit(array &$form, FormStateInterface $form_state){
		// For each option checked we get the nutritional infos and define fields for the node
		$datas = $form_state->get('final_data');
		foreach ($datas as $data){

			$title =$data->food->food_name;
			$userid = \Drupal::currentUser()->id();
			// Dev Condition to delate later
			if(1==1){
				// Creation of the node with the right fields and display of a confirmation message
				$node = Node::create(['type' => 'aliments']);
				$node->set('title', $title);
				$description = $form_state->getValue('description_'.$title);
				$body = [
					'value' => $description, 
					'format' => 'basic_html',
				];
				$node->set('body', $body);
				$node->set('uid', $userid);

				// We get the data for "100g" serving
				foreach ($data->food->servings->serving as $serving) {
					if($serving->serving_description == '100 g'){
						if(isset($serving->calories)){
							$node->set('field_calories', $serving->calories);
						}
						if(isset($serving->calcium)){
							$node->set('field_calcium', $serving->calcium);
						}
						if(isset($serving->carbohydrate)){
							$node->set('field_carbohydrate', $serving->carbohydrate);
						}
						if(isset($serving->cholesterol)){
							$node->set('field_cholesterol', $serving->cholesterol);
						}
						if(isset($serving->fat)){
							$node->set('field_total_fat', $serving->fat);
						}
						if(isset($serving->fiber)){
							$node->set('field_fiber', $serving->fiber);
						}
						if(isset($serving->iron)){
							$node->set('field_iron', $serving->iron);
						}
						if(isset($serving->monounsaturated_fat)){
							$node->set('field_monounsaturated_fat', $serving->monounsaturated_fat);
						}
						if(isset($serving->polyunsaturated_fat)){
							$node->set('field_polyunsaturated_fat', $serving->polyunsaturated_fat);
						}
						if(isset($serving->potassium)){
							$node->set('field_potassium', $serving->potassium);
						}
						if(isset($serving->protein)){
							$node->set('field_protein', $serving->protein);
						}
						if(isset($serving->saturated_fat)){
							$node->set('field_saturated_fat', $serving->saturated_fat);
						}
						if(isset($serving->sodium)){
							$node->set('field_sodium', $serving->sodium);
						}
						if(isset($serving->sugar)){
							$node->set('field_sugar', $serving->sugar);
						}
						if(isset($serving->vitamin_a)){
							$node->set('field_vitamin_a', $serving->vitamin_a);
						}
						if(isset($serving->vitamin_b)){
							$node->set('field_vitamin_b', $serving->vitamin_b);
						}
						if(isset($serving->vitamin_c)){
							$node->set('field_vitamin_c', $serving->vitamin_c);
						}
						if(isset($serving->vitamin_d)){
							$node->set('field_vitamin_d', $serving->vitamin_d);
						}
					}
				}
				$node->status = 1;
				$node->setPromoted(FALSE) ;
				$node->enforceIsNew();
				$node->save();
				drupal_set_message( "Node " . $node->getTitle() . " saved!\n");
			}
		}
		// Reset of all fields at the end of the creation loop (doesn't totally work)
		$form_state->addRebuildInfo('result', NULL);
		$form_state->setRebuild();
	}

	/**
	* {@inheritdoc}.
	*/
	public function submitForm(array &$form, FormStateInterface $form_state){

	}
}