<?php

namespace PhilipNewcomer\RpiTemperatureMonitorDaemon;

require_once '../src/class.App.php';
require_once '../src/curl_remote_post.php';

try {
    $app = new TemperatureMonitorDaemonApp();
    $app->run();
} catch (\Exception $exception) {
    printf(
        'Error: %s',
        $exception->getMessage()
    );
}

echo "\n";
