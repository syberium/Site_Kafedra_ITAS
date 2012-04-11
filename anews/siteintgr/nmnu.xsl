<?xml version="1.0" encoding="koi8-r"?>
<xsl:stylesheet
 xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
 xmlns:xs="http://www.w3.org/2001/XMLSchema"
 xmlns:itasxsl="http://www.itas.pstu.ru/XSL/Transform"
 version="2.0"
 exclude-result-prefixes="xs itasxsl">

<!-- Inclusions -->
<xsl:import href="../ntreelib.xsl"/>

<!-- Parameters and variables -->
<!-- Parameters isn't used -->
<xsl:param name="nMaxQntMnu3" select="20" as="xs:integer"/>

<!-- Templates -->

<xsl:template name="tmplMenu1">
  <xsl:copy>
    <xsl:copy-of select="@section|@name|@short-name|@menuid"/>
	<xsl:for-each select="menu2">
	  <xsl:call-template name="tmplMenu2"/>
	</xsl:for-each>
  </xsl:copy>
</xsl:template>

<xsl:template name="tmplMenu2">
  <xsl:copy>
    <xsl:call-template name="attrs-create"/>
	<xsl:choose>
	  <xsl:when test="menu3">
	    <xsl:for-each select="menu3">
		  <xsl:copy>
		    <xsl:call-template name="attrs-create"/>
		    <xsl:copy-of select="itasxsl:get-leaf-news(@section,$nMaxQntMnu3)"/>
		  </xsl:copy>
		</xsl:for-each>
	  </xsl:when>
	  <xsl:otherwise>
	    <xsl:copy-of select="itasxsl:get-leaf-news(@section,$nMaxQntMnu3)"/>
	  </xsl:otherwise>
	</xsl:choose>
  </xsl:copy>
</xsl:template>

<xsl:template name="attrs-create">
  <xsl:copy-of select="@*"/>
  <xsl:variable name="attrs"><xsl:copy-of select="itasxsl:get-attrs-by-elname(@section)"/></xsl:variable>
  <xsl:if test="not(@name)">
    <xsl:attribute name="name" select="$attrs/name"/>
  </xsl:if>
  <xsl:if test="not(@short-name)">
    <xsl:attribute name="short-name" select="$attrs/short-name"/>
  </xsl:if>
</xsl:template>

<xsl:function name="itasxsl:nTreeMnuCorrect">
<xsl:param name="nTree"/>
    <xsl:for-each select="$nTree/menu1[position()=1 and count(//news)&gt;0]">
	<xsl:copy>
	  <xsl:copy-of select="@*"/>
	  <xsl:for-each select="menu2[count(//news)&gt;0]">
	    <xsl:choose>
		<xsl:when test="menu3">
	      <xsl:copy>
		    <xsl:copy-of select="@*"/>
			<xsl:for-each select="menu3[count(news)&gt;0]">
			  <xsl:copy-of select="."/>
			</xsl:for-each>
		  </xsl:copy>
		</xsl:when>
		<xsl:otherwise>
		  <xsl:copy-of select="."/>
		</xsl:otherwise>
		</xsl:choose>
	  </xsl:for-each>
	</xsl:copy>
	</xsl:for-each>
</xsl:function>

</xsl:stylesheet>