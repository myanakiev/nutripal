<?php

namespace Drupal\nutripal\Controller;

	use Drupal\Core\Controller\ControllerBase;
	use Drupal\Core\Session\AccountInterface;
	use Drupal\node\Entity\Node;
	use Symfony\Component\DependencyInjection\ContainerInterface;
	use Drupal\Core\Render\Renderer;


class NutripalUserMealsController extends ControllerBase{

	/**
   * @var Renderer
   */
  protected $renderer;

  public function __construct(Renderer $renderer) {
    $this->renderer = $renderer;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer')
    );
  }





	public function content(AccountInterface $user){
		$content = [];

		$view = views_embed_view('meals_view', 'page_1', $user->id());

		$content['#markup'] = $this->renderer->render($view);

  		return $view;
		
	
	}
}