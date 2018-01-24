<?php

namespace PhilipNewcomer\RpiTemperatureMonitorDaemon;

class TemperatureMonitorDaemonApp
{
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
     * @throws \Exception on an error condition.
     */
    public function __construct()
    {
        $config = $this->getConfig();

        $this->sensorId   = $config['sensor_id'];
        $this->requestUrl = $config['request_url'];
    }

    /**
     * Reads the configuration file and makes sure it is valid.
     *
     * @return array The configuration parameters.
     *
     * @throws \Exception if the configuration is invalid.
     */
    public function getConfig()
    {
        $config_file = '../config.php';

        if (! file_exists($config_file)) {
            throw new \Exception('Config file not present');
        }

        $config = require_once($config_file);

        if (empty($config['request_url']) || empty($config['sensor_id'])) {
            throw new \Exception('Missing required configuration settings');
        }

        return $config;
    }

    /**
     * Run the app.
     *
     * @throws \Exception on an error condition.
     */
    public function run()
    {
        $this->readSensor();

        $response = $this->sendRequest();
        $this->handleResponse($response);
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
     * @return string The Curl response body.
     */
    public function sendRequest()
    {
        $data = array(
            'temperature' => $this->temperature,
        );

        $response = curl_remote_post($this->requestUrl, $data);

        return $response;
    }

    /**
     * Handles the Curl response, and outputs the appropriate error/success message.
     *
     * @param string $response The Curl response body.
     *
     * @throws \Exception if an error occurred.
     */
    public function handleResponse($response)
    {
        $response = json_decode($response, $assoc = true);

        if (null === $response) {
            throw new \Exception('Could not connect to the remote server');
        }

        if (empty($response['type']) || 'success' !== $response['type']) {
            throw new \Exception('Remote server returned an error response');
        }

        echo $response['data'];
    }
}
