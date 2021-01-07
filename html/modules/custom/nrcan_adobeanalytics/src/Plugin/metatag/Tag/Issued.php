<?php

namespace Drupal\nrcan_adobeanalytics\Plugin\metatag\Tag;

use Drupal\metatag\Plugin\metatag\Tag\MetaNameBase;

/**
 * The Dublin Core "Service" meta tag.
 *
 * @MetatagTag(
 *   id = "dcterms_issued",
 *   label = @Translation("Issued"),
 *   description = @Translation("Page creation date"),
 *   name = "dcterms.issued",
 *   group = "dublin_core_advanced",
 *   weight = 4,
 *   type = "label",
 *   secure = FALSE,
 *   multiple = FALSE
 * )
 */
class Issued extends MetaNameBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}
