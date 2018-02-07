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
        $weight   = isset($user->field_weight)        ? $user->field_weight->getString()        : 0;
        $weight_t = isset($user->field_target_weight) ? $user->field_target_weight->getString() : 0;

        $progression = \Drupal::database()->select('nutripal_user_progression')
                ->fields('nutripal_user_progression', ['uid', 'weight', 'date'])
                ->condition('uid', $idUser, '=')
                ->execute();

        $rows = [];

        $rows[] = [\Drupal::service('date.formatter')->format($registeredDate, $format = 'd/m/Y'), $weight, $weight_t];
        //kint($registeredDate);
        if ($progression) {
            foreach ($progression as $value) {
                $rows[] = [\Drupal::service('date.formatter')->format($value->date, $format = 'd/m/Y'), $value->weight, $weight_t];
            }
        }

        $headers = array('date', 'weight', 'target weight');
        $form = \Drupal::formBuilder()->getForm('\Drupal\nutripal\Form\UserProgressionForm');

        return [['#theme' => 'table', '#header' => $headers, '#rows' => $rows], $form];
    }

}
