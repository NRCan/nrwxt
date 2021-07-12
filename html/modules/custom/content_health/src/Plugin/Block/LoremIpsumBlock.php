<?php

namespace Drupal\content_health\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a Report a problem block with which you can generate dummy text anywhere.
 *
 * @Block(
 *   id = "content_health_block",
 *   admin_label = @Translation("Report a problem block"),
 * )
 */
class ContentHealthReportBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Return the form @ Form/ContentHealthReportBlockForm.php.
    return \Drupal::formBuilder()->getForm('Drupal\content_health\Form\ContentHealthReportBlockForm');
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'content health admin');
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {

    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('content_health_report_block_settings', $form_state->getValue('content_health_report_block_settings'));
  }

}
