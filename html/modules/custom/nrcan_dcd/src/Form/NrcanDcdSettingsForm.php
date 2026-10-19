<?php

namespace Drupal\nrcan_dcd\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure example settings for this site.
 */
class NrcanDcdSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'nrcan_dcd_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'nrcan_dcd.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('nrcan_dcd.settings');

    $form['mode'] = [
      '#type' => 'radios',
      '#title' => $this->t('Server mode'),
      '#default_value' => $config->get('mode'),
      '#options' => [0 => $this->t('Staging'), 1 => $this->t('Production')],
      '#description' => $this->t('Select the type of server this will be, this will determine the options available through the UI.'),
    ];

    $form['deployment_directory'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Deployment directory'),
      '#default_value' => $config->get('deployment_directory'),
      '#description' => $this->t('Specify the path relative to index.php. For example: ../content/deploy'),
      '#required' => TRUE,
    ];

    $form['devel'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Devel mode'),
      '#default_value' => $config->get('devel'),
      '#description' => $this->t('This checkbox enables the devel mode, which disables the pre-serialization event subscriber.'),
    ];

    $form['enable_release_scan'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable package scan'),
      '#default_value' => $config->get('enable_release_scan'),
      '#description' => $this->t('This checkbox enables the cron job that will scan the content deploy directory for new packages.'),
    ];

    $form['enable_import_revert'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable import revert'),
      '#default_value' => $config->get('enable_import_revert'),
      '#description' => $this->t('This checkbox enables the ability to revert an import by exporting all affected content prior to import.'),
    ];

    $form['revert_directory'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Import revert directory'),
      '#default_value' => $config->get('revert_directory'),
      '#description' => $this->t('Specify the path relative to index.php. For example: ../content/revert'),
      '#states' => [
        'visible' => [
          ':input[name="enable_import_revert"]' => [
            'checked' => TRUE,
          ],
        ],
        'required' => [
          ':input[name="enable_import_revert"]' => [
            'checked' => TRUE,
          ],
        ],
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $mode = $form_state->getValue('mode');
    $deployment_directory = $form_state->getValue('deployment_directory');
    $devel = $form_state->getValue('devel');
    $enable_release_scan = $form_state->getValue('enable_release_scan');
    $enable_auto_import = $form_state->getValue('enable_auto_import');
    $enable_import_revert = $form_state->getValue('enable_import_revert');
    $revert_directory = $form_state->getValue('revert_directory');
    $this->config('nrcan_dcd.settings')
      ->set('mode', $mode)
      ->set('deployment_directory', $deployment_directory)
      ->set('devel', $devel)
      ->set('enable_release_scan', $enable_release_scan)
      ->set('enable_import_revert', $enable_import_revert)
      ->set('revert_directory', $revert_directory)
      ->save();

    parent::submitForm($form, $form_state);
  }

}
