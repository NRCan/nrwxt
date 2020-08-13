<?php

namespace Drupal\nrcan_wxt\Plugin\Preprocess;

use Drupal\wxt_bootstrap\Plugin\Preprocess\Breadcrumb as BootstrapBreadcrumb;

/**
 * Pre-processes variables for the "breadcrumb" theme hook.
 *
 * @ingroup plugins_preprocess
 *
 * @BootstrapPreprocess("breadcrumb")
 */
class Breadcrumb extends BootstrapBreadcrumb {

  /**
   * {@inheritdoc}
   */
  public function preprocess(array &$variables, $hook, array $info) {
    kint($variables);

    //parent::preprocess($variables, $hook, $info);
  }

}
