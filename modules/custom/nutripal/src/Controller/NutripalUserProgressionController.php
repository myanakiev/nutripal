<?php

namespace Drupal\nutripal\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\HttpFoundation\Request;

class NutripalUserProgressionController extends ControllerBase {
    
    public function content(AccountInterface $user, Request $request) {
        $form = \Drupal::formBuilder()->getForm('\Drupal\nutripal\Form\UserProgressionForm');

        $chart['chart'] = [
            '#markup' => '<div id="line_charts_user"></div>',
        ];
        
        return [$form, $chart];
    }
}