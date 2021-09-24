<?php

namespace Drupal\cloudfront_path_invalidate\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

/**
 * Contains CDNKeys settings.
 */
class CloudfrontPathInvalidateKeysSettingsPage extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cloudfront_path_invalidate_keys_settings_page';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['cloudfront_path_invalidate.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('cloudfront_path_invalidate.settings');

    foreach (Element::children($form) as $variable) {
      $config->set($variable, $form_state->getValue($form[$variable]['#parents']));
    }
    $config->save();

    if (method_exists($this, '_submitForm')) {
      $this->_submitForm($form, $form_state);
    }

    parent::submitForm($form, $form_state);
  }

  /**
   * CDNKeys form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('cloudfront_path_invalidate.settings');
    $form['cloudfront_path_invalidate_distribution'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Distribution ID'),
      '#required' => TRUE,
      '#default_value' => $config->get('cloudfront_path_invalidate_distribution'),
    ];

    $form['cloudfront_path_invalidate_access'] = [
      '#markup' => $this->t('Note that the AWS IAM user credentials are set in the environment (/usr/share/httpd/.aws/credentials) and are not configurable here'),
    ];

    $profile = $config->get('cloudfront_path_invalidate_profile');
    if (!$profile) {
      $profile = 'default';
    }
    $form['cloudfront_path_invalidate_profile'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Credential Profile'),
      '#required' => TRUE,
      '#attributes' => [
        'value' => $profile,
      ],
    ];

    $region = $config->get('cloudfront_path_invalidate_region');
    if (!$region) {
      $region = 'ca-central-1';
    }
    $form['cloudfront_path_invalidate_region'] = [
      '#type' => 'textfield',
      '#title' => $this->t('AWS Region'),
      '#required' => TRUE,
      '#attributes' => [
        'value' => $region,
      ],
    ];


    /*
    $form['cloudfront_path_invalidate_access'] = [
      '#type' => 'password',
      '#title' => $this->t('Access Key'),
      '#required' => TRUE,
      '#attributes' => [
        'value' => $config->get('cloudfront_path_invalidate_access'),
      ],
    ];
    $form['cloudfront_path_invalidate_secret'] = [
      '#type' => 'password',
      '#title' => $this->t('Secret Key'),
      '#required' => TRUE,
      '#attributes' => [
        'value' => $config->get('cloudfront_path_invalidate_secret'),
      ],
    ];
    */
    $form['cloudfront_path_invalidate_homapage'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Homepage node_id or url_alias. This will include "/"
    with invalidation paths listed below. (eg. node/1234)'),
      '#default_value' => $config->get('cloudfront_path_invalidate_homapage'),
    ];
    $form['cloudfront_path_invalidate_related_paths'] = [
      '#type' => 'textarea',
      '#title' => $this->t('List comma separated related paths that you would like to invalidate together. For example:
     path/one, path/two (When path/one will be invalidated it will also invalidate path/two)'),
      '#attributes' => [
        'placeholder' => $this->t('node/12345, path/to/invalidate
path/one, path/two'),
      ],
      '#default_value' => $config->get('cloudfront_path_invalidate_related_paths'),
    ];
    $form['cloudfront_path_invalidate_host_provider'] = [
      '#type' => 'radios',
      '#title' => $this->t('Please select you host provider (this will clear
    varnish cache)'),
      '#options' => [
        $this->t('Acquia'),
        $this->t('Pantheon'),
        $this->t('None of the above'),
      ],
      '#required' => TRUE,
      '#default_value' => $config->get('cloudfront_path_invalidate_host_provider'),
    ];
    return parent::buildForm($form, $form_state);
  }

}
