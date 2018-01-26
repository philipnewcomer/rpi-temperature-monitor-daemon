<?php

namespace PhilipNewcomer\RpiTemperatureMonitorDaemon;

require_once realpath(__DIR__ . '/../') . '/vendor/autoload.php';
require_once realpath(__DIR__ . '/../') . '/src/class.App.php';

try {
    $options = getopt(null, [
        'secret_key:',
        'sensor_id:',
        'remote_url:'
    ]);

    if (empty($options['sensor_id'])) {
        throw new \Exception('The --sensor_id argument is required.');
    }

    if (empty($options['remote_url'])) {
        throw new \Exception('The --remote_url argument is required.');
    }

    if (empty($options['secret_key'])) {
        throw new \Exception('The --secret_key argument is required.');
    }

    echo (new App($options['sensor_id'], $options['remote_url'], $options['secret_key']))
        ->run();

} catch (\Exception $exception) {
    printf(
        'Error: %s',
        $exception->getMessage()
    );
}

echo "\n";
