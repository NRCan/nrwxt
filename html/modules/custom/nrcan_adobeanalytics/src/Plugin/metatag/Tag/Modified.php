<?php

namespace Drupal\nrcan_adobeanalytics\Plugin\metatag\Tag;

use Drupal\metatag\Plugin\metatag\Tag\MetaNameBase;

/**
 * The Dublin Core "Service" meta tag.
 *
 * @MetatagTag(
 *   id = "dcterms_modified",
 *   label = @Translation("Modified"),
 *   description = @Translation("Page modification date"),
 *   name = "dcterms.modified",
 *   group = "dublin_core_advanced",
 *   weight = 5,
 *   type = "label",
 *   secure = FALSE,
 *   multiple = FALSE
 * )
 */
class Modified extends MetaNameBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}
