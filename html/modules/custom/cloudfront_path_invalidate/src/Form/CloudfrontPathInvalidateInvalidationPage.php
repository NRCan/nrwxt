<?php

namespace Drupal\cloudfront_path_invalidate\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;

/**
 * Contains main function for path invalidation.
 */
class CloudfrontPathInvalidateInvalidationPage extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cloudfront_path_invalidate_invalidation_page';
  }

  /**
   * Main function that clears CDN and varnish cache.
   */
  public function cloudfrontPathInvalidateInvalidateOnCloudfront($paths) {
    $distribution = $this->config('cloudfront_path_invalidate.settings')->get('cloudfront_path_invalidate_distribution');
    $access_key = $this->config('cloudfront_path_invalidate.settings')->get('cloudfront_path_invalidate_access');
    $secret_key = $this->config('cloudfront_path_invalidate.settings')->get('cloudfront_path_invalidate_secret');
    if ($distribution == "" || $access_key == "" || $secret_key == "") {
      return 0;
    }
    if (in_array($this->config('cloudfront_path_invalidate.settings')->get('cloudfront_path_invalidate_homapage'), $paths)) {
      array_push($paths, "");
    }
    if ($this->config('cloudfront_path_invalidate.settings')->get('cloudfront_path_invalidate_related_paths') != '') {
      $related_paths = preg_split('/\n|\r\n?/', $this->config('cloudfront_path_invalidate.settings')->get('cloudfront_path_invalidate_related_paths'));
      foreach ($related_paths as $onepath) {
        $add_path = explode(',', $onepath);
        $add_path[0] = trim($add_path[0]);
        $add_path[1] = trim($add_path[1]);
        if (is_string($add_path[0]) && is_string($add_path[1])) {
          if (in_array($add_path[0], $paths) || in_array('/' . $add_path[0], $paths)) {
            array_push($paths, $add_path[1]);
          }
        }
      }
    }
    /*Adding "/" to all paths.*/
    array_walk($paths,
      function (&$value, $key) {
        if ($value[0] != '/') {
          $value = '/' . $value;
        }
      }
    );
    if ($this->config('cloudfront_path_invalidate.settings')->get('cloudfront_path_invalidate_host_provider') == 1) {
      pantheon_clear_edge_paths($paths);
    }
    $i = rand();
    foreach ($paths as &$url) {
      if ($this->config('cloudfront_path_invalidate.settings')->get('cloudfront_path_invalidate_host_provider') == 0) {
        $service = _acquia_purge_service();
        $service->addPath($url);
        if ($service->lockAcquire()) {
          $service->process();
          $service->lockRelease();
        }
      }
      /*Invalidating object at AWS CloudFront.*/
      $onefile = $url;
      $epoch = date('U') + $i;
      $i++;
      $xml = <<<EOD
      <InvalidationBatch>
      <Path>{$onefile}</Path>
      <CallerReference>{$distribution}{$epoch}</CallerReference>
      </InvalidationBatch>
EOD;

      // You probably don't need to change anything below here.
      $len = strlen($xml);
      $date = gmdate('D, d M Y G:i:s T');
      $sig = base64_encode(
        hash_hmac('sha1', $date, $secret_key, TRUE)
      );
      $msg = "POST /2010-11-01/distribution/{$distribution}/invalidation HTTP/1.0\r\n";
      $msg .= "Host: cloudfront.amazonaws.com\r\n";
      $msg .= "Date: {$date}\r\n";
      $msg .= "Content-Type: text/xml; charset=UTF-8\r\n";
      $msg .= "Authorization: AWS {$access_key}:{$sig}\r\n";
      $msg .= "Content-Length: {$len}\r\n\r\n";
      $msg .= $xml;
      $fp = fsockopen('ssl://cloudfront.amazonaws.com', 443,
        $errno, $errstr, 30
      );
      if (!$fp) {
        // die("Connection failed: {$errno} {$errstr}\n");.
        return 0;
      }
      if (!fwrite($fp, $msg)) {
        return 0;
      }
      $resp = '';
      while (!feof($fp)) {
        $resp .= fgets($fp, 1024);
      }
      fclose($fp);
      if ($resp = '') {
        return 0;
      }
    }
    return 1;
  }

  /**
   * Building form for single path invalidation.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['cloudfront_path_invalidate_invalidation_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter Invalidation URL without the first leading "/"
    eg. test/basic/path'),
      '#required' => TRUE,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Clear on AWS Cloudfront'),
    ];
    return $form;
  }

  /**
   * After clear on AWS Cloudfront button is clicked.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $paths = [
      htmlspecialchars($values["cloudfront_path_invalidate_invalidation_url"],
        ENT_QUOTES, 'UTF-8'),
    ];
    $response = $this->cloudfrontPathInvalidateInvalidateOnCloudfront($paths);
    if ($response) {
      $this->messenger()->addStatus($this->t('@invalidated_path has successfully been
    invalidated on CDN.', ['@invalidated_path' => $values["cloudfront_path_invalidate_invalidation_url"]]));
    }
    else {
      $this->messenger()->addStatus($this->t('Error @response: Unable to invalidate path. Please check
    your AWS Credentials.', ['@response' => $response]));
    }
  }

}
