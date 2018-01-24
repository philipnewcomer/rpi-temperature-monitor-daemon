<?php

namespace PhilipNewcomer\RpiTemperatureMonitorDaemon;

define( 'APP_DIR', __DIR__ );

require_once( APP_DIR . '/inc/class.App.php' );
require_once( APP_DIR . '/inc/curl_remote_post.php' );

try {

	$app = new TemperatureMonitorDaemonApp();
	$app->run();
}
catch ( \Exception $exception ) {

	printf( 'Error: %s',
		$exception->getMessage()
	);
}

echo "\n";
