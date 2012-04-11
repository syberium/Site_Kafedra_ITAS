<?xml version="1.0" encoding="koi8-r"?>
<xsl:stylesheet
 xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
 xmlns:xs="http://www.w3.org/2001/XMLSchema"
 xmlns:fn="http://www.w3.org/2005/xpath-functions"
 xmlns:itasxsl="http://www.itas.pstu.ru/XSL/Transform"
 exclude-result-prefixes="xs fn itasxsl"
 version="2.0">

<!--<xsl:include href="lib.xsl"/>-->

<!-- Parameters and variables -->

<xsl:param name="nTreeFile">ntree.xml</xsl:param>
<xsl:variable name="nTree" select="document($nTreeFile)" as="document-node()"/>

<xsl:param name="nbdFile">ndb.xml</xsl:param>
<xsl:variable name="ndb" select="document($nbdFile)" as="document-node()"/>

<!-- Functions -->

<!-- use in run.ndbin.xsl -->
<xsl:function name="itasxsl:test-news-struct" as="xs:boolean">
<xsl:param name="ns" as="element()"/>
  <xsl:variable name="res" select="count($ns/title)=1 and count($ns/link)=1 
    and count($ns/description)&lt;=1
	and ( count($ns/date)=0
	  or (count($ns/date)=1 and count($ns/date/day)=1 and count($ns/date/month)=1 and count($ns/date/year)=1) 
	    )" as="xs:boolean"/>
  <xsl:value-of select="$res"/>
</xsl:function>

<!-- use in run.ndbin.xsl -->
<!-- Testing length of title and link, and  link for begining with http:// -->
<xsl:function name="itasxsl:testlight-news-content" as="xs:boolean">
<xsl:param name="ns" as="element()"/>
  <xsl:variable name="title" select="fn:normalize-space($ns/title)" as="xs:string"/>
  <xsl:variable name="link" select="fn:normalize-space($ns/link)" as="xs:string"/>
  <xsl:variable name="res" select="fn:string-length($title)&gt;1 and fn:string-length($title)&lt;300
    and fn:string-length($link)&gt;10 and fn:string-length($link)&lt;255 and fn:contains($link,'http://')" as ="xs:boolean"/>
  <xsl:value-of select="$res"/>
</xsl:function>

<!-- use in run.ndbin.xsl -->
<xsl:function name="itasxsl:test-date-correct" as="xs:boolean">
<xsl:param name="date" as="element()"/>
  <xsl:variable name="day" select="fn:normalize-space($date/day)" as="xs:string"/>
  <xsl:variable name="month" select="fn:normalize-space($date/month)" as="xs:string"/>
  <xsl:variable name="year" select="fn:normalize-space($date/year)" as="xs:string"/>
  <xsl:variable name="res" select="fn:string-length($day)&gt;=1 and fn:string-length($day)&lt;=2
    and fn:string-length($month)&gt;=1 and fn:string-length($month)&lt;=9
	and ( fn:string-length($year)=2 or fn:string-length($year)=4 )" as ="xs:boolean"/>
  <xsl:variable name="dDay" select="fn:number($day)" as="xs:double"/>
  <xsl:variable name="dMnth" select="fn:number($month)" as="xs:double"/>
  <xsl:variable name="dYear" select="fn:number(itasxsl:year-len-correct($year))" as="xs:double"/>
  <xsl:variable name="mnth" select="fn:lower-case($month)" as="xs:string"/>
  <xsl:variable name="res" select="$res and  $dDay &gt;= 1 and $dDay &lt;= 31
    and $dYear &lt;= fn:year-from-date(fn:current-date())
    and ( ($dMnth &gt;= 1 and $dMnth &lt;= 12)
	  or fn:contains($mnth,'янв') or fn:contains($mnth,'jan')
	  or fn:contains($mnth,'фев') or fn:contains($mnth,'feb')
	  or fn:contains($mnth,'мар') or fn:contains($mnth,'mar')
	  or fn:contains($mnth,'апр') or fn:contains($mnth,'apr')
	  or fn:contains($mnth,'ма')  or fn:contains($mnth,'may')
	  or fn:contains($mnth,'июн') or fn:contains($mnth,'jun')
	  or fn:contains($mnth,'июл') or fn:contains($mnth,'jul')
	  or fn:contains($mnth,'авг') or fn:contains($mnth,'aug')
	  or fn:contains($mnth,'сен') or fn:contains($mnth,'sep')
	  or fn:contains($mnth,'окт') or fn:contains($mnth,'oct')
	  or fn:contains($mnth,'ноя') or fn:contains($mnth,'nov')
      or fn:contains($mnth,'дек') or fn:contains($mnth,'dec')  
	    ) " as ="xs:boolean"/>
  <xsl:value-of select="$res"/>
</xsl:function>

<!-- not in use-->
<!--
<xsl:function name="itasxsl:test-news-content" as="xs:boolean">
<xsl:param name="ns" as="element()"/>
  <xsl:variable name="res" select="itasxsl:testlight-news-content($ns)
   and ( count($ns/description)=0
	  or (count($ns/description)=1 and fn:string-length($ns/description)&gt;5) )
    and ( count($ns/date)=0
	  or (count($ns/date)=1 and itasxsl:test-date-correct($ns/date)) )
  " as="xs:boolean"/>
  <xsl:value-of select="$res"/>
</xsl:function>
-->

<!-- not in use-->
<!-- 
<xsl:function name="itasxsl:sctn-exist-in-tree" as="xs:boolean">
<xsl:param name="sctn" as="xs:string"/>
  <xsl:value-of select="$nTree/document//*[name()=$sctn and count(*)=0]"/>
</xsl:function>
-->
<!-- not in use-->
<!--
<xsl:function name="itasxsl:sctn-exist-in-db" as="xs:boolean">
<xsl:param name="sctn" as="xs:string"/>
  <xsl:value-of select="$ndb/document/news[section=$sctn][1]"/>
</xsl:function>
-->

</xsl:stylesheet>