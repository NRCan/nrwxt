<?php

namespace Drupal\nrcan_wxt\Plugin\Preprocess;

use Drupal\bootstrap\Plugin\Preprocess\Page as BootstrapPage;

/**
 * Pre-processes variables for the "page" theme hook.
 *
 * @ingroup plugins_preprocess
 *
 * @BootstrapPreprocess("page")
 */
class Page extends BootstrapPage {

  /**
   * {@inheritdoc}
   */
  public function preprocess(array &$variables, $hook, array $info) {

    /** @var \Drupal\wxt_library\LibraryService $wxt */
    $wxt = \Drupal::service('wxt_library.service_wxt');
    $wxt_active = $wxt->getLibraryName();
    $library_path = $wxt->getLibraryPath();

    // Language Handling.
    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $language_prefix = \Drupal::config('language.negotiation')->get('url.prefixes');
    $variables['language'] = $language;
    $variables['language_prefix'] = $language_prefix[$language];
    $variables['library_path'] = $library_path;

    // WxT homepage special handling for container-fluid.
    if (!empty($variables['node'])) {
      $node = $variables['node'];
      if ($node->hasField('layout_builder__layout')) {
        $field = $node->layout_builder__layout;
        if ($field->count() > 0) {
          $layout = $field->getSection(0)->getLayoutId();
          if ($layout === 'wxt_homepage') {
            $variables['wxt_homepage'] = TRUE;
          }
        }
      }
    }

    // Visibility settings.
    $pages = $this->theme->getSetting('wxt_search_box');
    $path = \Drupal::service('path.current')->getPath();
    $page_match = \Drupal::service('path.matcher')->matchPath($path, $pages);
    $page_match = !(0 xor $page_match);
    if (!$page_match) {
      $variables['page']['search'] = '';
    }
    $variables['is_front'] = \Drupal::service('path.matcher')->isFrontPage();

    // Footer Navigation (gcweb).
    //$wxt_active = gcweb for modern sites
    if ($wxt_active == 'gcweb' || $wxt_active == 'gcweb_legacy' || $wxt_active == 'gcwu_fegc') {
      // CDN handling.
      $gcweb_cdn = $this->theme->getSetting('wxt_gcweb_cdn');
      $gcweb_cdn_url = $this->theme->getSetting('wxt_gcweb_cdn_cmm');
      $gcweb_cdn_footer_enable = $this->theme->getSetting('wxt_gcweb_cdn_footer_enable');
      $gcweb_cdn_footer_url = $this->theme->getSetting('wxt_gcweb_cdn_footer_url');
      $gcweb_cdn_goc = $this->theme->getSetting('wxt_gcweb_cdn_goc_init');
      $gcweb_election = $this->theme->getSetting('wxt_gcweb_election');

      $variables['gcweb_cdn'] = (!empty($gcweb_cdn)) ? TRUE : FALSE;
      $variables['gcweb_cdn_url'] = ($wxt_active == 'gcweb') ? $gcweb_cdn_url : '//cdn.canada.ca/gcweb-cdn-live/sitemenu/sitemenu-';
      $variables['gcweb_cdn_footer_enable'] = (!empty($gcweb_cdn_footer_enable)) ? TRUE : FALSE;
      $variables['gcweb_cdn_footer_url'] = (!empty($gcweb_cdn_footer_url)) ? $gcweb_cdn_footer_url : NULL;
      $variables['gcweb_cdn_goc'] = (!empty($gcweb_cdn_goc)) ? TRUE : FALSE;
      $variables['gcweb_election'] = (!empty($gcweb_election)) ? TRUE : FALSE;

      $variables['gcweb'] = [
        'feedback' => [
          'en' => 'http://www.canada.ca/en/contact/feedback.html',
          'fr' => 'http://www.canada.ca/fr/contact/retroaction.html',
        ],
        'social' => [
          'en' => 'http://www.canada.ca/en/social/index.html',
          'fr' => 'http://www.canada.ca/fr/sociaux/index.html',
        ],
        'mobile' => [
          'en' => 'http://www.canada.ca/en/mobile/index.html',
          'fr' => 'http://www.canada.ca/fr/mobile/index.html',
        ],
      ];
      $variables['logo'] = $library_path . '/assets/sig-blk-' . $language . '.png';
      $variables['logo_svg'] = $library_path . '/assets/sig-blk-' . $language . '.svg';
      $variables['logo_bottom_svg'] = $library_path . '/assets/wmms-blk' . '.png';
      $variables['logo_bottom_svg'] = $library_path . '/assets/wmms-blk' . '.svg';
      //$variables['logo_home_link'] = Url::fromUri('https://www.canada.ca/' . $language . '.html');
      $variables['logo_home_link'] = 'https://www.canada.ca/' . $language . '.html';
    }
    elseif ($wxt_active == 'gc_intranet') {
      $variables['logo_svg'] = $library_path . '/assets/sig-blk-' . $language . '.svg';
    }
    /* */
    // Check if we're the homepage and if we are then do the check for banner images
      if ($variables['is_front']) {
      $backgrounds = self::get_bg_random(TRUE);
    }
    $page = &$variables['page'];
    if (count($backgrounds) == 1) {
      $page['#attached']['html_head'][] = [
        [
          '#tag' => 'style',
          '#value' => '.ip-cover-img { { background-image: ' . $backgrounds[0] . '; }',
        ],
        'nrcan_wxt'
      ];
    }
    else {
      $background_settings = json_encode($backgrounds);

      $page['#attached']['library'][] = 'nrcan_wxt/homepage_banners';
      $page['#attached']['drupalSettings']['nrcanWxt']['homepageBanners'] = $background_settings;
      kint($page['#attached']);

    }


    /* */

    parent::preprocess($variables, $hook, $info);
  }

  private static function get_bg_random($return_all = FALSE) {
    $cid = 'get_bg_random_backgrounds';
    $data = NULL;
    if ($cache = \Drupal::cache()
      ->get($cid)) {
      $data = $cache->data;
    }
    else {
      $base_path = \Drupal::service('file_system')->realpath(file_default_scheme() . "://");
      $base_url = file_create_url('public://');
      //kint($base_path);
      $folder = 'images/homepage_banners';
      $files_path = $base_path . '/' . $folder;
      //kint($files_path);
      if (is_dir($files_path)) {
        $bg_image_path = $files_path . '/*.*';
        $data = glob($bg_image_path);
        foreach($data as &$banner_file) {
          $banner_file = strtr($banner_file, array(
            $base_path => $base_url,
          ));
        }
      }
      // else use a fallback image
      else {
        //$bg_image_path = 'https://www.canada.ca/content/dam/cra-arc/images/CRAHQexterior-white.jpg';
        $data = array();
      }

      \Drupal::cache()
        ->set($cid, $data);
    }
    //kint($data);
    if ($return_all) {
      return $data;
    }
    return $data[array_rand($data)];
  }

}
