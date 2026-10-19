<?php

namespace Drupal\nrcan_dcd\Services;

use Drupal\taxonomy\Entity\Term;
use Drupal\default_content_deploy\Exporter;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Provides service for deploying releases.
 */
class ReleaseDeployService {

  use StringTranslationTrait;

  /**
   * The DCD Exporter.
   *
   * @var \Drupal\default_content_deploy\Exporter
   */
  protected $exporter;

  /**
   * Core messenger.
   *
   * @var \Drupal\Core\Messenger\Messenger
   */
  protected $messenger;

  /**
   * The File system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * Creates a ReleaseDeployService instance.
   *
   * @param \Drupal\default_content_deploy\Exporter $exporter
   *   DCD Exporter.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The File system.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The config factory.
   */
  public function __construct(Exporter $exporter, MessengerInterface $messenger, FileSystemInterface $file_system, ConfigFactoryInterface $config) {
    $this->exporter = $exporter;
    $this->messenger = $messenger;
    $this->fileSystem = $file_system;
    $this->config = $config;
  }

  /**
   * Deploy a release.
   *
   * @param Drupal\taxonomy\Entity\TermInterface $taxonomy_term
   *   The term entity.
   *
   * @return bool
   *   Return TRUE on success FALSE on fail.
   */
  public function doDeployRelease(Term $taxonomy_term) : bool {

    // Update release information.
    _nrcan_dcd_update_release_info($taxonomy_term, 'deploy');

    $config = $this->config->get('nrcan_dcd.settings');

    // Get the export directory and ensure that it is created.
    $export_dir = $config->get('deployment_directory') . '/' . $taxonomy_term->uuid();
    if (is_dir($export_dir)) {
      $this->fileSystem->deleteRecursive($export_dir);
    }
    else {
      $this->fileSystem->prepareDirectory($export_dir, FileSystemInterface::CREATE_DIRECTORY);
    }

    // Get all nodes tagged with this release term.
    $this->exporter->setEntityTypeId('node');
    $this->exporter->setFolder($export_dir);
    $this->exporter->setForceUpdate(TRUE);
    $this->exporter->setMode('reference');
    $nids = \Drupal::entityQuery('node')
      ->condition('field_package', $taxonomy_term->id())
      ->accessCheck(FALSE)
      ->execute();
    $export_nids = array_values($nids);

    $upload_nids = [];
    if ($taxonomy_term->hasField('field_package_upload') && !$taxonomy_term->get('field_package_upload')->isEmpty()) {
      /** @var \Drupal\file\FileInterface $file */
      $file_item = $taxonomy_term->get('field_package_upload')->first();
      $file = $file_item->entity;
      $file_uri = $file->getFileUri();
      $path = $this->fileSystem->realpath($file_uri);
  
      // Load the Excel file
      $spreadsheet = IOFactory::load($path);
      $worksheet = $spreadsheet->getActiveSheet();

      // Loop through each row
      foreach ($worksheet->getRowIterator() as $row) {
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false); // Read empty cells too
        foreach ($cellIterator as $cell) { 
          $value = $cell->getValue();
          if (is_numeric($value)) {
            $upload_nids[] = $value;
          }
          break;
        }
      }
    }
    $export_nids = array_merge($export_nids, $upload_nids); 

    if (!empty($export_nids)) {
      $this->exporter->setEntityIds($export_nids);
      $this->exporter->export();
    }

    return TRUE;
  }

}
