<?php

namespace PhilipNewcomer\RpiTemperatureMonitorDaemon;

function curl_remote_post( $url, $data ) {

	$curl = curl_init();

	curl_setopt( $curl, CURLOPT_URL,            $url );
	curl_setopt( $curl, CURLOPT_POST,           1 );
	curl_setopt( $curl, CURLOPT_POSTFIELDS,     http_build_query( $data ) );
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );

	$response = curl_exec( $curl );
	curl_close( $curl );

	return $response;
}
