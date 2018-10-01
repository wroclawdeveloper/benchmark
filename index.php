<?php

define('ROOT_DIRECTORY', __DIR__ !== DIRECTORY_SEPARATOR ? __DIR__ : '');
require 'vendor/autoload.php';
require 'lib/Autoloader.php';

spl_autoload_register('Autoloader::loader');

// Create new Timing class
$timing = new App\Timing();
$timing->setStartTime();
$time = date('d-M-Y');
$log = new App\LogWriter('logs/log-' . $time . '.txt');

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['domain'])) {

    $domain = filter_var($_POST['domain'], FILTER_SANITIZE_URL);

    $restClient = new App\RestClientLib\RestClient();
    $restClient->setRemoteHost($domain)
                ->setUriBase('/')
                ->setUseSsl(false)
                ->setUseSslTestMode(false)
                ->setBasicAuthCredentials('username', 'password')
                ->setHeaders(array('Accept' => 'text/html'));

    // make requests against service
    $response = $restClient->get('');

    if(!$restClient->error) {
        $info = $response->getCurlGetinfo();
        $render = ['Total' => $info['total_time'] ];
    }
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['compare'])) {

    $compareArr = filter_var_array($_POST['compare'], FILTER_SANITIZE_URL);

    foreach ($compareArr as $compare) {
        $restClient = new App\RestClientLib\RestClient();
        $restClient->setRemoteHost($compare)
            ->setUriBase('/')
            ->setUseSsl(false)
            ->setUseSslTestMode(false)
            ->setBasicAuthCredentials('username', 'password')
            ->setHeaders(array('Accept' => 'text/html'));

        // make requests against service
        $responses[] = $restClient->get('')->getCurlGetinfo();
    }
    $timeArr = array_column($responses, 'total_time');
    $urlArr = array_column($responses, 'url');
    $max = max($timeArr);
    $log->info(' execution time for '.$domain.' : '.$info['total_time'].', competitors :'.implode(",",$urlArr).', results :'.implode(",",$timeArr));
    if($info['total_time']>$max) {
        include 'src/Mail.php';
    }
}

include 'templates/form.php'
?>
