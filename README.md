iGestis Roundcube
=================

Table of content
----------------

* What is iGestis Roundcube
* iGestis Roundcube installation

What is iGestis Roundcube
-------------------------

iGestis Roundcube is a module dedicated for iGestis core software. It integrates 
the Roundcube webmail to permit users to easily access to their mails.

The following features are included :

* Access to local imap server.
* External pop or imap mail retreiving.
* Mail filtering over sieve.
* All classic roundcube features.
* Get iGestis authentity automatically.

Warning : a proper installation of Dovecot is necessary. It's also recommanded to 
configure Sieve.

iGestis Roundcube installation
------------------------------

### Debian/Ubuntu installation

The installation is quite simple from Debian or Ubuntu and if you already
have the iGestis repository.

    apt-get install igestis-roundcube

### Other operating system

Create an empty directory under modules named Roundcube and place the content of
the module inside.
