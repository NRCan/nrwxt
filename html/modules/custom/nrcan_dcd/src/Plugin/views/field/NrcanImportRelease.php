<?php

namespace Drupal\nrcan_dcd\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Field handler for import button.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("nrcan_import_release")
 */
class NrcanImportRelease extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Leave empty to avoid a query on this field.
  }

  /**
   * Define the available options.
   *
   * @return array
   *   The options.
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {

    // If this is staging hide this button.
    $config = \Drupal::config('nrcan_dcd.settings');
    $devel = $config->get('devel');
    if (!$config->get('mode')) {
      return;
    }

    $term = $values->_entity;
    // If there has been an import don't show import.
    $options = [
      'attributes' => [
        'class' => ['button', 'button--primary'],
      ],
      'query' => [
        'destination' => 'admin/deployment/packages',
      ],
    ];

    $url = Url::fromRoute('nrcan_dcd.import_release', ['taxonomy_term' => $term->id()], $options);
    $text = $this->t('Import');
    $link = Link::fromTextAndUrl($text, $url);
    $link_string = $link->toString();

    return $link_string;
  }

}
