<xsl:stylesheet
  version = '1.0'
  xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>
   
  <xsl:param name="l1" select="0"/>
  <xsl:param name="l2" select="0"/>
  <xsl:param name="l3" select="0"/>
  <xsl:param name="l4" select="0"/>
  <xsl:param name="l5" select="0"/>
  
  <xsl:output method="html" encoding="koi8-r" />

  <xsl:template match="/">
  <html><body>
    <h1>Публикации</h1>
    <p>Список трудов, опубликованных сотрудниками кафедры</p>
    <table cellspacing="0" cellpadding="3" width="100%" style="border: 2px solid black" frame="box" rules="all">
      <thead>
        <tr>
          <th bgcolor="white" width="20%"><xsl:value-of select="//fields/author/title"   /></th>
          <th bgcolor="white" width="30%"><xsl:value-of select="//fields/title/title"    /></th>
          <th bgcolor="white" width="30%"><xsl:value-of select="//fields/published/title"/></th>
          <th bgcolor="white" width="10%" style="font-size:10pt"><xsl:value-of select="//fields/place/title"    /></th>
          <th bgcolor="white" width="10%" style="font-size:10pt"><xsl:value-of select="//fields/pages/title"    /></th>
        </tr>
      </thead>
      <tbody>
        <xsl:variable name="i" select="1" />
        <xsl:for-each select="//item">
          <xsl:sort select="author" />
          <xsl:if test="$l1 = '_' or (starts-with(author,$l1) or starts-with(author,$l2) or starts-with(author,$l3) or starts-with(author,$l4) or starts-with(author,$l5))">
            <tr style="font-size:10pt">
              <td bgcolor="white">
                <xsl:for-each select="author">
                  <xsl:value-of select="text()" /><br />
                </xsl:for-each>
              </td>
              <td bgcolor="white"><xsl:value-of select="title" /></td>
              <td bgcolor="white"><xsl:value-of select="published" /></td>
              <td bgcolor="white" style="text-align:center"><xsl:value-of select="place" /></td>
              <td bgcolor="white" style="text-align:center"><xsl:value-of select="pages" /></td>
            </tr>
          </xsl:if>
        </xsl:for-each>
      </tbody>
    </table>
  </body></html>
  </xsl:template>
  
</xsl:stylesheet>
