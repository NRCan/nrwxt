<?php

namespace Drupal\cloudfront_path_invalidate\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Aws\CloudFront\CloudFrontClient;
use Aws\Exception\AwsException;

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

  public function createInvalidation($cloudFrontClient, $distributionId,
                                     $callerReference, $paths, $quantity) {
    try {
      $result = $cloudFrontClient->createInvalidation([
        'DistributionId' => $distributionId,
        'InvalidationBatch' => [
          'CallerReference' => $callerReference,
          'Paths' => [
            'Items' => $paths,
            'Quantity' => $quantity,
          ],
        ]
      ]);

      $message = '';

      if (isset($result['Location'])) {
        $message = 'The invalidation location is: ' .
          $result['Location'];
      }

      $message .= ' and the effective URI is ' .
        $result['@metadata']['effectiveUri'] . '.';

      return $message;
    } catch (AwsException $e) {
      return 'Error: ' . $e->getAwsErrorMessage();
    }
  }

  /**
   * Main function that clears CDN and varnish cache.
   */
  public function cloudfrontPathInvalidateInvalidateOnCloudfront($paths) {
    $config = $this->config('cloudfront_path_invalidate.settings');
    $distribution = $config->get('cloudfront_path_invalidate_distribution');
    //$access_key = $config->get('cloudfront_path_invalidate_access');
    //$secret_key = $config->get('cloudfront_path_invalidate_secret');

    //if ($distribution == '' || $access_key == '' || $secret_key == '') {
    if ($distribution == '') {
      return FALSE;
    }

    if (in_array($config->get('cloudfront_path_invalidate_homapage'), $paths)) {
      array_push($paths, "");
    }
    if ($config->get('cloudfront_path_invalidate_related_paths') != '') {
      $related_paths = preg_split('/\n|\r\n?/', $config->get('cloudfront_path_invalidate_related_paths'));
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
    // Adding "/" to all paths and stripping out http://domain.com domains so it's just clean paths
    array_walk($paths,
      function (&$value, $key) {
        $pattern = '/https?:\/\/.*\/(.*)/i';
        if (preg_match($pattern, $value, $matches) === 1) {
          $value = $matches[0];
        }
        if ($value[0] != '/') {
          $value = '/' . $value;
        }
      }
    );

    $_SESSION['cloudfront_path_invalidate_invalidation_value'] = implode("\n", $paths);
    $profile = $config->get('cloudfront_path_invalidate_profile');
    if (!$profile) {
      $profile = 'default';
    }
    $region = $config->get('cloudfront_path_invalidate_region');
    if (!$region) {
      $region = 'ca-central-1';
    }

    if ($config->get('cloudfront_path_invalidate_host_provider') == 1) {
      pantheon_clear_edge_paths($paths);
    }
    $i = rand();
    $callerReference = date('U') + $i;
    $quantity = count($paths);

    $cloudFrontClient = new CloudFrontClient([
      'profile' => $profile,
      'version' => '2018-06-18',
      'region' => $region,
    ]);

    $response = $this->createInvalidation($cloudFrontClient, $distribution, $callerReference, $paths, $quantity);

    //kpr($response);

    /*
    $xmlpaths = array();

    foreach ($paths as &$url) {
      if ($config->get('cloudfront_path_invalidate_host_provider') == 0) {
        $service = _acquia_purge_service();
        $service->addPath($url);
        if ($service->lockAcquire()) {
          $service->process();
          $service->lockRelease();
        }
      }
      //Invalidating object at AWS CloudFront.
      $xmlpaths[] = "    <Path>{$url}</Path>";
      $epoch = date('U') + $i;
      $i++;
    }

    $xmlpaths_rendered = implode(PHP_EOL, $xmlpaths);
    $onefile = $url;
    $epoch = date('U') + $i;
    $i++;
    $xml = <<<EOD
      <InvalidationBatch>
      {$xmlpaths_rendered}
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
    */
    return TRUE;
  }

  /**
   * Building form for single path invalidation.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    if (empty($_SESSION['cloudfront_path_invalidate_invalidation_value'])) {
      $_SESSION['cloudfront_path_invalidate_invalidation_value'] = '';
    }

    $form['cloudfront_path_invalidate_invalidation_url'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Enter Invalidation URLs, one per line. You can use wildcards eg. "/test/*" or "/*"'),
      '#default_value' => $_SESSION['cloudfront_path_invalidate_invalidation_value'],
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

    // Save the last form value to the session so we can reuse it
    $_SESSION['cloudfront_path_invalidate_invalidation_value'] = $values['cloudfront_path_invalidate_invalidation_url'];

    $paths = [];
    $paths_raw = htmlspecialchars($values['cloudfront_path_invalidate_invalidation_url'],
      ENT_QUOTES, 'UTF-8');
    if (!empty($paths_raw)) {
      $paths_raw = strtr($paths_raw, array(
        "\t" => ',',
        "\n" => ',',
        ';' => ',',
        ' ' => ',',
      ));
      $paths = explode(',', $paths_raw);
      // Remove empty rows
      array_filter($paths, fn($value) => !is_null($value) && $value !== '');
    }

    if (!empty($paths)) {
      $response = $this->cloudfrontPathInvalidateInvalidateOnCloudfront($paths);
      if ($response) {
        foreach ($paths as $url) {
          $this->messenger()->addStatus($this->t('@invalidated_path has successfully been
    invalidated on CDN.', ['@invalidated_path' => $url]));
        }
      }
      else {
        $this->messenger()->addStatus($this->t('Error @response: Unable to invalidate path(s). Please check
    your AWS Credentials.', ['@response' => $response]));
      }
    }
    else {
      $this->messenger()->addStatus($this->t('Error: No paths to invalidate.', []));
    }

  }

}
