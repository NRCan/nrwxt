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
    $output = '';

    $current_url = Url::fromRoute('<current>', [], ['absolute' => 'true'])->toString();

    $nid = FALSE;
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof NodeInterface) {
      $nid = $node->id();
    }
    $request = \Drupal::request();
    $title = \Drupal::service('title_resolver')->getTitle($request, $route_match->getRouteObject());
    $lang = \Drupal::languageManager()->getCurrentLanguage()->getName();
    $date = new DrupalDateTime('now');
    $timestamp = $date->format('Y-m-d H:i:s');

    $reportproblem = t('Report a problem on this page');
    $pleaseselect = t('Please select all that apply:');
    $submitbtn = t('Submit');

    $metadata = [];
    if ($title) {
      $metadata[] = '<input type="hidden" name="pageTitle" value="'.$title.'">';
    }
    $metadata[] = '<input type="hidden" name="submissionPage" value="' . $current_url . '">';
    $metadata[] = '<input type="hidden" name="currentPage" value="' . $current_url . '">';
    $metadata[] = '<input type="hidden" name="submissionWebsite" value="' . $_SERVER['HTTP_HOST'] . '">';
    //$metadata[] = '<!-- Current URL ' . $current_url . '-->';
    //$metadata[] = '<!-- HTTP_HOST ' . $_SERVER['HTTP_HOST'] . '-->';
    //$metadata[] = '<!-- NID ' . $nid . '-->';
    $metadata[] = '<input type="hidden" name="nid" value="' . $nid . '">';
    $metadata[] = '<input type="hidden" name="lang" value="' . $lang . '">';
    $metadata_html = implode("\n", $metadata);

    $rows = $this->getFormRows();
    $row_html = '';
    foreach ($rows as &$row) {
      $row_html .= "
        <div class=\"checkbox\">
          <label for=\"{$row['id']}\"><input name=\"{$row['name']}\" id=\"{$row['id']}\" type=\"checkbox\" value=\"Yes\">
          {$row['text']}
          </label>
        </div>
        <!-- -->";
    }


    $thanks = $this->getReportThanks();
    if (FALSE) {
      $comment_label = t('Comment');
      $comment_placeholder = t('You may include a short comment with your submission.');
      $comment_personal = t('Please don’t include any personal information in your comment.');
      $contacttext = $this->getReportContact();
      $row_html .= '
<div>
  <label id="feedback-label" for="comment">'.$comment_label.'</label>
  <textarea id="comment" name="comment" class="form-control form-textarea" placeholder="'.$comment_placeholder.'" cols="50" rows="4" maxlength="500" pattern=".{2,500}"></textarea>
  <span class="text-muted small">'.$comment_personal.$contacttext.'</span>
</div>
';
    }
    $output =<<<HTML
          <div>
            <div class="gc-rprt-prblm">
              <div class="gc-rprt-prblm-frm gc-rprt-prblm-tggl">
                <form id="gc-rprt-prblm-form" action="{$formtarget}" method="POST">
                  {$metadata_html}
                  <input name="subject" type="hidden" value="{$reportproblem}">
                  <fieldset>
                    <legend><span class="field-name">{$pleaseselect}</span></legend>
                    {$row_html}
                  </fieldset>
                  <button data-wb5-click="postback@#gc-rprt-prblm-form@" type="submit" class="btn btn-primary wb-toggle" data-toggle='{"stateOff": "hide", "stateOn": "show", "selector": ".gc-rprt-prblm-tggl"}'>{$submitbtn}</button>
                </form>
              </div>
              <div class="gc-rprt-prblm-thnk gc-rprt-prblm-tggl hide">
                {$thanks}
              </div>
            </div>
          </div>
HTML;
    return [
      '#type' => 'markup',
      '#markup' => $output,
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
    $lang = $GLOBALS['language']->language;
    $contact_url = array(
      'en' => variable_get('system_contact_en', 'https://contact-contactez.nrcan-rncan.gc.ca/index.cfm?lang=eng'),
      'fr' => variable_get('system_contact_fr', 'https://contact-contactez.nrcan-rncan.gc.ca/index.cfm?lang=fra'),
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
                    <p>{$contacttext}</p>\n";
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
