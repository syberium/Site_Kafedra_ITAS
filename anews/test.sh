#!/bin/sh
. lib.sh
. vars.sh
# ===== Start wrapping
rm -rf $wrapSrcDir/*
Xvfb $DISPLAY >>$XvfbLog 2>>$XvfbLog & Xvfb_pid=$!
$wrap -P $PWD/collection/wrappers/microsoft.project.xml > collection/source/microsoft.xml 2>> $wrapLogFull
$wrap -P $PWD/collection/wrappers/microsoft.project.xml > collection/source/microsoft.xml 2>> $wrapLogFull
kill $Xvfb_pid >>$XvfbLog 2>>$XvfbLog
# ===== Extract RSS
#RSS
# ===== Writing extraction information in DB
NDBIN
# ===== Integration in ITAS Site
rm -rf $htmlDir/*
SITEINTEGR
