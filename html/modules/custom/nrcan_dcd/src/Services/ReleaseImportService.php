<?php

namespace Drupal\nrcan_dcd\Services;

use Drupal\taxonomy\Entity\Term;
use Drupal\default_content_deploy\Importer;
use Drupal\default_content_deploy\Exporter;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Symfony\Component\Serializer\Serializer;
use Drupal\Core\File\FileSystemInterface;

/**
 * Provides service for importing releases.
 */
class ReleaseImportService {

  use StringTranslationTrait;

  /**
   * The DCD Importer.
   *
   * @var \Drupal\default_content_deploy\Importer
   */
  protected $importer;

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
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * The entity repository interface.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Serializer.
   *
   * @var \Symfony\Component\Serializer\Serializer
   */
  protected $serializer;

  /**
   * The File system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Creates a ReleaseImportService instance.
   *
   * @param \Drupal\default_content_deploy\Importer $importer
   *   DCD Importer.
   * @param \Drupal\default_content_deploy\Exporter $exporter
   *   DCD Exporter.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The config factory.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   *   The entity repository service.
   * @param \Symfony\Component\Serializer\Serializer $serializer
   *   Serializer.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The File system.
   */
  public function __construct(Importer $importer, Exporter $exporter, MessengerInterface $messenger, ConfigFactoryInterface $config, EntityRepositoryInterface $entityRepository, Serializer $serializer, FileSystemInterface $file_system) {
    $this->importer = $importer;
    $this->exporter = $exporter;
    $this->messenger = $messenger;
    $this->config = $config;
    $this->entityRepository = $entityRepository;
    $this->serializer = $serializer;
    $this->fileSystem = $file_system;
  }

  /**
   * Import a release.
   *
   * @param Drupal\taxonomy\Entity\TermInterface $taxonomy_term
   *   The term entity.
   * @param bool $revert
   *   Flag to tell import whether this is a revert (TRUE) or not (FALSE)
   *
   * @return bool
   *   Return TRUE on success FALSE on fail.
   */
  public function doImportRelease(Term $taxonomy_term, bool $revert = FALSE) : bool {
    // Perform read only update.
    $this->importer->setForceOverride(TRUE);
    $config = $this->config->get('nrcan_dcd.settings');

    // Get the import directory depending on whether this is a
    // revert operation or not.
    if ($revert) {
      $import_dir = $config->get('revert_directory') . '/' . $taxonomy_term->uuid();
    }
    else {
      $import_dir = $config->get('deployment_directory') . '/' . $taxonomy_term->uuid();
    }

    $this->importer->setFolder($import_dir);

    $revert_base = $config->get('revert_directory');
    $revert_dir = $revert_base . '/' . $taxonomy_term->uuid();
    // If revert import is enabled and this is not a revert, then export
    // the affected content first.
    if ($config->get('enable_import_revert') && !$revert) {
      // Get the revert directory and ensure that it is created.
      $this->fileSystem->prepareDirectory($revert_dir, FileSystemInterface::CREATE_DIRECTORY);

      // Get all existing and new uuids.
      [$export_list, $new_list] = $this->prepareExportCurrent();
      // Export all existing uuids.
      $result = $this->exportCurrent($export_list, $revert_dir);
      // Record all new uuids for deletion on revert.
      $this->recordNewEntities($new_list, $revert_base, $taxonomy_term->uuid());
    }

    // Prevent release term from being re-imported.
    $this->deleteReleaseTerm($import_dir, $taxonomy_term->uuid());

    // Do the import.
    $this->importer->prepareForImport();
    $this->importer->import();
    $result = $this->importer->getResult();

    if (!empty($result)) {
      $return = TRUE;
      $action = $revert ? 'reverted' : 'imported';
      $this->messenger->addMessage(
        $this->t('@count entities successfully @action.',
          ['@count' => count($result), '@action' => $action],
        )
      );
    }
    else {
      $return = FALSE;
    }

    // Remove new entities if this is a revert.
    if ($revert) {
      $this->deleteNewEntities($revert_base, $taxonomy_term->uuid());
      _nrcan_dcd_update_release_info($taxonomy_term, 'import');
      // Update release information.
    }
    else {
      // Update release information.
      _nrcan_dcd_update_release_info($taxonomy_term, 'revert');
    }

    return $return;
  }

  /**
   * Prepare export of existing entities.
   */
  public function prepareExportCurrent() : array {
    // Iterate over import files and save which UUIDs are already in the system.
    $new_list = [];
    $export_list = [];
    foreach ($this->importer->getResult() as $uuid => $file) {
      // Get entity type from path and load entity by uuid.
      $entity_type = $file['entity_type_id'];
      $entity = $this->entityRepository->loadEntityByUuid($entity_type, $uuid);
      if ($entity) {
        // We only want nodes and media forms/pubs and data sources as the rest
        // will be taken care of by reference.
        if ($entity_type == 'node') {
          $export_list[$entity_type][] = $entity->id();
        }
        elseif ($entity_type == 'media') {
          if ($entity->bundle() == 'form_publication' || $entity->bundle() == 'data_source') {
            $export_list[$entity_type][] = $entity->id();
          }
        }
      }
      else {
        $new_list[$entity_type][] = $uuid;
      }
    }

    return [$export_list, $new_list];
  }

  /**
   * Export existing entities.
   *
   * @param array $export_list
   *   Array of IDs by entity type.
   * @param string $revert_dir
   *   The export directory.
   *
   * @return bool
   *   TRUE if completed successfully, otherwise FALSE.
   */
  public function exportCurrent(array $export_list, string $revert_dir) : bool {
    $return = FALSE;
    $summary = [];

    $entity_types = array_keys($export_list);
    foreach ($entity_types as $entity_type) {
      $this->exporter->setEntityTypeId($entity_type);
      $this->exporter->setFolder($revert_dir);
      $this->exporter->setForceUpdate(TRUE);
      $this->exporter->setMode('reference');
      $this->exporter->setEntityIds($export_list[$entity_type]);
      $result = $this->exporter->export()->getResult();

      // Build summary information by entity type exported. This is a bit more
      // complicated than it should be because we also export taxonomy_term, media
      // and file references.
      if (!empty($result)) {
        $entity_types = array_keys($result);
        foreach ($entity_types as $exported_entity_type) {
          if (!isset($summary[$exported_entity_type])) {
            $summary[$exported_entity_type] = count($result[$exported_entity_type]);
          }
          else {
            $summary[$exported_entity_type] += count($result[$exported_entity_type]);
          }
        }
      }
    }

    foreach ($summary as $exported_entity_type => $result) {
      if (!empty($result)) {
        $return = TRUE;
        $this->messenger->addMessage(
          $this->t('@exported_entity_type: @count successfully exported for backup purposes.',
            ['@exported_entity_type' => $exported_entity_type, '@count' => $result],
          )
        );
      }
    }

    return $return;
  }

  /**
   * Export new UUIDs to a file.
   *
   * @param array $new_list
   *   Array of UUIDs by entity type.
   * @param string $revert_base
   *   The export directory.
   * @param string $uuid
   *   The uuid of the release taxonomy term.
   */
  public function recordNewEntities(array $new_list, string $revert_base, string $uuid) : void {
    // Write $new_list to a json file.
    $new_uuids_json = $this->serializer->serialize($new_list, 'hal_json', [
      'json_encode_options' => JSON_PRETTY_PRINT,
    ]);

    file_put_contents("{$revert_base}/new_{$uuid}.json", $new_uuids_json);
  }

  /**
   * Read the new UUIDs file and delete the associated entities.
   *
   * @param string $revert_base
   *   The export directory.
   * @param string $uuid
   *   The uuid of the release taxonomy term.
   */
  public function deleteNewEntities(string $revert_base, string $uuid) : void {
    $new_uuids_json = file_get_contents("{$revert_base}/new_{$uuid}.json");
    $new_uuids = $this->serializer->decode($new_uuids_json, 'hal_json');
    // Nothing to do.
    if (empty($new_uuids)) {
      return;
    }

    $deleted = 0;
    foreach ($new_uuids as $entity_type => $entities) {
      foreach ($entities as $uuid) {
        $entity = $this->entityRepository->loadEntityByUuid($entity_type, $uuid);
        if ($entity) {
          $entity->delete();
          $deleted++;
        }
      }
    }

    $this->messenger->addMessage(
      $this->t('@count entities successfully deleted.',
        ['@count' => $deleted],
      )
    );
  }

  /**
   * Remove this releases import file because it has already been imported. 
   * Re-importing causes issues with batch/updating the release info.
   *
   * @param string $import_base
   *   The export directory.
   * @param string $uuid
   *   The uuid of the release taxonomy term.
   */
  public function deleteReleaseTerm(string $import_dir, string $uuid) : void {
    $file_system = \Drupal::service('file_system');
    $path = $import_dir . '/taxonomy_term/' . $uuid . '.json';
    $result = $file_system->delete($path);
    if ($result) {
      $message = 'Preventing import of release term: {term}';
      $context = ['term' => $uuid];
      \Drupal::logger('nrcan_dcd')->info($message, $context);
    }
  }

}
