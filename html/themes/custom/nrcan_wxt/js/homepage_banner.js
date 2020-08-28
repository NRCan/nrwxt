/**
 * @file
 * Extends methods from core/misc/form.js.
 */

(function ($, window, Drupal, drupalSettings) {

  /**
   * Behavior for "forms_has_error_value_toggle" theme setting.
   */
  Drupal.behaviors.homepage_banner = {
    attach: function (context) {
      if (drupalSettings.nrcanWxt.homepageBanners) {
        var bg_images = drupalSettings.nrcanWxt.homepageBanners;
        $('.ip-cover-img').addClass('bg-initialized').css({'background-image': 'url(' + bg_images[Math.floor(Math.random() * bg_images.length)] + ')'});
      }
    }
  };


})(jQuery, this, Drupal, drupalSettings);
