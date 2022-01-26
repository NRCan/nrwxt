<?php

namespace Drupal\theme_settings\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure example settings for this site.
 */
class ThemeSettingsSettingsForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'theme_settings_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'theme_settings.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('theme_settings.settings');

    $enable_options = [
      'on' => t('Enabled'),
      'off' => t('Disabled'),
    ];

    // The Groups

    $form['admin'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Turn Features on or off'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];

    $form['experimental'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Experimental Features. Be Careful!'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];

    $form['old'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Features that have not been migrated yet.'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];

    // The features
    $form['old']['block_update_date'] = [
      '#type' => 'radios',
      '#title' => $this->t('Block Update Date Changes'),
      '#options' => $enable_options,
      '#default_value' => $config->get('block_update_date'),
      '#description' => t('If enabled users with the permission set will not update the content edit date when saving changes.'),
    ];

    $form['admin']['bootstrap_cards'] = [
      '#type' => 'radios',
      '#title' => $this->t('Bootstrap Cards'),
      '#options' => $enable_options,
      '#default_value' => $config->get('bootstrap_cards'),
      '#description' => t('Backport of Bootstrap 4 cards for the Wet4 themes.'),
    ];

    $form['admin']['osdp'] = [
      '#type' => 'radios',
      '#title' => $this->t('OSDP Theme'),
      '#options' => $enable_options,
      '#default_value' => $config->get('osdp'),
      '#description' => t('OSDP custom theme elements.'),
    ];

    $form['admin']['ngsc'] = [
      '#type' => 'radios',
      '#title' => $this->t('NGSC Theme'),
      '#options' => $enable_options,
      '#default_value' => $config->get('ngsc'),
      '#description' => t('NGSC custom theme elements.'),
    ];

    $form['admin']['hide_gc_menu'] = [
      '#type' => 'radios',
      '#title' => $this->t('Hide GC Menu'),
      '#options' => $enable_options,
      '#default_value' => $config->get('hide_gc_menu'),
      '#description' => t('if enabled hides the entire GC Menu bar.'),
    ];

    $form['admin']['custom_bullets'] = [
      '#type' => 'radios',
      '#title' => $this->t('Custom list bullets library.'),
      '#options' => $enable_options,
      '#default_value' => $config->get('custom_bullets'),
      '#description' => t('If enabled allow lists, including the feed list on the source homepage, to use custom bullets via a wrapper data tag: data-icon="fa-calendar-o"'),
    ];

    $form['admin']['match_height'] = [
      '#type' => 'radios',
      '#title' => $this->t('Match Height library.'),
      '#options' => $enable_options,
      '#default_value' => $config->get('match_height'),
      '#description' => t('If enabled include the js match heigh library to include the intelligent equal height script.'),
    ];

    $form['admin']['custom_ga_links'] = [
      '#type' => 'radios',
      '#title' => $this->t('Custom Google Analytics for links.'),
      '#options' => $enable_options,
      '#default_value' => $config->get('custom_ga_links'),
      '#description' => t('If enabled add Google Analytics to links via data tag: data-ga="{\'category\':\'campaign\', \'mode\':\'title\'} . If you omit the label it will use the link URL or title based on the mode.'),
    ];

    $splash_config = $this->config('theme_settings.splash');

    $form['splash'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Splash Page & Header'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];

    $form['splash']['site_name_en'] = [
      '#type' => 'textarea',
      '#title' => $this->t('English Site Name'),
      '#default_value' => $splash_config->get('site_name.en'),
      '#description' => t('English Site Name on the Splash page'),
    ];

    $form['splash']['site_name_fr'] = [
      '#type' => 'textarea',
      '#title' => $this->t('French Site Name'),
      '#default_value' => $splash_config->get('site_name.fr'),
      '#description' => t('French Site Name on the Splash page'),
    ];

    $form['splash']['site_fip_en'] = [
      '#type' => 'textarea',
      '#title' => $this->t('English Header Logo (FIP)'),
      '#default_value' => $splash_config->get('site_fip.en'),
      '#description' => t('English logo for the top right throughout the site. Leave blank for default.'),
    ];

    $form['splash']['site_fip_fr'] = [
      '#type' => 'textarea',
      '#title' => $this->t('French Header Logo (FIP)'),
      '#default_value' => $splash_config->get('site_fip.fr'),
      '#description' => t('French logo for the top right throughout the site. Leave blank for default.'),
    ];

    $form['splash']['splash_bg'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Background HTML'),
      '#default_value' => $splash_config->get('splash_bg'),
      '#description' => t('HTML to render the background. See GCWeb theme for example'),
    ];

    $form['splash']['splash_tc_en'] = [
      '#type' => 'textarea',
      '#title' => $this->t('English Terms & Conditions link'),
      '#default_value' => $splash_config->get('tc_link.en'),
      '#description' => t('the full url of the splash page T&C link'),
    ];

    $form['splash']['splash_tc_fr'] = [
      '#type' => 'textarea',
      '#title' => $this->t('French Terms & Conditions link'),
      '#default_value' => $splash_config->get('tc_link.fr'),
      '#description' => t('the full url of the splash page T&C link'),
    ];


    $form['old']['left_menu'] = [
      '#type' => 'radios',
      '#title' => $this->t('Left Menu'),
      '#options' => $enable_options,
      '#default_value' => $config->get('left_menu'),
      '#description' => t('Left align the root level items in the main menu.'),
    ];

    $form['old']['admin_css'] = [
      '#type' => 'radios',
      '#title' => $this->t('Admin Theme CSS'),
      '#options' => $enable_options,
      '#default_value' => $config->get('admin_css'),
      '#description' => t('Some CSS updates for the admin theme (put radios/checkboxes in columns etc.)'),
    ];


    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $this->configFactory->getEditable('theme_settings.settings')
      ->set('bootstrap_cards', $form_state->getValue('bootstrap_cards'))
      ->set('left_menu', $form_state->getValue('left_menu'))
      ->set('admin_css', $form_state->getValue('admin_css'))
      ->set('block_update_date', $form_state->getValue('block_update_date'))
      ->set('match_height', $form_state->getValue('match_height'))
      ->set('custom_bullets', $form_state->getValue('custom_bullets'))
      ->set('custom_ga_links', $form_state->getValue('custom_ga_links'))
      ->set('osdp', $form_state->getValue('osdp'))
      ->set('ngsc', $form_state->getValue('ngsc'))
      ->set('hide_gc_menu', $form_state->getValue('hide_gc_menu'))
      ->save();

    // Save the plash configurations.
    $this->configFactory->getEditable('theme_settings.splash')
      ->set('site_name.en', $form_state->getValue('site_name_en'))
      ->set('site_name.fr', $form_state->getValue('site_name_fr'))
      ->set('site_fip.en', $form_state->getValue('site_fip_en'))
      ->set('site_fip.fr', $form_state->getValue('site_fip_fr'))
      ->set('splash_bg', $form_state->getValue('splash_bg'))
      ->set('tc_link.en', $form_state->getValue('splash_tc_en'))
      ->set('tc_link.fr', $form_state->getValue('splash_tc_fr'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
