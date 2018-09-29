<?php

define('ROOT_DIRECTORY', __DIR__ !== DIRECTORY_SEPARATOR ? __DIR__ : '');

require ROOT_DIRECTORY.DIRECTORY_SEPARATOR.'lib/Autoloader.php';

//use App;

spl_autoload_register('Autoloader::loader');

// Create new Timing class
$timing = new App\Timing();
$timing->setStartTime();

if(isset($_POST['domain'])) {

    $domain = $_POST['domain'];

    $restClient = new App\RestClientLib\RestClient();
    $restClient->setRemoteHost($domain)
                ->setUriBase('/some_service/')
                ->setUseSsl(true)
                ->setUseSslTestMode(false)
                ->setBasicAuthCredentials('username', 'password')
                ->setHeaders(array('Accept' => 'application/json'));

// make requests against service
    $response = $restClient->head('resource');
    $info = $response->getCurlGetinfo();
    $total_time = $info['total_time'];
    $namelookup_time = $info['namelookup_time'];
    $connect_time = $info['connect_time'];
    $pretransfer_time = $info['pretransfer_time'];

    var_dump($response->getCurlGetinfo());
}

?>
<form name="contactform" method="post" action="index.php">
<table width="450px">
<tr>
 <td valign="top">
  <label for="domain">Website *</label>
 </td>
 <td valign="top">
  <input  type="text" name="domain" maxlength="50" size="30">
 </td>
</tr>

<tr>
 <td colspan="2" style="text-align:center">
  <input type="submit" value="Submit">
 </td>
</tr>
</table>
</form>