<?xml version="1.0" encoding="koi8-r"?>
<!-- Taking text from description which located before symbol '&lt;' -->
<xsl:stylesheet
 xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
 xmlns:fn="http://www.w3.org/2005/xpath-functions"
 xmlns:xs="http://www.w3.org/2001/XMLSchema"
 xmlns:itasxsl="http://www.itas.pstu.ru/XSL/Transform"
 version="2.0"
 exclude-result-prefixes="fn xs itasxsl">

<!-- Inclusions -->
<xsl:include href="../lib.xsl"/>

<!-- Parameters and variables -->

<xsl:param name="section">empty</xsl:param>
<xsl:param name="outputFile">empty.xml</xsl:param>
<!--<xsl:variable name="output" select="fn:concat('collection/source/',$outputFile)" as="xs:string"/>-->

<!-- Templates -->

<xsl:template match="rss">
 <xsl:if test="@version='2.0'">
  <xsl:result-document href="{$outputFile}" format="xmlOut-koi8-r">
    <document>
      <section><xsl:value-of select="$section"/></section>
      <xsl:apply-templates select="channel/item"/>
    </document>
  </xsl:result-document>
 </xsl:if>
</xsl:template>

<xsl:template match="item">
  <news>
    <title><xsl:value-of select="fn:normalize-space(title)"/></title>
    <link><xsl:value-of select="fn:normalize-space(link)"/></link>
    <xsl:variable name="dscr">
      <xsl:variable name="dscr1" select="itasxsl:string-specTrim(fn:normalize-space(fn:substring-before(description,'&amp;')),$descrMaxLen)"/>
      <xsl:variable name="dscr2" select="itasxsl:string-specTrim(fn:normalize-space(fn:substring-before(description,'&lt;')),$descrMaxLen)"/>
      <xsl:variable name="dscr3" select="itasxsl:string-specTrim(fn:normalize-space(fn:substring-before(description,'&gt;')),$descrMaxLen)"/>
      <xsl:variable name="dscrlen1" select="fn:string-length($dscr1)"/>
      <xsl:variable name="dscrlen2" select="fn:string-length($dscr2)"/>
      <xsl:variable name="dscrlen3" select="fn:string-length($dscr3)"/>
      <xsl:choose>
        <xsl:when test="($dscrlen1 &gt; 0) and (($dscrlen2 = 0) or ($dscrlen1 &lt;= $dscrlen2)) and (($dscrlen3 = 0) or ($dscrlen1 &lt;= $dscrlen3))">
          <xsl:value-of select="$dscr1"/>
        </xsl:when>
        <xsl:when test="($dscrlen2 &gt; 0) and (($dscrlen1 = 0) or ($dscrlen2 &lt;= $dscrlen1)) and (($dscrlen3 = 0) or ($dscrlen2 &lt;= $dscrlen3))">
          <xsl:value-of select="$dscr2"/>
        </xsl:when>
        <xsl:when test="($dscrlen3 &gt; 0) and (($dscrlen1 = 0) or ($dscrlen3 &lt;= $dscrlen1)) and (($dscrlen2 = 0) or ($dscrlen3 &lt;= $dscrlen2))">
          <xsl:value-of select="$dscr3"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:text> </xsl:text>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:if test="fn:string-length($dscr) &gt;= $descrMinLen">
      <description><xsl:value-of select="$dscr"/></description>
    </xsl:if>
    <date>
      <xsl:variable name="d" as="xs:string">
        <xsl:value-of select="fn:normalize-space(pubDate)"/>
      </xsl:variable>
      <day><xsl:value-of select="fn:substring($d,6,2)"/></day>
      <month><xsl:value-of select="fn:substring($d,9,3)"/></month>
      <year><xsl:value-of select="fn:substring($d,13,4)"/></year>
    </date>
  </news>
</xsl:template>

</xsl:stylesheet>
