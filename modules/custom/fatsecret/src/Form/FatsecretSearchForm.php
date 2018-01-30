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
			'#ajax' => [
				'callback'=> [$this,'SearchResultAjax'],
			]
		];

		

		// Condition for the display of the select element
		if(isset($form_state->getRebuildInfo()['result'])){
			$options = [];
			$node_titles = [];

			$result = ($form_state->getRebuildInfo()['result']);
			$result = $result['foods']['food'];

			// Query to get the title of all content already existing in Aliment to add a message to options

			$database = \Drupal::database();
			$query = $database->select('node', 'n');
		    $query->leftJoin('node_field_data', 't', 't.nid = n.nid');
		    $query->fields('t', array('title'));
		    $query->condition('n.type', 'aliment', '=');
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

				$form['create_content'] = [
					'#type' => 'submit',
					'#value' => $this->t('Add content'),
					'#submit' => array([$this,'createContentSubmit']),
				];
			}
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

	// Creation content submit
	public function createContentSubmit(array &$form, FormStateInterface $form_state){
		// Get the Fatsecret rest API consumer key and secret from configuration
		$key = \Drupal::config('fatsecret.config')->get('consumerkey');
		$secret = \Drupal::config('fatsecret.config')->get('sharedsecret');

		$ids = $form_state->getValue('select_ingredients');
		$data = [];
		// For each option checked we get the nutritional infos and define fields for the node
		foreach ($ids as $id){
			if($id != 0){
				$data = json_decode(Fatsecret::getFood($id, $key, $secret));
				$title =$data->food->food_name;
				// We get the data for "100g" serving
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
				// Dev Contition to delate later
				if(1==1){
				
				// Creation of the node with the right fields and display of a confirmation message
				$node = Node::create(['type' => 'aliment']);
				$node->set('title', $title);
				$body = [
					'value' => 'Now that we know who you are, I know who I am. I\'m not a mistake! It all makes sense! In a comic, you know how you can tell who the arch-villain\'s going to be? He\'s the exact opposite of the hero. And most times they\'re friends, like you and me! I should\'ve known way back when... You know why, David? Because of the kids. They called me Mr Glass.', 
						'format' => 'basic_html',
				];
				$node->set('body', $body);
				$node->set('uid', $id);
				$node->status = 1;
				$node->setPromoted(FALSE) ;
				$node->enforceIsNew();
				$node->save();
				drupal_set_message( "Node " . $node->getTitle() . " saved!\n");
				}
			}
		}
		// Reset of all fields at the end of the creation loop (doesn't work)
		$form_state->addRebuildInfo('result', NULL);
		$form_state->setRebuild();
	}

	/**
	* {@inheritdoc}.
	*/
	public function submitForm(array &$form, FormStateInterface $form_state){

	}
}