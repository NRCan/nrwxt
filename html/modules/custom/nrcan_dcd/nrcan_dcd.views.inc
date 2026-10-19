<?php

/**
 * @file
 * Customizations for views.
 */

/**
 * Implements hook_views_data_alter().
 */
function nrcan_dcd_views_data_alter(array &$data) {

  // Add links for deploy/import release.
  $data['taxonomy_term_data']['nrcan_deploy_release'] = [
    'title' => t('Deploy link'),
    'field' => [
      'title' => t('Deploy release'),
      'help' => t('Deploys a release of content.'),
      'id' => 'nrcan_deploy_release',
    ],
  ];
  $data['taxonomy_term_data']['nrcan_import_release'] = [
    'title' => t('Import link'),
    'field' => [
      'title' => t('Import release'),
      'help' => t('Import a release of content.'),
      'id' => 'nrcan_import_release',
    ],
  ];
  $data['taxonomy_term_data']['nrcan_revert_release'] = [
    'title' => t('Revert link'),
    'field' => [
      'title' => t('Revert release'),
      'help' => t('Revert a release of content.'),
      'id' => 'nrcan_revert_release',
    ],
  ];
  $data['taxonomy_term_data']['nrcan_dowload_release'] = [
    'title' => t('Download release link'),
    'field' => [
      'title' => t('Download release'),
      'help' => t('Download a release of content.'),
      'id' => 'nrcan_download_release',
    ],
  ];
}
