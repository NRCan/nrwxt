<?php

namespace Drupal\nrcan_dcd\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Field handler for the deploy button.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("nrcan_download_release")
 */
class NrcanDownloadRelease extends FieldPluginBase {

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

    // If this is production hide this button.
    $config = \Drupal::config('nrcan_dcd.settings');
    if ($config->get('mode')) {
      return;
    }

    $term = $values->_entity;
    $source_dir = $config->get('deployment_directory') . '/' . $term->uuid();
    $path = realpath($source_dir);
    if (is_dir($path)) {
      $timestamp = filemtime($source_dir);
      $btn_date = date('Y-m-d h:ia');
    }
    else {
      return;
    }

    $options = [
      'attributes' => [
        'class' => ['button', 'button--default'],
        'title' => t('Deployed on: @date', ['@date' => $btn_date]),
      ],
      'query' => [
        'destination' => 'admin/deployment/packages',
      ],
    ];

    $url = Url::fromRoute('nrcan_dcd.download_release', ['taxonomy_term' => $term->id()], $options);
    $text = $this->t('Download');
    $link = Link::fromTextAndUrl($text, $url);
    $link_string = $link->toString();

    return $link_string;
  }

}
