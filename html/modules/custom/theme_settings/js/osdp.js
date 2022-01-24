(function ($) {

OsdpAA = {};

$(document).ready(function() {

  // Attach mousedown, keyup, touchstart events to document only and catch
  // clicks on all elements.
  $(document.body).bind("mousedown keyup touchstart", function(event) {

    // Catch the closest surrounding link of a clicked element.
    $(event.target).closest("a,area").each(function() {

      // Is the clicked URL internal?
      if (OsdpAA.isInternal(this.href)) {
        // Skip 'click' tracking, if custom tracking events are bound.
        // Is download tracking activated and the file extension configured for download tracking?
        else if (OsdpAA.isDownload(this.href)) {
          // Download link clicked.
          console.log('OsdpAA Download', this);
          /*OsdpAA.track('download', {
            download: {
              text: `${this.artifact.identifier_osdp_unique_id}:${this.artifact.title_en[0]}`.substring(0, 255),
              url
            }
          });*/
        }
      }
      else {
        if ($(this).is("a[href^='mailto:'],area[href^='mailto:']")) {
          // Mailto link clicked.
          // nothing yet
        }
        else if (this.href.match(/^\w+:\/\//i)) {
          // External link clicked / No top-level cross domain clicked.
          console.log('OsdpAA External Link', this);
        }
      }
    });
  });

});

// For regex
OsdpAA.trackDownloadExtensions = "bin|csv|doc(x|m)?|exe|flv|gif|jpe?g|js|mp(2|3|4|e?g)|pdf|png|ppt(x|m)?|pps(x|m)?|txt|wav|wma|wmv|xls(x|m|b)?|xml|zip";

/**
 * Check whether this is a download URL or not.
 *
 * @param string url
 *   The web url to check.
 *
 * @return boolean
 */
OsdpAA.isDownload = function (url) {
  console.log('isDownload', url);
  var isDownload = new RegExp("\\.(" + OsdpAA.trackDownloadExtensions + ")([\?#].*)?$", "i");
  return isDownload.test(url);
};

/**
 * Check whether this is an absolute internal URL or not.
 *
 * @param string url
 *   The web url to check.
 *
 * @return boolean
 */
OsdpAA.isInternal = function (url) {
  console.log('isInternal', url);
  var isInternal = new RegExp("^(https?):\/\/" + window.location.host, "i");
  return isInternal.test(url);
};

/**
 * Extract the relative internal URL from an absolute internal URL.
 *
 * Examples:
 * - https://mydomain.com/node/1 -> /node/1
 * - https://example.com/foo/bar -> https://example.com/foo/bar
 *
 * @param string url
 *   The web url to check.
 *
 * @return string
 *   Internal website URL
 */
OsdpAA.getPageUrl = function (url) {
  console.log('getPageUrl', url);
  var extractInternalUrl = new RegExp("^(https?):\/\/" + window.location.host, "i");
  return url.replace(extractInternalUrl, '');
};

/**
 * Extract the download file extension from the URL.
 *
 * @param string url
 *   The web url to check.
 *
 * @return string
 *   The file extension of the passed url. e.g. "zip", "txt"
 */
OsdpAA.getDownloadExtension = function (url) {
  console.log('getDownloadExtension', url);
  var extractDownloadextension = new RegExp("\\.(" + OsdpAA.trackDownloadExtensions + ")([\?#].*)?$", "i");
  var extension = extractDownloadextension.exec(url);
  return (extension === null) ? '' : extension[1];
};

/**
 * Extract the download file extension from the URL.
 *
 * @param string url
 *   The web url to check.
 *
 * @return string
 *   The file extension of the passed url. e.g. "zip", "txt"
 */
OsdpAA.track = function (action, data) {
  window[this.digitalData] = data;
  setTimeout(function() {
      _satellite.track(action)
  }, 500);  
};

})(jQuery);
