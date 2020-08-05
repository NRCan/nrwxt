<?php

namespace Drupal\theme_settings\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure example settings for this site.
 */
class ThemeUpgradesSettingsForm extends ConfigFormBase {
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

    $enable_options = array(
      'on' => t('Enabled'),
      'off' => t('Disabled'),
    );

    // The Groups

    $form['admin'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Turn Features on or off'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    );

    $form['experimental'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Experimental Features. Be Careful!'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    );

    $form['old'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Features that have not been migrated yet.'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    );

    // The features

    $form['admin']['bootstrap_cards'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Bootstrap Cards'),
      '#options' => $enable_options,
      '#default_value' => $config->get('bootstrap_cards', 'off'),
      '#description' => t('Backport of Bootstrap 4 cards for the Wet4 themes.'),
    );

    $form['old']['left_menu'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Left Menu'),
      '#options' => $enable_options,
      '#default_value' => $config->get('left_menu', 'on'),
      '#description' => t('Left align the root level items in the main menu.'),
    );

    $form['old']['admin_css'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Admin Theme CSS'),
      '#options' => $enable_options,
      '#default_value' => $config->get('admin_css', 'on'),
      '#description' => t('Some CSS updates for the admin theme (put radios/checkboxes in columns etc.)'),
    );

    $form['experimental']['wet5'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Enable Wet5 test code'),
      '#options' => $enable_options,
      '#default_value' => $config->get('experimental.wet5', 'off'),
      '#description' => t('Enable the WeT5 js library on top of the current theme. DEVELOPMENT CODE. TESTING ONLY.'),
    );

    $form['old']['block_update_date'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Block Update Date Changes'),
      '#options' => $enable_options,
      '#default_value' => $config->get('block_update_date', 'off'),
      '#description' => t('If enabled users with the permission set will not update the content edit date when saving changes.'),
    );

    $form['old']['custom_bullets'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Custom list bullets library.'),
      '#options' => $enable_options,
      '#default_value' => $config->get('custom_bullets', 'on'),
      '#description' => t('If enabled allow lists, including the feed list on the source homepage, to use custom bullets via a wrapper data tag: data-icon="fa-calendar-o"'),
    );

    $form['old']['custom_ga_links'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Custom Google Analytics for links.'),
      '#options' => $enable_options,
      '#default_value' => $config->get('custom_ga_links', 'on'),
      '#description' => t('If enabled add Google Analytics to links via data tag: data-ga="{\'category\':\'campaign\', \'mode\':\'title\'} . If you omit the label it will use the link URL or title based on the mode.'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
      // Retrieve the configuration
       $this->configFactory->getEditable('theme_settings.settings')
      ->set('bootstrap_cards', $form_state->getValue('bootstrap_cards'))
      ->set('left_menu', $form_state->getValue('left_menu'))
      ->set('admin_css', $form_state->getValue('admin_css'))
      ->set('block_update_date', $form_state->getValue('block_update_date'))
      ->set('custom_bullets', $form_state->getValue('custom_bullets'))
      ->set('custom_ga_links', $form_state->getValue('custom_ga_links'))
      ->set('experimental.wet5', $form_state->getValue('wet5'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
