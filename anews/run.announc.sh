#!/bin/sh
. lib.sh
. vars.sh
# ===== Start wrapping
rm -rf $wrapSrcDir/*
Xvfb $DISPLAY >>$XvfbLog 2>>$XvfbLog & Xvfb_pid=$!

  fName="$wrapWrapperDir/announc.project.xml"
  resName="announc.xml"
  $wrap -P "$PWD/$fName" > "$wrapSrcDir/$resName" 2>> $wrapLogFull
  fs=`fileSize "$wrapSrcDir/$resName"`
  if [ $minSrcFileSize -gt $fs ]; then
    $wrap -P "$PWD/$fName" > "$wrapSrcDir/$resName" 2>> $wrapLogFull
  fi
  logIfS "$wrapSrcDir/$resName" $fName $wrapLog

kill $Xvfb_pid >>$XvfbLog 2>>$XvfbLog

fs=`fileSize "$wrapSrcDir/$resName"`
if [ $fs -gt $minSrcFileSize ]; then
  # ===== Writing extraction information in DB
  NDBIN
  # ===== Integration in ITAS Site
  rm -rf $htmlDir/*
  SITEINTEGR
fi