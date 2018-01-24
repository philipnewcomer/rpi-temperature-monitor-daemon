<?php

namespace PhilipNewcomer\RpiTemperatureMonitorDaemon;

use GuzzleHttp\Client;

class TemperatureMonitorDaemonApp
{
    /**
     * @var Client
     */
    public $client;

    /**
     * @var string The ID of the sensor from which to read.
     */
    public $sensorId;

    /**
     * @var string The URL of the server to send the request to.
     */
    public $requestUrl;

    /**
     * @var float The current temperature reading.
     */
    public $temperature;

    /**
     * App constructor.
     *
     * Sets up the initial state of the application.
     *
     * @param string $sensorId The sensor ID.
     * @param string $requestUrl The request URL.
     *
     * @throws \Exception on an error condition.
     */
    public function __construct($sensorId, $requestUrl)
    {
        $this->sensorId = $sensorId;
        $this->requestUrl = $requestUrl;

        $this->client = new Client();
    }

    /**
     * Run the app.
     *
     * @throws \Exception on an error condition.
     */
    public function run()
    {
        $this->readSensor();
        $this->sendRequest();
    }

    /**
     * Reads the sensor data.
     *
     * @throws \Exception if the sensor file could not be read.
     */
    public function readSensor()
    {
        $sensor_id = $this->sensorId;

        if ('dummy' === $sensor_id) {
            // If the sensor ID is 'dummy', use some dummy data in place of a real reading so we can test the operation
            // of the software side of things without requiring the sensor hardware to be fully in place yet.

            $dummy_temperature = rand(0, 40); // In Celsius.

            $sensor_data = sprintf(
                "4c 01 4b 46 7f ff 04 10 f5 : crc=f5 YES\n4c 01 4b 46 7f ff 04 10 f5 t=%s",
                $dummy_temperature * 1000
            );

            printf('[Using dummy data with a temperature of %s.]' . "\n", $dummy_temperature);

        } else {
            $input_file = sprintf('/sys/bus/w1/devices/%s/w1_slave', $sensor_id);

            if (! is_readable($input_file)) {
                throw new \Exception(sprintf('Could not read from %s.', $input_file));
            }

            $sensor_data = file_get_contents($input_file);
        }

        $temperature = $this->extractTemperature($sensor_data);

        $this->temperature = $temperature;
    }

    /**
     * Extract the temperature from the sensor data.
     *
     * @param string $sensor_data The data read from the sensor.
     *
     * @return float The temperature reading.
     *
     * @throws \Exception if the temperature was not found in the sensor data.
     */
    public function extractTemperature($sensor_data)
    {
        $regex = '/t=(\d+)$/';

        if (! preg_match($regex, $sensor_data, $matches)) {
            throw new \Exception('Could not extract temperature from the sensor data.');
        }

        $temperature_string = floatval($matches[1]);
        $temperature        = $temperature_string / 1000;

        return $temperature;
    }

    /**
     * Sends an HTTP request to the server to record the reading.
     *
     * @throws \Exception if the remote server returns a non-201 response code.
     */
    public function sendRequest()
    {
        $data = array(
            'temperature' => $this->temperature,
        );

        $response = $this->client->post($this->requestUrl, [
            'json' => $data
        ]);

        if (201 !== $response->getStatusCode()) {
            throw new \Exception(sprintf('Remote server returned a %s status code', $response->getStatusCode()));
        }
    }
}
