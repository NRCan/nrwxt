<?php

namespace Drupal\nrcan_adobeanalytics\Plugin\metatag\Tag;

use Drupal\metatag\Plugin\metatag\Tag\MetaNameBase;

/**
 * The Dublin Core "Service" meta tag.
 *
 * @MetatagTag(
 *   id = "dcterms_service",
 *   label = @Translation("Service"),
 *   description = @Translation("Adobe Anayltics Service Code. NRCAN, OSDP, etc"),
 *   name = "dcterms.service",
 *   group = "dublin_core_advanced",
 *   weight = 6,
 *   type = "label",
 *   secure = FALSE,
 *   multiple = FALSE
 * )
 */
class Service extends MetaNameBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}
