<?php

namespace Drupal\nrcan_dcd\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Drupal\Core\File\Event\FileUploadSanitizeNameEvent;

/**
 * Returns responses for config module routes.
 */
class DownloadController extends ControllerBase {

  /**
   * The File system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The Event dispatcher.
   *
   * @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * DownloadController constructor.
   *
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The File system.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher service.
   */
  public function __construct(FileSystemInterface $file_system, ConfigFactoryInterface $config_factory, EventDispatcherInterface $event_dispatcher) {
    $this->fileSystem = $file_system;
    $this->configFactory = $config_factory;
    $this->eventDispatcher = $event_dispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('file_system'),
      $container->get('config.factory'),
      $container->get('event_dispatcher')
    );
  }

  /**
   * Return binary archive file for download.
   *
   * @param Drupal\taxonomy\Entity\TermInterface $taxonomy_term
   *   The term entity.
   *
   * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
   */
  public function downloadCompressedContent(TermInterface $taxonomy_term) : BinaryFileResponse {

    $filename = $this->compressContent($taxonomy_term);

    if ($filename) {
      $path = $this->fileSystem->getTempDirectory() . '/dcd/' . $filename;

      $headers = [
        'Content-Type' => 'application/zip',
        'Content-Description' => 'File Download',
        'Content-Disposition' => 'attachment; filename=' . $filename,
      ];

      return new BinaryFileResponse($path, 200, $headers);
    }

    // Fallback if the compression failed so the controller always returns a response
    throw new NotFoundHttpException('Could not generate download file.');
  }

  /**
   * Create a zip file.
   *
   * @param Drupal\taxonomy\Entity\TermInterface $taxonomy_term
   *   The term entity.
   *
   * @return string $filename 
   *   Return filename of zip file.
   */
  public function compressContent(TermInterface $taxonomy_term) : string {
    $config = $this->configFactory->get('nrcan_dcd.settings');

    // Get the export directory and ensure that it is created.
    $source_dir = $config->get('deployment_directory') . '/' . $taxonomy_term->uuid();
    $destination_dir = $this->fileSystem->getTempDirectory() . '/dcd/';

    $file_name = $taxonomy_term->label() . '.zip';
    $event = new FileUploadSanitizeNameEvent($file_name, 'zip');
    $this->eventDispatcher->dispatch($event);
    $sanitized_filename = $event->getFilename();

    $this->fileSystem->prepareDirectory($destination_dir, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
    $zip_file_path = $this->fileSystem->realpath($destination_dir) . '/' . $sanitized_filename;
    $zip = new \ZipArchive();
    if ($zip->open($zip_file_path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
      $source_dir = realpath($source_dir);

      $files = new \RecursiveIteratorIterator(
         new \RecursiveDirectoryIterator($source_dir, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::LEAVES_ONLY);

      foreach ($files as $name => $file) {
        if (!$file->isDir()) {
          $file_path = $file->getRealPath();
          $relative_path = substr($file_path, strlen($source_dir) + 1);
          $zip->addFile($file_path, $relative_path);
        }
      }

      $zip->close();
      return $sanitized_filename;
    }
    else {
      return '';
    }
  }

}
