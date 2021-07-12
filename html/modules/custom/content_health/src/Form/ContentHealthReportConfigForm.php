<?php

namespace Drupal\content_health\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class ContentHealthReportConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'content_health_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Form constructor.
    $form = parent::buildForm($form, $form_state);
    // Default settings.
    $config = $this->config('content_health.settings');

    // Page title field.
    $form['report_button_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Report a problem block title:'),
      '#default_value' => $config->get('content_health.report_button_title'),
      '#description' => $this->t('Report a problem block title'),
    ];
    // Source text field.
    $form['report_button_text'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Report a problem button text:'),
      '#default_value' => $config->get('content_health.report_button_text'),
      '#description' => $this->t('The button text.'),
    ];

    // Source text field.
    // EN https://www.nrcan.gc.ca/report-problem .
    // FR https://www.rncan.gc.ca/signaler-probleme .
    $form['report_button_target_url'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Report a problem form target URL:'),
      '#default_value' => $config->get('content_health.report_button_target_url'),
      '#description' => $this->t('The target url for the form. defaults back to nrcan.gc.ca'),
    ];



    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('content_health.settings');
    $config->set('content_health.report_button_title', $form_state->getValue('report_button_title'));
    $config->set('content_health.report_button_text', $form_state->getValue('report_button_text'));
    $config->set('content_health.report_button_target_url', $form_state->getValue('report_button_target_url'));
    $config->save();
    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'content_health.settings',
    ];
  }

}
