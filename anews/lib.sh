cd "$homeDir"

function WRAP()
{
  ls "$wrapWrapperDir" | while read fName; do
  fName="$wrapWrapperDir/$fName"
  resName=`echo $fName | awk -F '/' '{print($NF)}' | awk -F '.project.xml' '{print($1)}'`
  resName="${resName}.xml"
  $wrap -P "$PWD/$fName" > "$wrapSrcDir/$resName" 2>> $wrapLogFull
  fs=`fileSize "$wrapSrcDir/$resName"`
  if [ $minSrcFileSize -gt $fs ]; then
    $wrap -P "$PWD/$fName" > "$wrapSrcDir/$resName" 2>> $wrapLogFull
  fi
  logIfS "$wrapSrcDir/$resName" $fName $wrapLog
 done
}

# testFilename, errFilename, logFilename
function logIfS()
{
 fs=`fileSize "$1"`
 if [ $minSrcFileSize -gt $fs ]; then
  dat=`get_date`
  echo "[$dat] Error in file $2: file $1 is not exists or empty." >> $3
 fi
}

function get_date()
{
  dat=`date +%T_%d.%m.%y | tr '_' ' '`
  echo $dat
}

function RSS()
{
 getRss "http://www.securitylab.ru/_Services/Export/RSS/news.php" "$rss2XSL" itSecSurv itSecSurv_securitylab.xml
 getRss "http://www.securitylab.ru/_Services/Export/RSS/vulnerabilities.php" "$rss2XSL" itSecVulnerabil itSecVulnerabil_securitylab.xml
 getRss "http://www.securitylab.ru/_Services/Export/RSS/virus.php" "$rss2XSL" itSecVirus itSecVirus_securitylab.xml
# getRss "http://www.thevista.ru/rss.php" "$rss2LightXSL" winVista winVista_thevista.ru.xml
}

# link, xsl-file ,section, created file
function getRss()
{
 $saxRun "$1" "$2" section="$3" outputFile="$wrapSrcDir/$4"
 logIfS "$wrapSrcDir/$4" "$1" "$rssLog"
 # id=`get_id`
 # fil="$2$id.xml" #Unique file name
 # $saxRun "$1" "$2" section="$3" outputFile="$wrapSrcDir/$fil"
 # logIfS "$wrapSrcDir/$fil" "$2" "$rssLog"
}

# testFilename, distinationFile, errFilename, logFilename
function logIfElsS()
{
 if [ -s "$1" ]; then
   cp -f "$1" "$2"
 else
   dat=`get_date`
   echo "[$dat] Error in file $3: file $1 is not exists or empty." >> $4
 fi
 rm -f "$1"
}

function NDBIN()
{
 fileCleanIf $ndb $maxNDBSize $ndbClnQnt
 ls "$wrapSrcDir" | while read fName; do
  srcNum="$fName"
  fName="$wrapSrcDir/$fName"
  if [ -s "$fName" ]; then
    cp -f "$fName" "$collectDir/$tmpnews"
    #id=`get_id`
    #$saxRun $ndb $ndbinXSL srcNum=$id outputFile="$collectDir/$tmpndb" newsFile="$tmpnews"
	$saxRun $ndb $ndbinXSL srcNum="$srcNum" outputFile="$collectDir/$tmpndb" newsFile="$tmpnews"
    logIfElsS $collectDir/$tmpndb $ndb "$fName" $ndbinLog
  fi
 done
 rm -f "$collectDir/$tmpnews"
}

function SITEINTEGR()
{
 $saxRun $ntreemnuXML $htmlcrXSL outputFolder="$htmlDir"
 $saxRun $dataXML $mnucrXSL outputFile=$tmpdata src="$mainPHP"
 logIfElsS $tmpdata $dataXML $mnucrXSL $siteintegrLog
}

function fileSize()
{
 fsize=`du -b $1 | awk '{print $1}'`
 echo $fsize
}

# fileName, maxFileSize, Qnt
function fileCleanIf()
{
 fs=`fileSize $1`
 if [ $fs -gt $2 ]; then
  $saxRun "$1" $ndbclnXSL outputFile="tmp$1" newsMaxSrcQnt="$3"
  logIfElsS "$serviceDir/tmp$1" "$1" "$ndbclnXSL" "$ndbclnLog"
 fi
}

# function get_id()
# {
#   id=`date +%y%m%d%H%M%S`
#   echo $id
# }