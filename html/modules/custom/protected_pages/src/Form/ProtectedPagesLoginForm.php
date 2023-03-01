<?php

namespace Drupal\protected_pages\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Password\PasswordInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\protected_pages\ProtectedPagesStorage;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides login screen to access protected page.
 */
class ProtectedPagesLoginForm extends FormBase {

  /**
   * The protected pages storage service.
   *
   * @var \Drupal\protected_pages\ProtectedPagesStorage
   */
  protected $protectedPagesStorage;

  /**
   * Provides the password hashing service object.
   *
   * @var \Drupal\Core\Password\PasswordInterface
   */
  protected $password;

  /**
   * Account proxy service.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * A date time instance.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Constructs a new ProtectedPagesLoginForm.
   *
   * @param \Drupal\Core\Password\PasswordInterface $password
   *   The password hashing service.
   * @param \Drupal\protected_pages\ProtectedPagesStorage $protectedPagesStorage
   *   The protected pages storage.
   * @param \Drupal\Core\Session\AccountProxy $currentUser
   *   The current user service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   A date time instance.
   */
  public function __construct(PasswordInterface $password, ProtectedPagesStorage $protectedPagesStorage, AccountProxy $currentUser, TimeInterface $time) {
    $this->password = $password;
    $this->protectedPagesStorage = $protectedPagesStorage;
    $this->currentUser = $currentUser;
    $this->time = $time;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('password'),
      $container->get('protected_pages.storage'),
      $container->get('current_user'),
      $container->get('datetime.time')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'protected_pages_enter_password';
  }

  /**
   * Checks access based permission and protected page id.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function accessProtectedPageLoginScreen() {
    $param_protected_page = $this->getRequest()->query->get('protected_page');
    $param_exists = (isset($param_protected_page) && is_numeric($param_protected_page));
    return AccessResult::allowedIf(($this->currentUser->hasPermission('access protected page password screen') || ($this->currentUser->id() == 1)) && $param_exists);
  }

  /**
   * Route title callback.
   *
   * @return string
   *   The protected page login screen title.
   */
  public function protectedPageTitle() {
    $config = $this->config('protected_pages.settings');
    return Html::escape($config->get('others.protected_pages_title'));
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('protected_pages.settings');

    $form['protected_page_enter_password'] = [
      '#type' => 'fieldset',
      '#collapsible' => FALSE,
      '#title' => $config->get('others.protected_pages_password_fieldset_legend'),
    ];

    $form['protected_page_enter_password']['protected_page_pid'] = [
      '#markup' => '<div class="protected_pages_description"><strong>' . $config->get('others.protected_pages_description') . '</strong></div>',
    ];
    $form['protected_page_enter_password']['password'] = [
      '#type' => 'password',
      '#title' => $config->get('others.protected_pages_password_label'),
      '#size' => 20,
      '#required' => TRUE,
    ];

    $form['protected_page_pid'] = [
      '#type' => 'hidden',
      '#value' => $this->getRequest()->query->get('protected_page'),
    ];

    $form['protected_page_enter_password']['submit'] = [
      '#type' => 'submit',
      '#value' => $config->get('others.protected_pages_submit_button_text'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('protected_pages.settings');
    $global_password_setting = $config->get('password.per_page_or_global');

    if ($global_password_setting == 'per_page_password') {
      $fields = ['password'];
      $conditions = [];
      $conditions['general'][] = [
        'field' => 'pid',
        'value' => $form_state->getValue('protected_page_pid'),
        'operator' => '=',
      ];

      $password = $this->protectedPagesStorage->loadProtectedPage($fields, $conditions, TRUE);

      if (!$this->password->check($form_state->getValue('password'), $password)) {
        $form_state->setErrorByName('password', $config->get('others.protected_pages_incorrect_password_msg'));
      }
    }
    elseif ($global_password_setting == 'per_page_or_global') {
      $fields = ['password'];
      $conditions = [];
      $conditions['general'][] = [
        'field' => 'pid',
        'value' => $form_state->getValue('protected_page_pid'),
        'operator' => '=',
      ];

      $password = $this->protectedPagesStorage->loadProtectedPage($fields, $conditions, TRUE);
      $global_password = $config->get('password.protected_pages_global_password');

      if (!$this->password->check($form_state->getValue('password'), $password) && !$this->password->check($form_state->getValue('password'), $global_password)) {
        $form_state->setErrorByName('password', $config->get('others.protected_pages_incorrect_password_msg'));
      }
    }
    else {
      $global_password = $config->get('password.protected_pages_global_password');

      if (!$this->password->check($form_state->getValue('password'), $global_password)) {
        $form_state->setErrorByName('password', $config->get('others.protected_pages_incorrect_password_msg'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('protected_pages.settings');
    $_SESSION['_protected_page']['passwords'][$form_state->getValue('protected_page_pid')]['request_time'] = $this->time->getRequestTime();
    $session_expire_time = $config->get('password.protected_pages_session_expire_time');
    if ($session_expire_time) {
      $_SESSION['_protected_page']['passwords'][$form_state->getValue('protected_page_pid')]['expire_time'] = strtotime("+{$session_expire_time} minutes");
    }
  }

}
