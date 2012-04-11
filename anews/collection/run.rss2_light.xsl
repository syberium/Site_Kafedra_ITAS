<?xml version="1.0" encoding="koi8-r"?>
<!-- News processing without description -->
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
