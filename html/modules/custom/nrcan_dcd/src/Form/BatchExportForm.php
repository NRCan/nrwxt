<?php

namespace Drupal\nrcan_dcd\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Confirmation form for Deploy.
 */
class BatchExportForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'nrcan_dcd_batch_export_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'nrcan_dcd_batch_export.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $taxonomy_term = NULL) {
    $form = parent::buildForm($form, $form_state);

    $form_state->set('term', $taxonomy_term);

    $form['actions']['submit']['#value'] = $this->t('Deploy');

    $form['actions']['cancel'] = [
      '#type' => 'submit',
      '#value' => $this->t('Cancel'),
      '#submit' => ['::batchExportCancelButtonSubmit'],
      '#limit_validation_errors' => [],
      '#weight' => 1000,
    ];

    return $form;
  }

  /**
   * Custom submit handler for the cancel button.
   */
  public function batchExportCancelButtonSubmit(array &$form, FormStateInterface $form_state) {
    // Perform any necessary actions when the cancel button is clicked.
    // For instance, you can redirect the user to a specific page.
    $form_state->setRedirect('view.packages.list');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $release_import = \Drupal::service('nrcan_dcd.release_deploy');
    $term = $form_state->get('term');

    $release_import->doDeployRelease($term);
  }


}
