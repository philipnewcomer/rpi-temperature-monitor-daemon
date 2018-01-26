<?php

namespace PhilipNewcomer\RpiTemperatureMonitorDaemon;

use GuzzleHttp\Client;
use PhpUnitsOfMeasure\PhysicalQuantity\Temperature;

class App
{
    /**
     * @var Client
     */
    public $client;

    /**
     * @var string The secret key used to authenticate the request.
     */
    public $secretKey;

    /**
     * @var string The ID of the sensor from which to read.
     */
    public $sensorId;

    /**
     * @var string The URL of the server to send the request to.
     */
    public $remoteUrl;

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
     * @param string $remoteUrl The request URL.
     * @param string $secretKey The secret key.
     *
     * @throws \Exception on an error condition.
     */
    public function __construct($sensorId, $remoteUrl, $secretKey)
    {
        $this->secretKey = $secretKey;
        $this->sensorId = $sensorId;
        $this->remoteUrl = $remoteUrl;

        $this->client = new Client([
            'headers' => [
                'Accept' => 'application/json'
            ]
        ]);
    }

    /**
     * Run the app.
     *
     * @throws \Exception on an error condition.
     */
    public function run()
    {
        $this->readSensor();
        return $this->sendRequest();
    }

    /**
     * Reads the sensor data.
     *
     * @throws \Exception if the sensor file could not be read.
     */
    public function readSensor()
    {
        if ('dummy' === $this->sensorId) {
            // If the sensor ID is 'dummy', use some dummy data in place of a real reading so we can test the operation
            // of the software side of things without requiring the sensor hardware to be fully in place yet.

            $dummyTemperature = rand(-25, 25); // In Celsius

            $sensorData = sprintf(
                "4c 01 4b 46 7f ff 04 10 f5 : crc=f5 YES\n4c 01 4b 46 7f ff 04 10 f5 t=%s",
                $dummyTemperature * 1000
            );

            printf(
                '[Using dummy data with a temperature of %s.]' . "\n",
                $this->convertCelsiusToFahrenheit($dummyTemperature)
            );

        } else {
            $sensorFile = sprintf('/sys/bus/w1/devices/%s/w1_slave', $this->sensorId);

            if (! is_readable($sensorFile)) {
                throw new \Exception(sprintf('Could not read from %s.', $sensorFile));
            }

            $sensorData = file_get_contents($sensorFile);
        }

        $this->temperature = $this->convertCelsiusToFahrenheit($this->extractTemperature($sensorData));
    }

    /**
     * Extract the temperature from the sensor data.
     *
     * @param string $sensorData The data read from the sensor.
     *
     * @return float The temperature reading.
     *
     * @throws \Exception if the temperature was not found in the sensor data.
     */
    public function extractTemperature($sensorData)
    {
        $regex = '/t=(-?\d+)$/';

        if (! preg_match($regex, $sensorData, $matches)) {
            throw new \Exception('Could not extract temperature from the sensor data.');
        }

        return floatval($matches[1]) / 1000;
    }

    /**
     * Sends an HTTP request to the server to record the reading.
     *
     * @throws \Exception if the remote server returns a non-201 response code.
     */
    public function sendRequest()
    {
        $data = array(
            'secret_key' => $this->secretKey,
            'temperature' => $this->temperature,
        );

        $response = $this->client->post($this->remoteUrl, [
            'json' => $data
        ]);

        if (201 !== $response->getStatusCode()) {
            throw new \Exception(sprintf('Remote server returned a %s status code', $response->getStatusCode()));
        }

        return $response->getBody();
    }

    /**
     * Converts a temperature in Celsius to Fahrenheit.
     *
     * @param float $temperatureCelsius The temperature in Celsius.
     *
     * @return float The temperature in Fahrenheit.
     *
     * @throws \Exception
     */
    public function convertCelsiusToFahrenheit($temperatureCelsius)
    {
        $temperature = new Temperature($temperatureCelsius, 'celsius');
        return $temperature->toUnit('fahrenheit');
    }
}
