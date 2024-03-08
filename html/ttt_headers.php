<h1>CPS Drupal Dev App Service [<?php print $_SERVER['WEBSITE_SITE_NAME']; ?>] - D10</h1>
<p>If you're seeing this then the gateway and apache are configured to point this domain at the deployment</p>
<pre>

<?php

header("Cache-Control: no-cache, must-revalidate"); //HTTP 1.1

header("Pragma: no-cache"); //HTTP 1.0

header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past


$headers = apache_request_headers();

foreach ($headers as $header => $value) {

 echo "$header: $value <br />\n";

}

print "\n\n-------\n\n";


print_r($_SERVER);


?>

</pre>
