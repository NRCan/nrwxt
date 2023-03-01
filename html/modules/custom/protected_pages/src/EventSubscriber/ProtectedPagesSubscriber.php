<?php

namespace Drupal\protected_pages\EventSubscriber;

use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Routing\RedirectDestination;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Url;
use Drupal\path_alias\AliasManager;
use Drupal\protected_pages\ProtectedPagesStorage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Redirects user to protected page login screen.
 */
class ProtectedPagesSubscriber implements EventSubscriberInterface {

  /**
   * The path alias manager.
   *
   * @var \Drupal\path_alias\AliasManager
   */
  protected $aliasManager;

  /**
   * The account proxy service.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * The current path stack service.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * The redirect destination service.
   *
   * @var \Drupal\Core\Routing\RedirectDestination
   */
  protected $destination;

  /**
   * The request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The protected pages storage.
   *
   * @var \Drupal\protected_pages\ProtectedPagesStorage
   */
  protected $protectedPagesStorage;

  /**
   * A policy evaluating to static::DENY when the kill switch was triggered.
   *
   * @var \Drupal\Core\PageCache\ResponsePolicy\KillSwitch
   */
  protected $pageCacheKillSwitch;

  /**
   * Constructs a new ProtectedPagesSubscriber.
   *
   * @param \Drupal\path_alias\AliasManager $aliasManager
   *   The path alias manager.
   * @param \Drupal\Core\Session\AccountProxy $currentUser
   *   The account proxy service.
   * @param \Drupal\Core\Path\CurrentPathStack $currentPathStack
   *   The current path stack service.
   * @param \Drupal\Core\Routing\RedirectDestination $destination
   *   The redirect destination service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack service.
   * @param \Drupal\protected_pages\ProtectedPagesStorage $protectedPagesStorage
   *   The request stack service.
   * @param \Drupal\Core\PageCache\ResponsePolicy\KillSwitch $pageCacheKillSwitch
   *   The cache kill switch service.
   */
  public function __construct(AliasManager $aliasManager, AccountProxy $currentUser, CurrentPathStack $currentPathStack, RedirectDestination $destination, RequestStack $requestStack, ProtectedPagesStorage $protectedPagesStorage, KillSwitch $pageCacheKillSwitch) {
    $this->aliasManager = $aliasManager;
    $this->currentUser = $currentUser;
    $this->currentPath = $currentPathStack;
    $this->destination = $destination;
    $this->requestStack = $requestStack;
    $this->protectedPagesStorage = $protectedPagesStorage;
    $this->pageCacheKillSwitch = $pageCacheKillSwitch;
  }

  /**
   * Redirects user to protected page login screen.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   The event to process.
   */
  public function checkProtectedPage(FilterResponseEvent $event) {
    if ($this->currentUser->hasPermission('bypass pages password protection')) {
      return;
    }
    $current_path = $this->aliasManager->getAliasByPath($this->currentPath->getPath());
    $normal_path = mb_strtolower($this->aliasManager->getPathByAlias($current_path));
    $pid = $this->protectedPagesIsPageLocked($current_path, $normal_path);
    $this->sendAccessDenied($pid);

    if (empty($pid)) {
      $page_node = \Drupal::request()->attributes->get('node');
      if (is_object($page_node)) {
        $nid = $page_node->id();
        if (isset($nid) && is_numeric($nid)) {
          $path_to_node = '/node/' . $nid;
          $current_path = mb_strtolower($this->aliasManager->getAliasByPath($path_to_node));
          $normal_path = mb_strtolower($this->aliasManager->getPathByAlias($current_path));
          $pid = $this->protectedPagesIsPageLocked($current_path, $normal_path);
          $this->sendAccessDenied($pid);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['checkProtectedPage'];
    return $events;
  }

  /**
   * Send Access Denied for pid.
   *
   * @param int $pid
   *   The Protected Page ID.
   */
  public function sendAccessDenied($pid) {
    if (empty($pid)) {
      return;
    }

    $query = \Drupal::destination()->getAsArray();
    $query['protected_page'] = $pid;
    $this->pageCacheKillSwitch->trigger();
    $response = new RedirectResponse(Url::fromUri('internal:/protected-page', ['query' => $query])->toString());
    $response->send();
  }

  /**
   * Returns protected page id.
   *
   * @param string $current_path
   *   Current path alias.
   * @param string $normal_path
   *   Current normal path.
   *
   * @return int
   *   The protected page id.
   */
  public function protectedPagesIsPageLocked(string $current_path, string $normal_path) {
    $fields = ['pid'];
    $conditions = [];
    $conditions['or'][] = [
      'field' => 'path',
      'value' => $normal_path,
      'operator' => '=',
    ];
    $conditions['or'][] = [
      'field' => 'path',
      'value' => $current_path,
      'operator' => '=',
    ];
    $pid = $this->protectedPagesStorage->loadProtectedPage($fields, $conditions, TRUE);

    if (isset($_SESSION['_protected_page']['passwords'][$pid]['expire_time'])) {
      if (time() >= $_SESSION['_protected_page']['passwords'][$pid]['expire_time']) {
        unset($_SESSION['_protected_page']['passwords'][$pid]['request_time']);
        unset($_SESSION['_protected_page']['passwords'][$pid]['expire_time']);
      }
    }
    if (isset($_SESSION['_protected_page']['passwords'][$pid]['request_time'])) {
      return FALSE;
    }
    return $pid;
  }

}
