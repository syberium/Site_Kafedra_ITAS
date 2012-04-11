<?xml version="1.0" encoding="koi8-r"?>
<xsl:stylesheet
 xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
 xmlns:xs="http://www.w3.org/2001/XMLSchema"
 xmlns:fn="http://www.w3.org/2005/xpath-functions"
 xmlns:itasxsl="http://www.itas.pstu.ru/XSL/Transform"
 version="2.0"
 exclude-result-prefixes="xs fn itasxsl">

<!-- Inclusions -->
<xsl:import href="../lib.xsl"/>

<!-- Parameters and variables -->
<xsl:param name="urlMaxLen" select="60" as="xs:integer"/>

<!-- Templates and functions -->

<xsl:template name="tmplNews">
    <p style="margin-bottom:0; padding-bottom:0;">
      <b><xsl:number/>. <xsl:value-of select="title"/></b><xsl:text> </xsl:text>
      [<xsl:call-template name="dateDefinition"/>]<br/>
  </p>
  <p style="margin-top:0; padding-top:0; padding-left:14pt;">
      <xsl:if test="fn:string-length(description) &gt; 0">
        <xsl:value-of select="itasxsl:string-specTrim(description,$descrMaxLenIntegr)"/><xsl:text> </xsl:text>
      </xsl:if>
      <xsl:if test="link">
     <xsl:variable name="baseNewsUrl" select="itasxsl:getBaseUrl(link)" as="xs:string"/>
        <xsl:variable name="prblBaseNewsUrl" select="itasxsl:printableURL($baseNewsUrl)" as="xs:string"/>
        <xsl:variable name="prblBaseNewsUrl" select="itasxsl:string-specTrim($prblBaseNewsUrl,$urlMaxLen)" as="xs:string"/>
     <xsl:element name="a">
          <xsl:attribute name="href" select="link"/>
    <xsl:attribute name="target" select="_blank"/>Подробнее&gt;&gt;&gt;
        </xsl:element><br/>
        <xsl:text>Ресурс: </xsl:text>
        <xsl:element name="a">
          <xsl:attribute name="href" select="$baseNewsUrl"/>
          <xsl:value-of select="$prblBaseNewsUrl"/>
        </xsl:element>
   </xsl:if>
    </p>
</xsl:template>

<xsl:template name="tmplNewsLight">
  <a style="color:#000;text-decoration:none;"><xsl:attribute name="href" select="link"/>
    <xsl:number/>. <xsl:value-of select="title"/>
  </a>
  <xsl:text> </xsl:text>[<xsl:call-template name="dateDefinition"/>]<br/>
</xsl:template>

<xsl:template name="tmplAnnounc">
    <p style="margin-bottom:0; padding-bottom:0;">
      <b><xsl:number/>. <xsl:value-of select="title"/></b><br/>
  </p>
  <p style="margin-top:0; padding-top:0; padding-left:14pt;">
      <xsl:if test="fn:string-length(description) &gt; 0">
        <xsl:value-of select="itasxsl:string-specTrim(description,$descrMaxLenIntegr)"/><xsl:text> </xsl:text>
      </xsl:if>
      <xsl:if test="link">
     <xsl:variable name="baseNewsUrl" select="itasxsl:getBaseUrl(link)" as="xs:string"/>
        <xsl:variable name="prblBaseNewsUrl" select="itasxsl:printableURL($baseNewsUrl)" as="xs:string"/>
        <xsl:variable name="prblBaseNewsUrl" select="itasxsl:string-specTrim($prblBaseNewsUrl,$urlMaxLen)" as="xs:string"/>
     <xsl:element name="a">
          <xsl:attribute name="href" select="link"/>Подробнее&gt;&gt;&gt;
        </xsl:element><br/>
        <xsl:text>Ресурс: </xsl:text>
        <xsl:element name="a">
          <xsl:attribute name="href" select="$baseNewsUrl"/>
          <xsl:value-of select="$prblBaseNewsUrl"/>
        </xsl:element>
   </xsl:if>
    </p>
</xsl:template>

    <xsl:template name="dateDefinition">
      <xsl:choose>
        <xsl:when test="fn:string-length(date) &gt; 0 and date!=$emptyDate">
          <xsl:value-of select="itasxsl:transform-date-view(date)"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="itasxsl:transform-date-view(procDate)"/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:template>

<xsl:template name="lixto-logo-insert">
  <p style="padding-top:16pt;">
  Инфомация извлечена с использованием <a href="http://www.lixto.com/"><img src="photos/logo/logolixtoklein_160.gif" align="absmiddle" border="0" alt="LiXto Visual Wrapper" title="LiXto Visual Wrapper"/></a>
  </p>
</xsl:template>

<xsl:template name="service-message">
  <!--<p style="marging:0; padding:0; text-align:right; font-size:12pt; color:silver;">
  Новости остановлены 13.04.07.<br/>Проблема с предоставлением доступа в Интернет.
  </p>-->
</xsl:template>
<!--А вы в курсе последних событий<br/>в мире ИТ-индустрии?-->
<!--Ежедневно системой проссматривается<br/>49 отобранных источников информации-->
<!--Благодаря применению ПП Lixto подсистема Новости устойчива<br/>к небольшим изменениям структуры источников информации-->

<xsl:template name="source-quantity-message">
  <p>
    Количество источников: <b>60</b>
  </p>
</xsl:template>

</xsl:stylesheet>
