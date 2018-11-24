# Raspberry Pi Temperature Monitor Daemon

*Monitor the temperature at a remote location using a Raspberry Pi*

This repo contains the daemon counterpart to the [Temperature Monitor Server](https://github.com/philipnewcomer/rpi-temperature-monitor-server).

The daemon is a PHP script which is run on a cron job on a Raspberry Pi, which reads the temperature sensor, and sends the temperature in an HTTP request to the remote URL where it is recorded by the [server](https://github.com/philipnewcomer/rpi-temperature-monitor-server).

## Raspberry Pi Setup

 1. Write the [Raspbian](https://www.raspberrypi.org/downloads/raspbian/) disk image to the SD card.
 2. Mount the `/boot` partition, and create an empty file named `ssh` in the partition root to enable SSH by default.
 3. Boot up the Raspberry Pi.
 4. Find the IP address of the Raspberry Pi on your network. `arp -na` may be helpful here.
 5. SSH into the Raspberry pi at the IP address from the previous step, using the credentials `pi`/`raspberry`.
 6. Change the password for the `pi` user by running `passwd`.
 7. Add your public key to `~/.ssh/authorized_keys` (this file may not yet exist).
 8. In `/etc/ssh/sshd_config`, disable password authentication.
 9. If connecting to the internet via WiFi, run `sudo raspi-config` and update the WiFi network credentials.
 10. Edit `/etc/modules` and add the following:
    ```
    w1-gpio
    w1-therm
    ```
 11. Edit `/boot/config.txt` and uncomment the following lines:
    ```
    #dtparam=i2c_arm=on
    #dtparam=i2s=on
    #dtparam=spi=on
    ```
 12. Still in `/boot/config.txt`, change the following line:
    ```
    #dtoverlay=lirc-rpi
    ```
    to:
    ```
    dtoverlay=w1-gpio
    ```
 13. Reboot the Raspberry Pi.
 14. Find the hardware ID of your temperature sensor by listing the files in the `/sys/bus/w1/devices/` directory. You should see two files; the one that looks like an ID is what you want:
     ```
     pi@raspberrypi:~ $ ls /sys/bus/w1/devices/
     28-00000730756e  w1_bus_master1
     ```
     In this case, `28-00000730756e` is our sensor's hardware ID.
 15. Run the following commands to create the log file:
     ```
     sudo touch /var/log/temperature-monitor-daemon.log
     sudo chown pi:pi /var/log/temperature-monitor-daemon.log
     ```
 16. Install the PHP CLI interpreter, Composer, and Git:
     ```
     sudo apt-get install php-cli composer git
     ```
 17. Clone this repo to `~/temperature-monitor-daemon`
 18. Change to `~/temperature-monitor-daemon` and run `composer install`
 19. Create an executable script `run_temperature_monitor_daemon.sh` in the `pi` home directory with the following content:
     ```
     #/bin/sh
     
     /usr/bin/php /home/pi/temperature-monitor-daemon/bin/daemon.php \
     	--remote_url={remote_url} \
     	--sensor_id={sensor_id} \
     	--secret_key={secret_key} \
     		>> /var/log/temperature-monitor-daemon.log 2>&1
     ```
     Where `sensor_id` is the hardware ID of the temperature sensor found in a previous step, `remote_url` is the URL of the remote [server](https://github.com/philipnewcomer/rpi-temperature-monitor-server), and `secret_key` is the secret key defined in the server's `.env` file used to authenticate requests from the daemon.
 20. Add the following cron entry by running `crontab -e`:
     ```
     */5 * * * * /home/pi/run_temperature_monitor_daemon.sh
     ```

## FAQ

* **Why PHP and not something more suitable like Python?**  
  Because PHP is the language I know right now. Eventually I'd like to rewrite this in Python, but for now I'm just getting it up and running with PHP.
