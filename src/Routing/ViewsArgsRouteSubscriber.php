<?php
namespace Drupal\entity_print_views_args\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

class ViewsArgsRouteSubscriber extends RouteSubscriberBase {
  protected function alterRoutes(RouteCollection $collection) {

    if ($route = $collection->get('entity_print.legacy_view')) {
      $route->setDefaults(array(
          '_controller' => '\Drupal\entity_print_views_args\Controller\PrintEntityController::viewEntityPrintRedirect',
        )
      );
    }
  }
}