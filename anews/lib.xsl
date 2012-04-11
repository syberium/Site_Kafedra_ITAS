<?xml version="1.0" encoding="koi8-r"?>
<xsl:stylesheet
 xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
 xmlns:saxon="http://saxon.sf.net/"
 xmlns:xs="http://www.w3.org/2001/XMLSchema"
 xmlns:fn="http://www.w3.org/2005/xpath-functions"
 xmlns:itasxsl="http://www.itas.pstu.ru/XSL/Transform"
 version="2.0"
 exclude-result-prefixes="saxon xs fn itasxsl">

<!-- Parameters and variables -->
<xsl:variable name="emptyDate">0001-01-01</xsl:variable>

<xsl:param name="descrMinLen">5</xsl:param>
<xsl:param name="descrMaxLen">500</xsl:param>

<xsl:param name="descrMaxLenIntegr">300</xsl:param>
<!-- Outputing methods -->

<xsl:output name="xmlOut-koi8-r" method="xml" version="1.0" encoding="koi8-r" indent="yes" saxon:indent-spaces="2"/>
<xsl:output name="xmlOut-win-1251" method="xml" version="1.0" encoding="windows-1251" indent="yes" saxon:indent-spaces="2"/>
<xsl:output name="htmlOut-koi8-r" method="html" version="4.01" encoding="koi8-r" indent="no"/>
<xsl:output name="htmlFOut-koi8-r" method="html" version="4.01" encoding="koi8-r" indent="no"
  doctype-public="-//W3C//DTD HTML 4.01 Transitional//EN" doctype-system="http://www.w3.org/TR/html4/loose.dtd"/>

<!-- Functions -->

<xsl:function name="itasxsl:getBaseUrl" as="xs:string">
<xsl:param name="url" as="xs:string"/>
  <xsl:variable name="baseUrl" select="fn:substring-after($url,'://')"/>
  <xsl:choose>
    <xsl:when test="fn:contains($baseUrl,'/')">
      <xsl:variable name="baseUrl" select="fn:substring-before($baseUrl,'/')"/>
      <xsl:variable name="baseUrl" select="fn:concat('http://',$baseUrl,'/')"/>
      <xsl:value-of select="$baseUrl"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:variable name="baseUrl" select="fn:concat('http://',$baseUrl,'/')"/>
      <xsl:value-of select="$baseUrl"/>
    </xsl:otherwise>
  </xsl:choose>  
</xsl:function>

<xsl:function name="itasxsl:printableURL" as="xs:string">
<xsl:param name="url" as="xs:string"/>
  <xsl:variable name="printableUrl" select="fn:escape-html-uri($url)"/>
  <xsl:variable name="printableUrl" select="fn:lower-case($printableUrl)"/>
  <xsl:value-of select="$printableUrl"/>
</xsl:function>

<xsl:function name="itasxsl:string-specTrim" as="xs:string">
<xsl:param name="str" as="xs:string"/>
<xsl:param name="maxLen" as="xs:integer"/>
  <xsl:choose>
    <xsl:when test="fn:string-length($str) &gt; $maxLen">
      <xsl:variable name="s" select="fn:substring($str,1,$maxLen)" as="xs:string"/>
	  <xsl:choose>
	  <xsl:when test="fn:substring($s,$maxLen - 2,3)='...'">
		<xsl:value-of select="$s"/>
	  </xsl:when>
	  <xsl:when test="fn:substring($s,$maxLen)='.'">
	    <xsl:variable name="s" select="fn:concat($s,'..')" as="xs:string"/>
		<xsl:value-of select="$s"/>
	  </xsl:when>
	  <xsl:when test="fn:substring($s,$maxLen)=' '">
	    <xsl:variable name="s" select="fn:concat(fn:substring($s,1,$maxLen - 1),'...')" as="xs:string"/>
		<xsl:value-of select="$s"/>
	  </xsl:when>
	  <xsl:otherwise>
        <xsl:variable name="s" select="fn:concat($s,'...')" as="xs:string"/>
		<xsl:value-of select="$s"/>
	  </xsl:otherwise>
      </xsl:choose>
	</xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="$str"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:function>

<xsl:function name="itasxsl:transform-date-view" as="xs:string">
<xsl:param name="d" as="xs:date"/>
  <xsl:value-of select="format-date($d,'[D01].[M01].[Y0001]','en',(),())"/><!-- Problem with time zone -->
</xsl:function>

</xsl:stylesheet>