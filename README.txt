                            TXTML Interpreter
                       Nonlinear Narrative SMS Engine
	  			
                          Developed by Brian House
                (house@knifeandfork.org http://brianhouse.net)


REQUIREMENTS
============

    * A *nix environment, including OS X
    * Permission to run cronjobs
    * PHP version 5.2.* or higher compiled with the IMAP, SimpleXML, and mysql modules enabled
      The TXTML-interpreter uses both commandline PHP, via cronjobs, and PHP as an Apache module or attached to some other webserver
    * MySQL 5.* or higher

Plus one (or more) of the following:

    * An IMAP email account
    * An account with an SMS aggregator that has an HTTP API
    * OS X, a Bluetooth-enabled phone, and BluePhoneElite 2
    * A PC, a phone connected via serial cable, and SMSlib


INSTALLATION
============

   1. Within the extracted distribution folder at a command prompt, type ./setup/init
   2. Create a mysql database with any name. If you don't know how to do this, try here
   3. Load the schema into your database with: mysql -h YOURHOST -u YOURUSERRNAME -p'YOURPASSWORD' YOURDATABASE < setup/tables.sql
   4. Edit config.xml to match your database and imap (optional) configurations.
   5. View setup/crontab.smp. Type crontab -e and install a crontab based on this file with the appropriate paths for your system.
   6. Make the public/ folder web accessible, preferrably as a root directory (the install folder should not be visible to the web). The administration website will be available at this url.
   7. The <api> config setting should be set to http://yourinstallationpath/api/.


USING
=====

Once installed on a running webserver, visit public/

also, http://knifeandfork.org/txtml

log.log contains a stream of the interpreter's operations. Set the database to
verbose to include MySQL calls.


Copyright/License
=================

Copyright (C) 2006 Brian House

This program is free software licensed under the GNU General Public License, and you are welcome to redistribute it under certain conditions. It comes without any warranty whatsoever. See the COPYING file for details, or see <http://www.gnu.org/licenses/> if it is missing.
