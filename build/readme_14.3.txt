		README & CHANGELOG FOR XIGMANAS® 14.3.0.5 - Jalanto

		IMPORTANT - PLEASE READ CAREFULLY

General note:
=============
Download your configuration file from the backup/restore page and store it in a safe location before upgrading your system.

Install from scratch instructions:
==================================
- Download the LiveUSB file, extract the image, and write the image to a USB media. Alternatevely, you can download the LiveCD ISO file and write it to a CD/DVD.
- Boot from the LiveCD/USB device and perform a XigmaNAS® installation onto a new boot media.

Warning:
========
Do not shortcut the installation procedure!
The LiveCD/USB image contains a file called embedded.img.xz. Do not extract this file and write the extracted image to a media.

Upgrade notes!
==============

Post-upgrade tasks:
===================
- You may need to clear your browser's cache to circumvent display issues.
- Make a backup of your configuration file and store it in a safe location.
- You may need to backup your databases and update your database tables to the latest version.

ZFS feature flags upgrade warning:
==================================
Enabling all supported feature flags on a pool can make the pool inaccessible on systems that do not support these feature flags.

Community extension "Extended GUI" warning:
===========================================
Warning! Extended GUI is not compatible with this release. Disable and remove the extension before upgrading.

Permanent restrictions:
=======================
- It is not possible to format a SoftRAID disk with MSDOS FAT16/32.
- It is not possible to encrypt a disk partition, the encryption of entire disks is supported.
- AFP shares with TimeMachine support are limited to a maximum of 4 shares.
- iperf3 is not backwards compatible with iperf2.x. Plese upgrade your client.

Minimum system requirements:
============================
Browser: Chrome, Firefox, Edge, Safari, Opera or any other recent browser.
Processor: Multicore 64-bit x86 processor or better.
Boot device: 4GB minimum for "Embedded" platform, 4GB for Full platform.
System memory: "Embedded" 2GB minimum RAM, "Full" platform 2GB minimum RAM.
An embedded installation on a USB/CF/SSD media is recommended.

 *Note: LiveCD is not supported on swap mode except installation and upgrading.
Swapless install (not recommended): physical memory 2GB RAM or higher required.
With swap*: physical memory 512MB RAM + swap 512MB minimum (swap 1024MB is recommended).

Restore configuration from backup instructions:
===============================================
1. Go to System > Backup/Restore > Restore and restore your configuration.
2. Go to Disks > Management > HDD Management
3. Select option "Clear configuration information before importing disks" and click "Import" button in the Import Disks section.
4. Re-activate S.M.A.R.T. monitoring and configure other settings on each disk.

 *Note: If you have RAID controllers but you cannot parse S.M.A.R.T. info properly, please add
  variables in System > Advanced > loader.conf to load the required kernel modules for controller
  support. Reboot and perform the above steps again.

Login error 403:
================
If you are presented with a login error 403 in the WebGUI, ensure your PC is on the same network.
By default, the Hosts Allow field under System > General is left empty. This setting allows anyone on the same network of the LAN interface to access the WebGUI.
With a space-delimited set of IP or CIDR notation, you can add computers from other networks.

HAST (Highly Available Storage):
================================
HAST configuration is still experimental in the WebGUI. You need CLI for some tasks.
To evaluate HAST, you need two of the same-configured XigmaNAS® servers.
iSCSI, CIFS, NFS, GPTUFS and ZFS on HAST is currently supported.
For master node of WebGUI, carp advskew is assumed as 0 or 1.

BUILD 14.3.0.5.10566
====================
Changes:
- Upgrade underlying OS to 14.3-RELEASE P5.
- Update translations.
- Upgrade rrdtool to v1.9.0.
- Upgrade exfat to v1.4.0.
- Upgrade python311 v3.11.10.
- Upgrade libvncserver v0.9.15.
- Upgrade rsync to v3.4.1.
- Upgrade tmux to v3.5a.
- Upgrade zoneinfo to v2025b.
- Upgrade smartmontools to v7.5.
- Upgrade pecl-APCu to v5.1.27.
- Upgrade sudo to v1.9.17p2.
- Upgrade bash to v5.3p3.
- Upgrade microcode amd to v20250729.
- Upgrade microcode intel to v20250812.
- Upgrade e2fsprogs to v1.47.3.
- Upgrade gzip to v1.14.
- Upgrade proftpd to v1.3.8d.
- Upgrade transmission to 4.1.0-beta.2
- Upgrade wsdd to v0.9.0.
- Upgrade netatalk to v4.3.2.
- Upgrade mariadb to v11.8.3.
- Upgrade mDNSResponder to v2600.140.3.
- Upgrade lighttpd to v1.4.82.
- Upgrade phpmyadmin to v5.2.3.
- Upgrade cdialog to v1.3.20251001.
- Upgrade arconf to vB27960.
- Upgrade msmtp to v1.8.32.
- Upgrade virtualbox-ose to 7.2.4.
- Upgrade php8 to v8.4.14.
- Upgrade samba to v4.22.6.
- Upgrade syncthing to v2.0.11.
- Upgrade nano to v8.7.
- Upgrade open-vm-tools to v13.0.5.
- Upgrade iperf3 to v3.20.
- Upgrade nut to v2025.11.17.
- Upgrade phpvirtualbox to 7.2-2.

New:
- New SMB use its own log file.
- New SMB Server Signing and Server/Client Encryption option settings added WebGUI.

Fixed:
- Fix start netatalk.
- Fix missing markdown module.
- Fix search function in filemanager to avoid crashes.
- Fix SATA NCQ error recovery after commit 25375b1415.
- Fix error: IANA PEN registry open failed.
- Fix incomplete speed coverage ixgbe.

Note for new user installs
==========================
XigmaNAS "embedded" install is recommended for practical all users.
Embedded is also intended to be resistant to (intentional or accidental) tampering.
The XigmaNAS "Full" install provide only some flexibility and ease for advanced OS customizations.
This is intended for some heavy advanced users that test/modify the OS.


Default login credentials:
==========================

 **** Default login credentials for the XigmaNAS® WebGUI ****
 username: admin
 password: xigmanas

 **** Default login credentials for the XigmaNAS® Console (CLI) ****
 username: root or [WebGUI username]
 password: xigmanas

 **** Default login credentials for phpVirtualBox ****
 username: admin
 password: admin

 **** Default login credentials for phpMyAdmin ****
 username: root
 password: leave blank, change password after first login!

It is recommended to change the default passwords as soon as possible.

Copyright:
==========
XigmaNAS® is copyright © 2018-2026 XigmaNAS® (info@xigmanas.com). All Rights Reserved.
XigmaNAS® is a registered trademark of Michael Zoon (zoon01@xigmanas.com). All Rights Reserved.
