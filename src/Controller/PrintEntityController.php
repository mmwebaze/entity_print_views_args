<?php

namespace Drupal\entity_print_views_args\Controller;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\entity_print\Controller\EntityPrintController;
use Drupal\entity_print\Plugin\EntityPrintPluginManagerInterface;
use Drupal\entity_print\Plugin\ExportTypeManagerInterface;
use Drupal\entity_print\PrintBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PrintEntityController.
 */
class PrintEntityController extends EntityPrintController {

  public function __construct(EntityPrintPluginManagerInterface $plugin_manager, ExportTypeManagerInterface $export_type_manager, PrintBuilderInterface $print_builder, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($plugin_manager, $export_type_manager, $print_builder, $entity_type_manager);
  }
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.entity_print.print_engine'),
      $container->get('plugin.manager.entity_print.export_type'),
      $container->get('entity_print.print_builder'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * @param string $export_type
   *   The export type
   * @param $entity_type
   *   The entity type
   * @param $entity_id
   *   The entity id
   * @param Request $request
   *   The request contains the contextual filter
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function viewEntityPrintRedirect($export_type, $entity_type, $entity_id, Request $request) {

   $parameters = [
     'export_type' => $export_type,
     'entity_type' => $entity_type,
     'entity_id' => $entity_id,
     'view_args' => $request->get('view_args'),
   ];
    return $this->redirect('entity_print_views_args.view', $parameters);
  }

  /**
   * @param $export_type
   *   The export type
   * @param $entity_type
   *   The entity type
   * @param $entity_id
   *   The entity id
   * @param $view_args
   *   The contextual filter selected
   *
   */
  public function viewEntityPrint($export_type, $entity_type, $entity_id, $view_args) {
    // Create the Print engine plugin.
    $config = $this->config('entity_print.settings');
    $entity = $this->entityTypeManager->getStorage($entity_type)->load($entity_id);
    $view = $entity->getExecutable();

    $view->setArguments(['nid' => $view_args]);
    //$view->execute();

    $print_engine = $this->pluginManager->createSelectedInstance($export_type);
    return (new StreamedResponse(function () use ($entity, $print_engine, $config) {
      // The Print is sent straight to the browser.
      $this->printBuilder->deliverPrintable([$entity], $print_engine, $config->get('force_download'), $config->get('default_css'));
    }))->send();
  }
}
