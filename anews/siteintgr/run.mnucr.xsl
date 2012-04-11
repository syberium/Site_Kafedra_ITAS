<?xml version="1.0" encoding="koi8-r"?>
<xsl:stylesheet
 xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
 xmlns:xs="http://www.w3.org/2001/XMLSchema"
 xmlns:fn="http://www.w3.org/2005/xpath-functions"
 xmlns:itasxsl="http://www.itas.pstu.ru/XSL/Transform"
 version="2.0"
 exclude-result-prefixes="xs fn itasxsl">

<!-- Inclusions -->
<xsl:import href="nmnu.xsl"/>
<xsl:include href="../lib.xsl"/>

<!-- Parameters and variables -->

<xsl:param name="nMaxQntMnu3" select="1" as="xs:integer"/>

<xsl:param name="mnuTreeFile">ntreemnu.xml</xsl:param>
<xsl:variable name="mnuTree" select="document($mnuTreeFile)" as="document-node()"/>

<xsl:param name="outputFile">tmpdata.xml</xsl:param>

<xsl:param name="scr">http://itas.pstu.ru/anews.php</xsl:param>

<!-- Templates -->

<xsl:template match="document">
  <xsl:result-document href="{$outputFile}" format="xmlOut-win-1251">
    <xsl:copy>
      <xsl:apply-templates/>
    </xsl:copy>
  </xsl:result-document>
</xsl:template>

<xsl:template match="*">
  <xsl:copy>
    <xsl:choose>
      <xsl:when test="name()='anews'">
        <xsl:copy-of select="@*"/>
        <xsl:for-each select="$mnuTree/document/menu1[1]">
          <xsl:call-template name="newsTreeCr"/>
        </xsl:for-each>
      </xsl:when>
      <xsl:otherwise>
	    <xsl:copy-of select="@*"/>
        <xsl:apply-templates select="node()"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:copy>
</xsl:template>

<xsl:template match="text()">
  <xsl:value-of select="fn:normalize-space()"/>
</xsl:template>

<xsl:template name="newsTreeCr">
  <xsl:variable name="nTree"><xsl:call-template name="tmplMenu1"/></xsl:variable>
  <xsl:variable name="nTree"><xsl:copy-of select="itasxsl:nTreeMnuCorrect($nTree)"/></xsl:variable>
  <xsl:for-each select="$nTree/menu1">
    <title><xsl:value-of select="@name"/> - ИТАС</title>
    <xsl:call-template name="treeInfo"/>
    <menuid><xsl:value-of select="@menuid"/></menuid>
	<xsl:for-each select="menu2">
	  <xsl:element name="{@section}">
	    <title><xsl:value-of select="@name"/> - <xsl:value-of select="../@short-name"/> - ИТАС</title>
        <xsl:call-template name="treeInfo"/>
        <menuid><xsl:number/></menuid>
        <xsl:for-each select="menu3">
          <xsl:element name="{@section}">
		    <title><xsl:value-of select="@name"/> - <xsl:value-of select="../@short-name"/> - <xsl:value-of select="../../@short-name"/> - ИТАС</title>
            <xsl:call-template name="treeInfo"/>
            <menuid><xsl:number/></menuid>
          </xsl:element>
        </xsl:for-each>
      </xsl:element>
	</xsl:for-each>
  </xsl:for-each>
</xsl:template>

<xsl:template name="treeInfo">
  <link><xsl:value-of select="@short-name"/></link>
  <xsl:variable name="fname" select="fn:concat($scr,'?section=',@section)"/>
  <file><xsl:value-of select="$fname"/></file>
</xsl:template>

</xsl:stylesheet>
