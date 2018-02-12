<?php

namespace Drupal\nutripal\Access;

use Drupal\Core\Access\AccessCheckInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Access\AccessResult;

class MealsAccessCheck implements AccessCheckInterface {

	public function applies(Route $route){
		return NULL;
	}

	public function access(Route $route, Request $request = NULL, AccountInterface $account){
		$user = \Drupal::routeMatch()->getParameter('user');
		$query = \Drupal::database()->select('nutripal_user_meals', 'num');
		$query->fields('num', array('uid'));
		$query->condition('num.uid', $user->id(), '=');
		$query_result = $query->execute()->fetchAll();

		if(in_array("administrator", $account->getRoles()) || in_array("user_support", $account->getRoles())){
			return AccessResult::allowed()->cachePerUser()->setCacheMaxAge(5);
		}
		elseif($account->id() == $user->id() && !empty($query_result)){
			return AccessResult::forbidden()->cachePerUser()->setCacheMaxAge(5);
		}
		else{
			return AccessResult::forbidden()->cachePerUser()->setCacheMaxAge(5);
		}
	}
}