<?php

namespace Drupal\nrcan_dcd\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\default_content_deploy\Event\PreSerializeEvent;
use Drupal\node\Entity\Node;
use Drupal\workflow\Entity\WorkflowManager;

/**
 *
 * @package Drupal\gvext_dcd\EventSubscriber
 */
class PreserializeEventsSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   *
   * @return array
   *   The event names to listen for, and the methods that should be executed.
   */
  public static function getSubscribedEvents() {
    return [
      PreSerializeEvent::class => 'preserializeEntity',
    ];
  }

  /**
   * React to a node being preserialized.
   *
   * @param Drupal\default_content_deploy\Event\PreSerializeEvent $event
   *   Preserialize event from default_content_deploy.
   */
  public function preserializeEntity(PreSerializeEvent $event) {
    $entity = $event->getEntity();
    if ($entity->getEntityTypeId() == 'node' || $entity->getEntityTypeId() == 'media') {
       $entity->set('uid', 1);
       $event->setEntity($entity);
    }
  }

}
