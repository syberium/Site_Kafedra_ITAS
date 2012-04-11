#!/bin/sh
. lib.sh
. vars.sh
rm -rf $wrapSrcDir/*
# ===== Start wrapping
Xvfb $DISPLAY >>$XvfbLog 2>>$XvfbLog & Xvfb_pid=$!
WRAP
kill $Xvfb_pid >>$XvfbLog 2>>$XvfbLog
# ===== Extract RSS
RSS
# ===== Writing extraction information in DB
NDBIN
# ===== Integration in ITAS Site
rm -rf $htmlDir/*
SITEINTEGR

# ===== Services operations
chmod 664 ../data.xml