export DISPLAY=":10"
export XvfbLog=Xvfb.log

# Programs
export java="java"
export wrap="/usr/local/lixto/vw/wrap"
export saxon="/usr/local/saxon/saxon8.jar"

export saxRun="$java -jar $saxon"

# Directories
export homeDir="/var/www/hosts/itas.pstu.ru/htdocs/anews"
export collectDir="collection"
export wrapWrapperDir="$collectDir/wrappers"
export wrapSrcDir="$collectDir/source"
export siteintgrDir="siteintgr"
export serviceDir="service"
export htmlDir="html"

# Function WRAP
export wrapLog="wrap.log"
export wrapLogFull="wrapf.log"
export minSrcFileSize=150 #B

#Function RSS
export rssLog="rss.log"
export rss2XSL="$collectDir/run.rss2.xsl"
export rss2LightXSL="$collectDir/run.rss2_light.xsl"

#Function NDBIN
export ndbinLog="ndbin.log"
export tmpndb="tmpndb.xml" # in $collectDir
export tmpnews="tmpnews.xml" # in $collectDir
export ndbinXSL="$collectDir/run.ndbin.xsl"
export ndb="ndb.xml"
export ndbinLog="ndbin.log"
export maxNDBSize=3000000 #B
export ndbClnQnt=0 #Quantity of no deleted leaves

#Function SITEINTEGR
export tmpdata='tmpdata.xml'
export siteintegrLog='siteintgr.log'
export ntreemnuXML="$siteintgrDir/ntreemnu.xml"
export htmlcrXSL="$siteintgrDir/run.htmlcr.xsl"
export dataXML="../data.xml"
export mnucrXSL="$siteintgrDir/run.mnucr.xsl"
export mainPHP="http://itas.pstu.ru/anews.php"

#Function fileCleanIf
export ndbclnXSL="$serviceDir/run.ndbcln.xsl"
export ndbclnLog="ndbcln.log"
