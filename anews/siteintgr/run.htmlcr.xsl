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
<xsl:import href="../ntreelib.xsl"/>
<xsl:include href="../lib.xsl"/>
<xsl:include href="nhtml.xsl"/>

<!-- Parameters and variables -->

<xsl:param name="nMaxQntMnu1" select="1" as="xs:integer"/>
<xsl:param name="nMaxQntMnu2" select="3" as="xs:integer"/>
<xsl:param name="nMaxQntMnu3" select="30" as="xs:integer"/>

<xsl:param name="outputFolder">html</xsl:param>

<xsl:param name="nMaxQntAnnounc" select="7" as="xs:integer"/>

<!-- Templates -->

<xsl:template match="document">
  <xsl:variable name="nTreeMnu"><xsl:apply-templates select="menu1"/></xsl:variable>
  <xsl:variable name="nTreeMnu">
    <xsl:copy-of select="itasxsl:nTreeMnuCorrect($nTreeMnu)"/>
  </xsl:variable>
<!-- Creating html files for menu3 -->
  <xsl:for-each select="$nTreeMnu/menu1//(menu2|menu3)[count(news)&gt;0]">
    <xsl:result-document href="{$outputFolder}/{@section}.html" format="htmlOut-koi8-r">
   <xsl:call-template name="service-message"/>
   <h1><xsl:value-of select="@name"/></h1>
   <xsl:for-each select="news">
     <xsl:call-template name="tmplNews"/>
   </xsl:for-each>
   <br/><xsl:call-template name="lixto-logo-insert"/>
   <xsl:call-template name="source-quantity-message"/>
 </xsl:result-document>
  </xsl:for-each>
<!-- Creating html files for menu2 -->
  <xsl:for-each select="$nTreeMnu/menu1/menu2[count(menu3)&gt;0]">
    <xsl:result-document href="{$outputFolder}/{@section}.html" format="htmlOut-koi8-r">
   <xsl:call-template name="service-message"/>
   <h1><xsl:value-of select="@name"/></h1>
   <xsl:for-each select="menu3">
     <h2><xsl:value-of select="@name"/></h2>
    <xsl:for-each select="news[position()&lt;=$nMaxQntMnu2]">
         <xsl:call-template name="tmplNews"/>
    </xsl:for-each>
<hr/>
   </xsl:for-each>
   <br/><xsl:call-template name="lixto-logo-insert"/>
   <xsl:call-template name="source-quantity-message"/>
 </xsl:result-document>
  </xsl:for-each>
<!-- Creating html file for menu1 -->
  <xsl:variable name="announc"><xsl:copy-of select="itasxsl:get-leaf-news-tree('announc',$nMaxQntAnnounc)"/></xsl:variable>
 <xsl:if test="$nTreeMnu//news[1] or $announc/announc/news[1]">
  <xsl:result-document href="{$outputFolder}/{$nTreeMnu/menu1/@section}.html" format="htmlOut-koi8-r">
   <xsl:call-template name="service-message"/>
   <h1><xsl:value-of select="$nTreeMnu/menu1/@name"/></h1>
   <xsl:if test="$announc/announc/news[1]">
    <h2><xsl:value-of select="$announc/announc/@name"/></h2>
 <xsl:for-each select="$announc/announc/news"><xsl:call-template name="tmplAnnounc"/></xsl:for-each>
 <hr/>
   </xsl:if>
   <xsl:for-each select="$nTreeMnu/menu1/menu2">
     <h2><xsl:value-of select="@name"/></h2>
  <xsl:variable name="ns">
    <xsl:choose>
    <xsl:when test="menu3">
        <xsl:for-each select="menu3/news[position()&lt;=$nMaxQntMnu1]">
			<xsl:sort select="xs:date(date)" order="descending"/>
            <xsl:sort select="xs:date(procDate)" order="descending"/>
            <xsl:copy-of select="."/>
        </xsl:for-each>
    </xsl:when>
    <xsl:otherwise>
      <xsl:copy-of select="news[position()&lt;=$nMaxQntMnu1]"/>
    </xsl:otherwise>
    </xsl:choose>
  </xsl:variable>
  <xsl:for-each select="$ns/news">
    <xsl:call-template name="tmplNewsLight"/>
  </xsl:for-each>
   </xsl:for-each>
   <xsl:call-template name="lixto-logo-insert"/>
   <xsl:call-template name="source-quantity-message"/>
  </xsl:result-document>
 </xsl:if>
</xsl:template>

<xsl:template match="menu1">
  <xsl:call-template name="tmplMenu1"/>
</xsl:template>

</xsl:stylesheet>
