Readme
================================================================================

CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration

INTRODUCTION
================================================================================
If you have AWS CloudFront CDN setup in front of your web server
then you can use this module to invalidate pages/paths on CDN.

Features Include:

    1.  Settings page where you can enter your AWS credentials
    2.  Config page where you can pass a path to be cleared on CloudFront
    3.  Automatically Clear paths on CloudFront on node Add/Delete/Update
    4.  Acquia/Pantheon Varnish Cache clear


REQUIREMENTS
================================================================================
Drupal 8.x
CloudFront Setup
    AWS Distribution ID
    AWS Access Key
    AWS Secret Key


INSTALLATION
================================================================================
/admin/config/cloudfront_path_invalidate_settings/CDNkeys


CONFIGURATION
================================================================================

If you are hosted on Acquia the you need to add this code in your settings.php

/**
 * Override domain detection in Acquia Purge.
 */
if (isset($_ENV['AH_SITE_ENVIRONMENT'])) {
    switch ($_ENV['AH_SITE_ENVIRONMENT']) {
        case 'prod':
            // Production environment.
            $conf['acquia_purge_domains'] = array(
                'www.domain1.com',
                'www.domain2.net',
                'www.domain3.org',
            );
            break;
        case 'test':
            // Staging environment.
            $conf['acquia_purge_domains'] = array(
                'test.domain1.com',
                'test.domain2.net',
                'test.domain3.org',
            );
            break;
    }
}

Ref:
support.acquia.com/hc/en-us/articles/360005304593-Acquia-Purge-domain-detection
