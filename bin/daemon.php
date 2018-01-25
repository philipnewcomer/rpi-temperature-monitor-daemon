<?php

namespace PhilipNewcomer\RpiTemperatureMonitorDaemon;

require_once realpath(__DIR__ . '/../') . '/vendor/autoload.php';
require_once realpath(__DIR__ . '/../') . '/src/class.App.php';

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

    echo 'Request completed successfully.';
} catch (\Exception $exception) {
    printf(
        'Error: %s',
        $exception->getMessage()
    );
}

echo "\n";
