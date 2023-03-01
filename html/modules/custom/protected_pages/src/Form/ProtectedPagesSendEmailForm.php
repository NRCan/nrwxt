<?php

namespace Drupal\protected_pages\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Url;
use Drupal\protected_pages\ProtectedPagesStorage;
use Egulias\EmailValidator\EmailValidator;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides send protected pages details email form.
 */
class ProtectedPagesSendEmailForm extends FormBase {

  /**
   * The protected pages storage service.
   *
   * @var \Drupal\protected_pages\ProtectedPagesStorage
   */
  protected $protectedPagesStorage;

  /**
   * The mail manager.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * The email validator.
   *
   * @var \Egulias\EmailValidator\EmailValidator
   */
  protected $emailValidator;

  /**
   * Provides messenger service.
   *
   * @var \Drupal\Core\Messenger\Messenger
   */
  protected $messenger;

  /**
   * Config Factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  /**
   * Logger channel factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Constructs a new ProtectedPagesSendEmailForm.
   *
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   *   The mail manager.
   * @param \Egulias\EmailValidator\EmailValidator $email_validator
   *   The email validator.
   * @param \Drupal\protected_pages\ProtectedPagesStorage $protectedPagesStorage
   *   The protected pages storage.
   * @param \Drupal\Core\Messenger\Messenger $messenger
   *   The messenger service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory service interface.
   * @param \Drupal\Core\Language\LanguageManager $languageManager
   *   The language manager service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The logger channel factory service.
   */
  public function __construct(MailManagerInterface $mail_manager, EmailValidator $email_validator, ProtectedPagesStorage $protectedPagesStorage, Messenger $messenger, ConfigFactoryInterface $configFactory, LanguageManager $languageManager, LoggerChannelFactoryInterface $loggerFactory) {
    $this->mailManager = $mail_manager;
    $this->emailValidator = $email_validator;
    $this->protectedPagesStorage = $protectedPagesStorage;
    $this->messenger = $messenger;
    $this->configFactory = $configFactory;
    $this->languageManager = $languageManager;
    $this->loggerFactory = $loggerFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.mail'),
      $container->get('email.validator'),
      $container->get('protected_pages.storage'),
      $container->get('messenger'),
      $container->get('config.factory'),
      $container->get('language_manager'),
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'protected_pages_send_email';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $pid = NULL) {
    $config = $this->configFactory->getEditable('protected_pages.settings');

    $form['send_email_box'] = [
      '#type' => 'details',
      '#title' => $this->t('Send email'),
      '#description' => $this->t('You send details of this protected page by email to multiple users. Please click <a href="@here">here</a> to configure email settings.', [
        '@here' => Url::fromUri('internal:/admin/config/system/protected_pages/settings', ['query' => $this->getDestinationArray()])
          ->toString(),
      ]),
      '#open' => TRUE,
    ];

    $form_state->set('pid', $pid);
    $form['send_email_box']['recipents'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Recipents'),
      '#rows' => 5,
      '#description' => $this->t('Enter comma separated list of recipients.'),
      '#required' => TRUE,
    ];
    $form['send_email_box']['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject'),
      '#default_value' => $config->get('email.subject'),
      '#description' => $this->t('Enter email subject.'),
      '#required' => TRUE,
    ];
    $form['send_email_box']['body'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Email body'),
      '#rows' => 15,
      '#default_value' => $config->get('email.body'),
      '#description' => $this->t('Enter the body of the email. Only [protected-page-url] and [site-name] tokens are available.
            Since password is encrypted, therefore we can not provide it by token.'),
      '#required' => TRUE,
    ];

    $form['send_email_box']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send email'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $emails = explode(',', str_replace(["\r", "\n"], ',', $form_state->getValue('recipents')));
    foreach ($emails as $key => $email) {
      $email = trim($email);
      if ($email) {
        if (!$this->emailValidator->isValid($email)) {
          $form_state->setErrorByName('recipents', $this->t('Invalid email address: @mail. Please correct this email.', ['@mail' => $email]));
          unset($emails[$key]);
        }
        else {
          $emails[$key] = $email;
        }
      }
      else {
        unset($emails[$key]);
      }
    }
    $form_state->set('validated_recipents', implode(', ', $emails));
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $fields = ['path'];
    $conditions = [];
    $conditions['general'][] = [
      'field' => 'pid',
      'value' => $form_state->get('pid'),
      'operator' => '=',
    ];

    $path = $this->protectedPagesStorage->loadProtectedPage($fields, $conditions, TRUE);
    $module = 'protected_pages';
    $key = 'protected_pages_details_mail';
    $to = $form_state->get('validated_recipents');
    $from = $this->configFactory->getEditable('system.site')->get('mail');
    $language_code = $this->languageManager->getDefaultLanguage()->getId();
    $send = TRUE;
    $params = [];
    $params['subject'] = $form_state->getValue('subject');
    $params['body'] = $form_state->getValue('body');
    $params['protected_page_url'] = Url::fromUri('internal:' . $path, ['absolute' => TRUE])->toString();
    $result = $this->mailManager->mail($module, $key, $to, $language_code, $params, $from, $send);
    if ($result['result'] !== TRUE) {
      $message = $this->t('There was a problem sending your email notification to @email.', ['@email' => $to]);
      $this->loggerFactory->get('protected_pages')->error($message);
    }
    else {
      $message = $this->t('The Email has been sent to @email.', ['@email' => $to]);
      $this->loggerFactory->get('protected_pages')->notice($message);
    }

    $form_state->setRedirect('protected_pages_list');
  }

}
