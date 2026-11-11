#!/usr/bin/env bash
#
# This script is designed to automate the assembly of XigmaNAS® builds.
#
# Part of XigmaNAS® (https://www.xigmanas.com).
# Copyright © 2018-2025 XigmaNAS® <info@xigmanas.com>.
# All rights reserved.
#
# Debug script
# set -x
#

# make.sh functions moved to functions.inc
# Source functions.inc from the same path as this file, so this script 
# can be called from any directory.
. "$(dirname "$0")"/functions.inc

while true; do
	main
done
exit 0
