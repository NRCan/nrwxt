<?php

namespace Drupal\theme_settings\Controller;

use Drupal\Core\Url;
use Drupal\Component\Utility\Html;

/**
 * Controller routines for theme upgrade pages
 */
class ThemeSettingsController extends ControllerBase {

  /**
   * Returns the administrative page
   *
   * @return array
   *   A render array representing the administrative page content.
   */
  public function adminOverview() {
    $rows = [];

    $headers = [t('Book'), t('Operations')];
    // Add any recognized books to the table list.
    foreach ($this->bookManager->getAllBooks() as $book) {
      /** @var \Drupal\Core\Url $url */
      $url = $book['url'];
      if (isset($book['options'])) {
        $url->setOptions($book['options']);
      }
      $row = [
        $this->l($book['title'], $url),
      ];
      $links = [];
      $links['edit'] = [
        'title' => t('Edit order and titles'),
        'url' => Url::fromRoute('book.admin_edit', ['node' => $book['nid']]),
      ];
      $row[] = [
        'data' => [
          '#type' => 'operations',
          '#links' => $links,
        ],
      ];
      $rows[] = $row;
    }
    return [
      '#type' => 'table',
      '#header' => $headers,
      '#rows' => $rows,
      '#empty' => t('No books available.'),
    ];
  }



}
