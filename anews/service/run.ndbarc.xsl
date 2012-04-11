<?xml version="1.0" encoding="koi8-r"?>
<xsl:stylesheet
 xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
 xmlns:xs="http://www.w3.org/2001/XMLSchema"
 xmlns:fn="http://www.w3.org/2005/xpath-functions"
 version="2.0"
 exclude-result-prefixes="xs fn">

<!-- Inclusions -->
<xsl:include href="../lib.xsl"/>

<!-- Parameters and variables -->

<xsl:param name="newsDBFile">../ndb.xml</xsl:param>
<xsl:variable name="newsDB" select="document($newsDBFile)" as="document-node()"/>

<xsl:param name="outputFile">tmpndbarc.xml</xsl:param>
<xsl:variable name="output" select="fn:concat('service/',$outputFile)" as="xs:string"/>

<!-- Templates and functions -->

<xsl:template match="document">
  <xsl:result-document href="{$output}" format="xmlOut-koi8-r">
    <xsl:copy>
      <xsl:copy-of select="$newsDB/document/news"/>
      <xsl:for-each select="news">
        <xsl:variable name="ttl">
          <xsl:value-of select="title"/>
        </xsl:variable>
        <xsl:if test="not($newsDB/document/news[title=$ttl])">
          <xsl:copy-of select="."/>
        </xsl:if>
      </xsl:for-each>
    </xsl:copy>
  </xsl:result-document>
</xsl:template>

</xsl:stylesheet>
