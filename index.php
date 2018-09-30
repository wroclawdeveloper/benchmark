<?php

define('ROOT_DIRECTORY', __DIR__ !== DIRECTORY_SEPARATOR ? __DIR__ : '');

require ROOT_DIRECTORY.DIRECTORY_SEPARATOR.'lib/Autoloader.php';

//use App;

spl_autoload_register('Autoloader::loader');

// Create new Timing class
$timing = new App\Timing();
$timing->setStartTime();

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
    /**
     * Multiple parallel requests using RestMultiClient
     */
//    $restMultiClient = new App\RestClientLib\RestMultiClient();
//    $restMultiClient->setRemoteHost('www')
//        ->setUriBase('/')
//        ->setUseSsl(false)
//        ->setUseSslTestMode(false)
//        ->setBasicAuthCredentials('username', 'password')
//        ->setHeaders(array('Accept' => 'text/html'));
//    // make requests against service
//    $responses = $restMultiClient->get(array_values($compareArr))->getCurlGetinfoArrays();

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

}
include 'templates/form.php'
?>
