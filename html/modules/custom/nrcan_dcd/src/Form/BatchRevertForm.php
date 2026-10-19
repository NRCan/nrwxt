<?php

namespace Drupal\nrcan_dcd\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Confirmation form for Revert.
 */
class BatchRevertForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'nrcan_dcd_batch_revert_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'nrcan_dcd_batch_revert.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $taxonomy_term = NULL) {
    $form = parent::buildForm($form, $form_state);

    $form_state->set('term', $taxonomy_term);

    $form['actions']['submit']['#value'] = $this->t('Revert');

    $form['actions']['cancel'] = [
      '#type' => 'submit',
      '#value' => $this->t('Cancel'),
      '#submit' => ['::batchRevertCancelButtonSubmit'],
      '#limit_validation_errors' => [],
      '#weight' => 1000,
    ];

    return $form;
  }

  /**
   * Custom submit handler for the cancel button.
   */
  public function batchRevertCancelButtonSubmit(array &$form, FormStateInterface $form_state) {
    // Redirect back to package listing page.
    $form_state->setRedirect('view.packages.list');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $release_import = \Drupal::service('nrcan_dcd.release_import');
    $term = $form_state->get('term');

    $release_import->doImportRelease($term, TRUE);
  }


}
