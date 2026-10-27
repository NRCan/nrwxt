<?php

namespace Drupal\nrcan_dcd\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\default_content_deploy\Event\PreSerializeEvent;
use Drupal\default_content_deploy\Event\PreSaveEntityEvent;
use Drupal\node\Entity\Node;
use Drupal\workflow\Entity\WorkflowManager;

/**
 *
 * @package Drupal\gvext_dcd\EventSubscriber
 */
class DcdEventsSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   *
   * @return array
   *   The event names to listen for, and the methods that should be executed.
   */
  public static function getSubscribedEvents() {
    return [
      PreSerializeEvent::class => 'preserializeEntity',
      PreSaveEntityEvent::class => 'presaveEntity',
    ];
  }

  /**
   * React to a node being preserialized.
   *
   * @param Drupal\default_content_deploy\Event\PreSerializeEvent $event
   *   Preserialize event from default_content_deploy.
   */
  public function preserializeEntity(PreSerializeEvent $event) {
  }

  /**
   * React to a node being presaved.
   *
   * @param Drupal\default_content_deploy\Event\PreSaveEntityEvent $event
   *   PreSaveEntity event from default_content_deploy.
   */
  public function presaveEntity(PreSaveEntityEvent $event) {
    $entity = $event->getEntity();
    $uid = \Drupal::currentUser()->id();
    if ($entity->hasField('uid')) {
      // Set the owner to the current admin or system user (UID 1)
      $entity->set('uid', $uid);
    }
    if ($entity->hasField('revision_uid')) {
      $entity->set('revision_uid', $uid);
    }
    // This is a hack to get around ContentEntityStorageBase
    // trying to load a null revision as the original.
    $entity->setOriginal($entity);
  }

}
