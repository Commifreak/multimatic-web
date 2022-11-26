# Vaillant VR900 / VR920 data collector with chart support

This project supports collecting data of Vaillant system over VR900/920 via Smart API. Code is a bit outdated but should guide you to the right direction.

# Do not query API too often, Vaillant keeps an eye on that and may disable your account. Already obersved that!

# Installation

* `composer install` ;)
* configure db
* `yii migrate/up`

# Run the harvester

You need to setup a cronjob that runs every 5 minutes and executes `php yii harvester/cronjob`. Please note that `php` is YOUR correct php binary name (it could differ from my example).
