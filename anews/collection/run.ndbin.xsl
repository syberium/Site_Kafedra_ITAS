<?xml version="1.0" encoding="koi8-r"?>
<xsl:stylesheet
 xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
 xmlns:xs="http://www.w3.org/2001/XMLSchema"
 xmlns:fn="http://www.w3.org/2005/xpath-functions"
 xmlns:itasxsl="http://www.itas.pstu.ru/XSL/Transform"
 version="2.0"
 exclude-result-prefixes="xs fn itasxsl">

<!-- Inclusions -->
<xsl:include href="../lib.xsl"/>
<xsl:include href="../testlib.xsl"/>

<!-- Parameters and variables -->

<xsl:param name="newsFile">tmpnews.xml</xsl:param>
<xsl:variable name="newsTree" select="document($newsFile)" as="document-node()"/>

<xsl:param name="outputFile">tmpndb.xml</xsl:param>
<!--<xsl:variable name="output" select="fn:concat('collection/',$outputFile)" as="xs:string"/>-->

<!-- <xsl:param name="srcNum" select="2" as="xs:integer"/> ID is used-->
<xsl:param name="srcNum"/> <!-- File name is used for unique source-->

<xsl:variable name="section" select="$newsTree/document/section" as="xs:string"/>
<xsl:variable name="curDate" select="fn:current-date()" as="xs:date"/>

<xsl:variable name="always-new-sessions">announc</xsl:variable>

<!-- Templates -->

<xsl:template match="document">
  <xsl:result-document href="{$outputFile}" format="xmlOut-koi8-r">
    <xsl:copy>
      <xsl:for-each select="$newsTree/document/news">
	    <xsl:if test="itasxsl:test-news-struct(.) and itasxsl:testlight-news-content(.)
		  and ( count(date)=0 or (count(date)=1 and itasxsl:is-date-actual(date)) )">
		 <xsl:call-template name="tmplNews"/>
   	    </xsl:if>
	  </xsl:for-each>
     <xsl:for-each select="news">
	   <xsl:variable name="ttl" as="xs:string"><xsl:value-of select="title"/></xsl:variable>
	   <xsl:variable name="dscr" as="xs:string"><xsl:value-of select="description"/></xsl:variable>
<!-- Important part "not(section='announc')" its means, that will be posted only real announcements -->
       <xsl:if test="not(section=$section and section=$always-new-sessions)
	     and not($newsTree/document/news[fn:normalize-space(title)=$ttl])">
         <xsl:copy-of select="."/>
       </xsl:if>
     </xsl:for-each>
    </xsl:copy>
  </xsl:result-document>
</xsl:template>

<xsl:template name="tmplNews">
  <xsl:copy>
    <srcNum><xsl:value-of select="$srcNum"/></srcNum>
    <section><xsl:value-of select="$section"/></section>
    <procDate><xsl:value-of select="itasxsl:transform-date($curDate)"/></procDate>
    <xsl:if test="not(date)">
      <xsl:comment>Date is absent in source</xsl:comment>
	  <!-- If date is absent we can to write either  $curDate or $emptyDate -->
      <!--<date><xsl:value-of select="$emptyDate"/></date>-->
	  <date><xsl:value-of select="itasxsl:transform-date($curDate)"/></date>
    </xsl:if>
    <xsl:for-each select="date|title|link|description">
      <xsl:choose>
        <xsl:when test="name()='date'">
          <xsl:choose>
            <xsl:when test="itasxsl:test-date-correct(.)">
              <xsl:copy><xsl:value-of select="fn:concat(itasxsl:year-len-correct(year),'-',itasxsl:transform-0-to-00(itasxsl:transform-month(month)),'-',itasxsl:transform-0-to-00(fn:number(day)))"/></xsl:copy>
            </xsl:when>
            <xsl:otherwise>
              <xsl:comment>Date is incorrect in source</xsl:comment>
              <date><xsl:value-of select="$emptyDate"/></date>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:when>
		<xsl:when test="name()='description'">
		  <xsl:if test="fn:string-length() &gt;= $descrMinLen">
		    <xsl:copy><xsl:value-of select="itasxsl:string-specTrim(.,$descrMaxLen)"/></xsl:copy>
		  </xsl:if>
		</xsl:when>
        <xsl:otherwise>
          <xsl:copy><xsl:value-of select="fn:normalize-space(.)"/></xsl:copy>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:for-each>
  </xsl:copy>
</xsl:template>


<xsl:function name="itasxsl:year-len-correct" as="xs:string">
<xsl:param name="year" as="xs:string"/>
  <xsl:choose>
    <xsl:when test="fn:string-length($year) = 2">
      <xsl:value-of select="fn:concat(20,$year)"/>
    </xsl:when>
	<xsl:when test="fn:string-length($year) = 4">
      <xsl:value-of select="$year"/>
    </xsl:when>
    <xsl:otherwise>
      XXXX
    </xsl:otherwise>
  </xsl:choose>
</xsl:function>

<xsl:function name="itasxsl:transform-0-to-00" as="xs:string">
<xsl:param name="d" as="xs:double"/>
  <xsl:choose>
    <xsl:when test="$d &lt; 10">
      <xsl:value-of select="fn:concat(0,$d)"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="$d"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:function>


<xsl:function name="itasxsl:transform-month" as="xs:double">
<xsl:param name="month" as="xs:string"/>
  <xsl:variable name="mnth" select="fn:lower-case($month)" as="xs:string"/>
  <xsl:choose>
    <xsl:when test="fn:contains($mnth,'янв') or fn:contains($mnth,'jan')">1</xsl:when>
    <xsl:when test="fn:contains($mnth,'фев') or fn:contains($mnth,'feb')">2</xsl:when>
    <xsl:when test="fn:contains($mnth,'мар') or fn:contains($mnth,'mar')">3</xsl:when>
    <xsl:when test="fn:contains($mnth,'апр') or fn:contains($mnth,'apr')">4</xsl:when>
    <xsl:when test="fn:contains($mnth,'ма')  or fn:contains($mnth,'may')">5</xsl:when>
    <xsl:when test="fn:contains($mnth,'июн') or fn:contains($mnth,'jun')">6</xsl:when>
    <xsl:when test="fn:contains($mnth,'июл') or fn:contains($mnth,'jul')">7</xsl:when>
    <xsl:when test="fn:contains($mnth,'авг') or fn:contains($mnth,'aug')">8</xsl:when>
    <xsl:when test="fn:contains($mnth,'сен') or fn:contains($mnth,'sep')">9</xsl:when>
    <xsl:when test="fn:contains($mnth,'окт') or fn:contains($mnth,'oct')">10</xsl:when>
    <xsl:when test="fn:contains($mnth,'ноя') or fn:contains($mnth,'nov')">11</xsl:when>
    <xsl:when test="fn:contains($mnth,'дек') or fn:contains($mnth,'dec')">12</xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="fn:number($mnth)"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:function>


<xsl:function name="itasxsl:is-date-actual" as="xs:boolean">
<xsl:param name="date" as="element()"/>
  <xsl:variable name="dYear" select="fn:number(itasxsl:year-len-correct($date/year))" as="xs:double"/>
  <xsl:value-of select="$dYear &gt;= fn:year-from-date($curDate)-1"/>
</xsl:function>

<xsl:function name="itasxsl:transform-date" as="xs:string">
<xsl:param name="d" as="xs:date"/>
  <xsl:value-of select="format-date($d,'[Y0001]-[M01]-[D01]','en',(),())"/>
</xsl:function>

</xsl:stylesheet>
