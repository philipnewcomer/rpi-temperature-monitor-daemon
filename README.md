# Raspberry Pi Temperature Monitor Daemon

*Monitor the temperature at a remote location using a Raspberry Pi*

This repo contains the daemon counterpart to the [Temperature Monitor Server](https://github.com/philipnewcomer/rpi-temperature-monitor-server).

The daemon is a PHP script which is run on a cron job on a Raspberry Pi, which reads the temperature sensor, and sends the temperature in an HTTP request to the remote URL where it is recorded by the [server](https://github.com/philipnewcomer/rpi-temperature-monitor-server).

## Raspberry Pi Setup

 1. Write the [Raspbian](https://www.raspberrypi.org/downloads/raspbian/) disk image to the SD card.
 2. Mount the `/boot` partition, and create an empty file named `.ssh` in the partition root to enable SSH by default.
 3. Boot up the Raspberry Pi and SSH into it.
 4. Change the password for the `pi` user.
 5. Add your public key to `~/.ssh/authorized_keys`.
 6. In `/etc/ssh/sshd_config`, disable password authentication.
 7. Edit `/etc/modules` and add the following:
    ```
    w1-gpio
    w1-therm
    ```
 8. Edit `/boot/config.txt` and uncomment the following lines:
    ```
    #dtparam=i2c_arm=on
    #dtparam=i2s=on
    #dtparam=spi=on
    ```
 9. Still in `/boot/config.txt`, change the following line:
    ```
    #dtoverlay=lirc-rpi
    ```
    to:
    ```
    dtoverlay=w1-gpio
    ```
 10. Run the following commands to create the log file:
     ```
     sudo touch /var/log/temperature-monitor-daemon.log
     sudo chown pi:pi /var/log/temperature-monitor-daemon.log
     ```
 11. Install the PHP CLI interpreter, Composer, and Git:
     ```
     apt-get install php-cli composer git
     ```
 12. Clone this repo to `~/temperature-monitor-daemon`
 13. Change to `~/temperature-monitor-daemon` and run `composer install`
 14. Create an executable script `run_temperature_monitor_daemon` in the `pi` home directory with the following content:
     ```
     #/bin/sh
     
     /usr/bin/php /home/pi/temperature-monitor-daemon/bin/daemon.php \
     	--remote_url={remote_url} \
     	--sensor_id={sensor_id} \
     	--secret_key={secret_key} \
     		>> /var/log/temperature-monitor-daemon.log 2>&1
     ```
     Where `sensor_id` is the hardware ID of the temparature sensor, `remote_url` is the URL of the remote [server](https://github.com/philipnewcomer/rpi-temperature-monitor-server), and `secret_key` is the secret key defined in the server's `.env` file used to authenticate requests from the daemon.
 15. Add the following cron entry:
     ```
     */15 * * * * /home/pi/run_temperature_monitor_daemon
     ```

## FAQ

* **Why PHP and not something more suitable like Python?**  
  Because PHP is what I know at the moment. Eventually I'd like to rewrite this in Python, but for now I'm just getting up and running with PHP.
