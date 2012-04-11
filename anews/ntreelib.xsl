<?xml version="1.0" encoding="koi8-r"?>
<xsl:stylesheet
 xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
 xmlns:xs="http://www.w3.org/2001/XMLSchema"
 xmlns:fn="http://www.w3.org/2005/xpath-functions"
 xmlns:itasxsl="http://www.itas.pstu.ru/XSL/Transform"
 version="2.0"
 exclude-result-prefixes="xs fn itasxsl">

<!-- Parameters and variables -->

<xsl:param name="nTreeFile">ntree.xml</xsl:param>
<xsl:variable name="nTree" select="document($nTreeFile)" as="document-node()"/>

<xsl:param name="nbdFile">ndb.xml</xsl:param>
<xsl:variable name="ndb" select="document($nbdFile)" as="document-node()"/>

<!-- Functions -->

<xsl:function name="itasxsl:get-attrs-by-elname" as="node()*">
<xsl:param name="elname" as="xs:string"/>
  <xsl:for-each select="$nTree//*[name()=$elname][1]/@*">
    <xsl:element name="{name()}">
      <xsl:value-of select="."/>
    </xsl:element>
  </xsl:for-each>
</xsl:function>

<xsl:function name="itasxsl:get-leaves">
<xsl:param name="elname" as="xs:string"/>
  <xsl:variable name="el">
    <xsl:copy-of select="$nTree//*[name()=$elname][1]"/>
  </xsl:variable>
  <xsl:copy-of select="$el//*[count(*)=0]"/><!-- It is a leaf -->
</xsl:function>

<xsl:function name="itasxsl:get-exist-leaves">
<xsl:param name="elname" as="xs:string"/>
  <xsl:variable name="ex" select="itasxsl:get-leaves($elname)"/>
  <xsl:for-each select="$ex">
    <xsl:variable name="sctnName" select="name()"/>
 <xsl:if test="$ndb/document/news[section=$sctnName][1]">
   <xsl:copy-of select="."/>
 </xsl:if>
  </xsl:for-each>
</xsl:function>

<xsl:function name="itasxsl:get-leaf-news">
<xsl:param name="elname" as="xs:string"/>
<xsl:param name="newsMaxQnt" as="xs:integer"/>
 <xsl:variable name="ns">
  <xsl:variable name="sctns" select="itasxsl:get-exist-leaves($elname)"/>
  <xsl:variable name="sctnN" select="itasxsl:spec-div($newsMaxQnt,count($sctns))"/>
  <xsl:for-each select="$sctns">
    <xsl:variable name="sctnName" select="name()"/>
 <xsl:variable name="srcs">
   <xsl:for-each-group select="$ndb/document/news[section=$sctnName]" group-by="srcNum">
  <srcNum><xsl:value-of select="srcNum"/></srcNum>
   </xsl:for-each-group>
 </xsl:variable>
 <xsl:variable name="srcN" select="itasxsl:spec-div($sctnN,count($srcs/srcNum))"/>
 <xsl:for-each select="$srcs/srcNum">
   <xsl:variable name="srcName"><xsl:value-of select="."/></xsl:variable>
   <xsl:copy-of select="$ndb/document/news[section=$sctnName and srcNum=$srcName][position()&lt;=$srcN]"/>
 </xsl:for-each>
  </xsl:for-each>
 </xsl:variable>
  <xsl:for-each select="$ns/news">
  <xsl:sort select="xs:date(date)" order="descending"/>
  <xsl:sort select="xs:date(procDate)" order="descending"/>
    <xsl:copy-of select="."/>
  </xsl:for-each>
</xsl:function>

<xsl:function name="itasxsl:get-leaf-news-tree">
<xsl:param name="elname" as="xs:string"/>
<xsl:param name="newsMaxQnt" as="xs:integer"/>
  <xsl:variable name="attrs"><xsl:copy-of select="itasxsl:get-attrs-by-elname($elname)"/></xsl:variable>
  <xsl:element name="{$elname}">
    <xsl:attribute name="name" select="$attrs/name"/>
    <!--<xsl:attribute name="short-name" select="$attrs/short-name"/>-->
    <xsl:copy-of select="itasxsl:get-leaf-news($elname, $newsMaxQnt)"/>
  </xsl:element>
</xsl:function>

<xsl:function name="itasxsl:spec-div" as="xs:integer">
<xsl:param name="a" as="xs:double"/>
<xsl:param name="b" as="xs:double"/>
  <xsl:choose>
  <xsl:when test="$a&gt;0 and $a&lt;=$b">
    <xsl:value-of select="1"/>
  </xsl:when>
  <xsl:when test="$b&gt;0 and $b&lt;=$a">
    <xsl:value-of select="round($a div $b)"/>
  </xsl:when>
  <xsl:otherwise>
    <xsl:value-of select="0"/>
  </xsl:otherwise>
  </xsl:choose>
</xsl:function>

</xsl:stylesheet>
