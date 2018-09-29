<?php

define('ROOT_DIRECTORY', __DIR__ !== DIRECTORY_SEPARATOR ? __DIR__ : '');

require ROOT_DIRECTORY.DIRECTORY_SEPARATOR.'lib/Autoloader.php';

//use App;

spl_autoload_register('Autoloader::loader');

// Create new Timing class
$timing = new App\Timing();
$timing->setStartTime();

if(isset($_POST['domain'])) {

    $domain = filter_var($_POST['domain'], FILTER_SANITIZE_URL);

    $restClient = new App\RestClientLib\RestClient();
    $restClient->setRemoteHost($domain)
                ->setUriBase('/some_service/')
                ->setUseSsl(false)
                ->setUseSslTestMode(false)
                ->setBasicAuthCredentials('username', 'password')
                ->setHeaders(array('Accept' => 'application/json'));

    // make requests against service
    $response = $restClient->head('resource');

    if(!$restClient->error) {
        $info = $response->getCurlGetinfo();
        $total_time = $info['total_time'];
        $namelookup_time = $info['namelookup_time'];
        $connect_time = $info['connect_time'];
        $pretransfer_time = $info['pretransfer_time'];
    }

}
?>
<form name="contactform" method="post" action="index.php">
<table width="450px">
<tr>
 <td valign="top">
  <label for="domain">Website *</label>
 </td>
 <td valign="top">
  <input  type="text" name="domain" maxlength="50" size="30" value="<?= $domain ?>">
 </td>
    <?php if($total_time) { ?>
    <td valign="top">
        <label for="domain">Total time</label>
    </td>
    <td valign="top">
        <input  type="text" name="total_time" maxlength="50" size="30" value="<?= $total_time ?>">
    </td>
    <td valign="top">
        <label for="domain">Namelookup time</label>
    </td>
    <td valign="top">
        <input  type="text" name="namelookup_time" maxlength="50" size="30" value="<?= $namelookup_time ?>">
    </td>
    <td valign="top">
        <label for="domain">Connect time</label>
    </td>
    <td valign="top">
        <input  type="text" name="connect_time" maxlength="50" size="30" value="<?= $connect_time ?>">
    </td>
    <td valign="top">
        <label for="domain">Pretransfer time</label>
    </td>
    <td valign="top">
        <input  type="text" name="pretransfer_time" maxlength="50" size="30" value="<?= $pretransfer_time ?>">
    </td>
    <?php } ?>
</tr>
    <?php if($restClient->error) { ?>
        <tr>
            <td colspan="2" style="text-align:center">
                <span style="color:red;"><?= $restClient->error ?></span>
            </td>
        </tr>
    <?php } ?>
<tr>
 <td colspan="2" style="text-align:center">
  <input type="submit" value="Submit">
 </td>
</tr>
</table>
</form>

<!--blocks the pop up asking for form resubmission on refresh-->
<script>
    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }
</script>