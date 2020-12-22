<?php
namespace Drupal\nrcan_adobeanalytics\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class NRCanAdobeAnalyticsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'nrcan_adobeanalytics_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Form constructor.
    $form = parent::buildForm($form, $form_state);
    // Default settings.
    $config = $this->config('nrcan_adobeanalytics.settings');

    // AA enabled
    $form['enabled'] = [
      '#type' => 'radios',
      '#title' => $this->t('Module Enabled'),
      '#options' => [
        'on' => $this->t('Enabled'),
        'off' => $this->t('Disabled')
      ],
      '#default_value' => $config->get('nrcan_adobeanalytics.enabled'),
      '#description' => $this->t('If enabled then the header/footer code will be included for anonymous users.'),
    ];
    // Header Code
    $form['header_code'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Header Code'),
      '#default_value' => $config->get('nrcan_adobeanalytics.header_code'),
      '#description' => $this->t('For inclusion in the header'),
    ];
    // Footer Code
    $form['footer_code'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Footer Code'),
      '#default_value' => $config->get('nrcan_adobeanalytics.footer_code'),
      '#description' => $this->t('For inclusion at the end of the body tag.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array & $form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array & $form, FormStateInterface $form_state) {
    $config = $this->config('nrcan_adobeanalytics.settings');
    $config->set('nrcan_adobeanalytics.enabled', $form_state->getValue('enabled'));
    $config->set('nrcan_adobeanalytics.header_code', $form_state->getValue('header_code'));
    $config->set('nrcan_adobeanalytics.footer_code', $form_state->getValue('footer_code'));
    $config->save();

    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['nrcan_adobeanalytics.settings', ];
  }

}
