<?php

namespace Drupal\nrcan_admin\Plugin\Field\FieldFormatter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\cshs\Plugin\Field\FieldFormatter\CshsFormatterBase;

/**
 * Defines the field formatter.
 *
 * @FieldFormatter(
 *   id = "nrcan_full_hierarchy",
 *   label = @Translation("NRCan hierarchy linked"),
 *   description = @Translation("Display the full hierarchy of the taxonomy terms linked with breadcrumb link."),
 *   field_types = {
 *     "entity_reference",
 *   },
 * )
 */
class NrcanFullHierarchyFormatter extends CshsFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings(): array {
    $settings = parent::defaultSettings();
    $settings['separator'] = ' » ';

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state): array {
    $element = parent::settingsForm($form, $form_state);
    $element['separator'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Separator'),
      '#description' => $this->t('Specify a separator which will be placed in between the different hierarchy levels.'),
      '#default_value' => $this->getSetting('separator'),
    ];
    $element['linked']['#disabled'] = 'disabled';
    $element['linked']['#title'] = t('Link to breadcrumb');

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary(): array {
    $summary = parent::settingsSummary();
    $separator = $this->getSetting('separator');

    $summary[] = $this->t('Separator: @separator', [
      '@separator' => empty($separator) ? $this->t('None') : $separator,
    ]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode): array {
    $elements = [];
    $linked = TRUE;
    $reverse = $this->getSetting('reverse');
    $separator = $this->getSetting('separator') ?: ' ';

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $term) {
      $elements[$delta]['#markup'] = '<span class="breadcrumb-terms">' . \implode($separator, $this->getTermsLabels($this->getTermParents($term, !$reverse), $linked)) . '</span>';
    }

    return $elements;
  }

  /**
   * Returns the list of terms labels.
   *
   * @param \Drupal\taxonomy\TermInterface[] $list
   *   The list of terms.
   * @param bool $linked
   *   The state of whether to create a linked label.
   *
   * @return string[]|\Drupal\Core\StringTranslation\TranslatableMarkup[]
   *   The list of labels.
   */
  protected function getTermsLabels(array $list, bool $linked): array {
    $terms = [];

    foreach ($list as $item) {
      $item = $this->getTranslationFromContext($item);
      $label = $item->label();

      // If no external link or ia landing page is set then ignore. 
      if ($item->get('field_external_link')->isEmpty() || $item->get('field_ia_landing_page')->isEmpty()) {
        $link = $label;
      }
      else {
        // If the term has an external link configured use it.
        if (!$item->get('field_external_link')->isEmpty()) {
          $url = Url::fromUri($item->get('field_external_link')->uri);
          $link = Link::fromTextAndUrl($label, $url)->toString();
     
        }
        else {
          $node = $item->get('field_ia_landing_page')->entity;
          // If node was deleted then show an error.
          if (is_object($node)) {
            $link = $node->toLink($label)->toString();
          }
          else {
            $link = t('(Node not found: ' . $item->get('field_ia_landing_page')->target_id . ') ' . $label);        
          }
        }
      }

      $terms[] = $link;
    } 

    return $terms;
  }


}
