<?php
namespace Drupal\nutripal\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Database\Statement;


class NutripalUserProgressionController extends ControllerBase {
  public function content(AccountInterface $user, Request $request) {

    $idUser = $user->id();
    $registeredDate = $user->getCreatedTime();
    //kint($registeredDate);
    $weight = $user->field_weight->getString();


    $progression = \Drupal::database()->select('nutripal_user_progression')
    				->fields('nutripal_user_progression', ['uid', 'weight', 'date'])
    				->condition('uid', $idUser, '=')
    				->execute();

    $rows = [];
    
    $rows[] = [$weight, $registeredDate];
    //kint($registeredDate);
    if($progression) {

    	foreach ($progression as $value) {
    		$rows[] = [$value->weight,$value->date];
    	}
    }

    $headers = array('weight', 'date');

    $form = \Drupal::formBuilder()->getForm('\Drupal\nutripal\Form\UserProgressionForm');


    return [['#theme' => 'table', '#header' => $headers, '#rows' => $rows], $form];
 }
}