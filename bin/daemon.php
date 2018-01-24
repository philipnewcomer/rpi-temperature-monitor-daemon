<?php

namespace PhilipNewcomer\RpiTemperatureMonitorDaemon;

require_once '../src/class.App.php';
require_once '../src/curl_remote_post.php';

try {
    $options = getopt(null, [
        'sensor_id:',
        'request_url:'
    ]);

    if (empty($options['sensor_id'])) {
        throw new \Exception('The --sensor_id argument is required.');
    }

    if (empty($options['request_url'])) {
        throw new \Exception('The --request_url argument is required.');
    }

    $app = new TemperatureMonitorDaemonApp($options['sensor_id'], $options['request_url']);
    $app->run();
} catch (\Exception $exception) {
    printf(
        'Error: %s',
        $exception->getMessage()
    );
}

echo "\n";
