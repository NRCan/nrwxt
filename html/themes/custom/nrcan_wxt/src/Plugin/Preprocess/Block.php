<?php

namespace Drupal\nrcan_wxt\Plugin\Preprocess;

use Drupal\bootstrap\Plugin\Preprocess\PreprocessBase;

/**
 * Pre-processes variables for the "block" theme hook.
 *
 * @ingroup plugins_preprocess
 *
 * @BootstrapPreprocess("block")
 */
class Block extends PreprocessBase {

  /**
   * {@inheritdoc}
   */
  public function preprocess(array &$variables, $hook, array $info) {

    // Language Handling.
    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $language_prefix = \Drupal::config('language.negotiation')->get('url.prefixes');
    $variables['language'] = $language;
    $variables['language_prefix'] = $language_prefix[$language];

    // Customize branding per WxT theme.
    if (isset($variables['plugin_id']) && $variables['plugin_id'] == 'system_branding_block') {
      /** @var \Drupal\wxt_library\LibraryService $wxt */
      $wxt = \Drupal::service('wxt_library.service_wxt');
      $wxt_active = $wxt->getLibraryName();
      $library_path = $wxt->getLibraryPath();

      if ($wxt_active == 'ogpl') {
        $variables['logo'] = $library_path . '/assets/logo.png';
      }
      elseif ($wxt_active == 'gc_intranet') {
        $variables['logo_sttl_svg'] = $library_path . '/assets/wmms-intra.svg';
      }
      elseif ($wxt_active == 'gcwu_fegc') {
        $variables['logo_sttl_svg'] = $library_path . '/assets/wmms.svg';
      }
      elseif ($wxt_active == 'gcweb' || $wxt_active == 'gcweb_legacy') {
        $variables['logo'] = $library_path . '/assets/sig-blk-' . $language . '.png';
        $variables['logo_svg'] = $library_path . '/assets/sig-blk-' . $language . '.svg';
      }
      elseif ($wxt_active == 'gc_intranet') {
        $variables['logo_svg'] = $library_path . '/assets/sig-blk-' . $language . '.svg';
      }
    }

    // Defaults
    $variables['site_link'] = 'https://canada.ca';
    $variables['site_name'] = t('Government of Canada');
    $variables['logo_home_link'] = 'https://www.canada.ca/' . $language . '.html';

    // NRCan Theme overrides
    $splash_config = \Drupal::config('theme_settings.splash');
    $site_name = $splash_config->get('site_name.'.$language);
    $site_fip = $splash_config->get('site_fip.'.$language);

    $variables['nrwxt_logo'] = FALSE;
    if (!empty($site_fip)) {
      $variables['nrwxt_logo'] = TRUE;
      $variables['logo'] = $site_fip;
      $variables['logo_svg'] = $site_fip;
      $variables['site_name'] = $site_name;
      $variables['site_link'] = \Drupal::urlGenerator()->generateFromRoute('<front>', [], ['absolute' => TRUE]);
      $variables['logo_home_link'] = \Drupal::urlGenerator()->generateFromRoute('<front>', [], ['absolute' => TRUE]);
    }

    if (isset($variables['plugin_id']) && $variables['plugin_id'] == 'wxt_language_block:language_interface') {
      /** @var \Drupal\wxt_library\LibraryService $wxt */
      $wxt = \Drupal::service('wxt_library.service_wxt');
      $wxt_active = $wxt->getLibraryName();
      $library_path = $wxt->getLibraryPath();

      if ($wxt_active == 'gcwu_fegc') {
        $variables['logo_sig_svg'] = $library_path . '/assets/sig-' . $language . '.svg';
      }
    }

    parent::preprocess($variables, $hook, $info);
  }

}
