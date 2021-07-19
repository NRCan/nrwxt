<?php

namespace Drupal\content_health\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;

/**
 * Provides a Report a problem block.
 *
 * https://www.webwash.net/programmatically-create-block-drupal-8/
 *
 * @Block(
 *   id = "content_health_report_block",
 *   admin_label = @Translation("NRCan Report a problem block"),
 * )
 */
class ContentHealthReportBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $data = [];
    $data['current_url'] = Url::fromRoute('<current>', [], ['absolute' => 'true'])->toString();

    $config = \Drupal::config('content_health.settings');
    $data['report_button_title'] = $config->get('content_health.report_button_title');
    $data['report_button_text'] = $config->get('content_health.report_button_text');
    $data['report_button_target_url'] = $config->get('content_health.report_button_target_url');

    $data['nid'] = FALSE;
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof NodeInterface) {
      $data['nid'] = $node->id();
    }
    $data['title'] = FALSE;
    $request = \Drupal::request();
    if ($route = $request->attributes->get(\Symfony\Cmf\Component\Routing\RouteObjectInterface::ROUTE_OBJECT)) {
      $data['title'] = \Drupal::service('title_resolver')->getTitle($request, $route);
    }
    $data['language'] = \Drupal::languageManager()->getCurrentLanguage()->getName();
    $data['timestamp'] = new DrupalDateTime('now');

    $rows = $this->getFormRows();
    $data['row_html'] = '';
    foreach ($rows as &$row) {
      $data['row_html'] .= "
        <div class=\"checkbox\">
          <label for=\"{$row['id']}\"><input name=\"{$row['name']}\" id=\"{$row['id']}\" type=\"checkbox\" value=\"Yes\">
          {$row['text']}
          </label>
        </div>
        <!-- -->";
    }

    $data['website'] = $_SERVER['HTTP_HOST'];
    $data['thanks'] = $this->getReportThanks();

    $template =<<<HTML
        <div id="feedback-button" class="hidden-print">
            <details class="brdr-0">
              <summary class="btn btn-default text-center">{{ report_button_text|t }}</summary>
              <div class="clearfix"></div>
              <div class="well row">
                <!-- {{ timestamp|date("Y-m-d H:i:s") }} -->
                <div>
                  <div class="gc-rprt-prblm">
                    <div class="gc-rprt-prblm-frm gc-rprt-prblm-tggl">
                      <form id="gc-rprt-prblm-form" action="{{ report_button_target_url|t }}" method="POST">
                      {% if title %}
                      <input type="hidden" name="pageTitle" value="{{ title }}">
                      {% if field_image %}
                      <input type="hidden" name="submissionPage" value="{{ current_url }}">
                      <input type="hidden" name="currentPage" value="{{ current_url }}">
                      <input type="hidden" name="submissionWebsite" value="{{ website }}">
                      <input type="hidden" name="nid" value="{{ nid }}">
                      <input type="hidden" name="lang" value="{{ lang }}">
                        <input name="subject" type="hidden" value="{{ report_button_title|t }}">
                        <fieldset>
                          <legend><span class="field-name">{{ 'Please select all that apply:'|t }}</span></legend>
                          {{ row_html|raw }}
                        </fieldset>
                        <button data-wb5-click="postback@#gc-rprt-prblm-form@" type="submit" class="btn btn-primary wb-toggle" data-toggle='{"stateOff": "hide", "stateOn": "show", "selector": ".gc-rprt-prblm-tggl"}'>{{ 'Submit'|t }}</button>
                      </form>
                    </div>
                    <div class="gc-rprt-prblm-thnk gc-rprt-prblm-tggl hide">
                      {{ thanks }}
                    </div>
                  </div>
                </div>
              </div>
            </details>
        </div>
HTML;

    return [
      '#type' => 'inline_template',
      '#template' => $template,
      '#context' => $data
    ];



  }

  /**
   * Get the Report form rows
   */
  protected function getFormRows() {
    $rows = [];
    $rows[] = [
      'id' => 'problem1',
      'name' => 'broken',
      'clean_name' => 'Broken Link',
      'text' => t('A link, button or video is not working'),
      // A link, button or video is not working-Un lien, un bouton ou une vidéo ne fonctionne pas
    ];
    $rows[] = [
      'id' => 'problem2',
      'name' => 'spelling',
      'clean_name' => 'Spelling Error',
      'text' => t('It has a spelling mistake'),
      // It has a spelling mistake-Il y a une erreur d'orthographe ou de grammaire
    ];
    $rows[] = [
      'id' => 'problem3',
      'name' => 'wrong',
      'clean_name' => 'Content Error',
      'text' => t('Information is missing'),
      // Information is missing-Les renseignements sont incomplets
    ];
    $rows[] = [
      'id' => 'problem4',
      'name' => 'outdated',
      'clean_name' => 'Content Outdated',
      'text' => t('Information is outdated or wrong'),
      // Information is outdated or wrong-L'information n'est plus à jour ou est erronée
    ];
    $rows[] = [
      'id' => 'problem11',
      'name' => 'find',
      'clean_name' => 'Content Missing',
      'text' => t("I can't find what I'm looking for"),
      // I can't find what I'm looking for-Je n'arrive pas à trouver ce que je cherche
    ];
    $rows[] = [
      'id' => 'problem12',
      'name' => 'other',
      'clean_name' => 'Other issue',
      'text' => t('Other issue not in this list'),
      // Other issue not in this list-Autre problème qui ne figure pas sur cette liste
    ];
    return $rows;
  }

  /**
   * Return the contact link and text.
   */
  protected function getReportContact() {
    $lang = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $contact_url = array(
      'en' => 'https://contact-contactez.nrcan-rncan.gc.ca/index.cfm?lang=eng',
      'fr' => 'https://contact-contactez.nrcan-rncan.gc.ca/index.cfm?lang=fra',
    );
    $contacttext= t('You will not receive a reply. For enquiries,&nbsp;<a href="@url">contact us</a>.', array('@url' => $contact_url[$lang]));
    return "                <p>{$contacttext}</p>";
  }

  /**
   * Return the Thanks text.
   */
  protected function getReportThanks() {
    $thankyoutext = t('Thank you for your help!');
    $contacttext = $this->getReportContact();
    return "                <h3>{$thankyoutext}</h3>
                    {$contacttext}\n";
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('content_health_report_block_settings', $form_state->getValue('content_health_report_block_settings'));
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
  }

}
