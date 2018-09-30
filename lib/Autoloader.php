<?php
class Autoloader {

    static public function loader($className)
    {
        $filename = __DIR__ . str_replace("\\", '/', $className) . ".php";

        $dirs = array(
            '/lib/', // Project specific classes (+Core Overrides)
            '/lib/RestClientLib/',
            '/src/', // Core classes example
            '/tests/',   // Unit test classes, if using PHP-Unit
        );
        $path = str_replace("/lib","",__DIR__);
        foreach ($dirs as $dir) {
            $filename = $path . $dir . end(explode("\\", $className)) . ".php";
            if (file_exists($filename)) {
                require_once($filename);
                return;
            }
        }
    }
}
