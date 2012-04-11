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

<!-- Parameters and variables -->

<xsl:param name="outputFile">tmpndbcln.xml</xsl:param>
<xsl:variable name="output" select="fn:concat('service/',$outputFile)" as="xs:string"/>

<xsl:param name="newsMaxSrcQnt" select="0" as="xs:integer"/>

<!-- Templates and functions -->

<xsl:template match="document">
  <xsl:result-document href="{$output}" format="xmlOut-koi8-r">
    <xsl:copy>
      <xsl:for-each-group select="news" group-by="srcNum">
        <xsl:for-each select="current-group()">
          <xsl:if test="position() &lt;= $newsMaxSrcQnt">
            <xsl:copy-of select="."/>
          </xsl:if>
        </xsl:for-each>
      </xsl:for-each-group>
    </xsl:copy>
  </xsl:result-document>
</xsl:template>

</xsl:stylesheet>
