<?php
namespace Drupal\content_health\Controller;

use Drupal\Component\Utility\Html;

/**
 * Controller routines for Lorem ipsum pages.
 */
class ContentHealthController {
  /**
   * Constructs Lorem ipsum text with arguments.
   *
   * This callback is mapped to the path
   * 'content_health/generate/{paragraphs}/{phrases}'.
   *
   * @param string $paragraphs
   *   The amount of paragraphs that need to be generated.
   * @param string $phrases
   *   The maximum amount of phrases that can be generated inside a paragraph.
   */
  public function generate($paragraphs, $phrases) {
    // Default settings.
    $config = \Drupal::config('content_health.settings');

    // Report button.
    $report_button_text = $config->get('content_health.report_button_text');
    $report_button_title = $config->get('content_health.report_button_title');

    $repertory = explode(PHP_EOL, $source_text);

    $this_paragraph = '<p>TEST</p>';
    $element['#source_text'][] = Html::escape($this_paragraph);

    // $element['#title'] = SafeMarkup::checkPlain($report_button_title);
    $element['#title'] = Html::escape($report_button_title);

    // Theme function.
    $element['#theme'] = 'content_health';

    return $element;
  }

}
