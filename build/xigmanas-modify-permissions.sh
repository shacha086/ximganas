#!/bin/sh
#
# This script does Modify the file permissions.
#
# Part of XigmaNAS® (https://www.xigmanas.com).
# Copyright © 2018-2025 XigmaNAS® <info@xigmanas.com>.
# All rights reserved.
#

ROOTDIR=

echo "Modify file permissions..."

if [ ! -z "$1" ] && [ -d "$1" ]; then
  ROOTDIR=$1;
  echo "Using directory $1.";
fi

if [ -z $ROOTDIR ]; then
	echo "=> No root directory defined.";
	echo "=> Exiting..."
	exit 1;
fi

# Change directory to given root.
cd $ROOTDIR

files="
usr/bin/su
usr/bin/passwd
sbin/init
libexec/ld-elf.so.1
lib/libc.so.7
lib/libcrypt.so.5
lib/libthr.so.3
"

for file in $files; do
    echo "$file"

    # Change flags recursively, handling symlinks:
    # removing the system immutable flag (schg,schange,simmutable)

    #  CHFLAGS(1) 
    #  Putting the letters “no” before or removing the letters “no” from a
    #  keyword causes the flag to be cleared.             
    #
    #  -H      If the -R option is specified, symbolic links on the command line
    #          are followed and hence unaffected by the command.  (Symbolic
    #          links encountered during traversal are not followed.)
    chflags -RH noschg "$file"  

    # Set permissions for usr/bin/su
    if [ "$file" = "usr/bin/su" ]; then
        chmod 4755 "$file"
    fi
done

# Set permissions for rc.d files
echo "etc/rc.d/files*"
chmod -R 0555 etc/rc.d/*
