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
 7. Install the PHP CLI interpreter, Composer, and Git: `apt-get install php-cli composer git`
 8. Clone this repo to `~/temperature-monitor-daemon`
 9. Change to `~/temperature-monitor-daemon` and run `composer install`
 10. Add the following to the crontab:  
    `/usr/bin/php /home/pi/temperature-monitor-daemon/bin/daemon.php --sensor_id={sensor_id} --remote_url={remote_url} --secret_key={secret_key}`  
    Where `sensor_id` is the hardware ID of the temparature sensor, `remote_url` is the URL of the remote [server](https://github.com/philipnewcomer/rpi-temperature-monitor-server), and `secret_key` is the secret key defined in the server's `.env` file used to authenticate requests from the daemon.

## FAQ

* **Why PHP and not something more suitable like Python?**  
  Because PHP is what I know at the moment. Eventually I'd like to rewrite this in Python, but for now I'm just getting up and running with PHP.
