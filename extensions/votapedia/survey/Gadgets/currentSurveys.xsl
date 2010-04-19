<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="uri:xsl">
<xsl:template xmlns:xsl="uri:xsl">
<HTML>
<head>

</head>
<BODY>
	<xsl:for-each select="surveys/survey">
		<DIV>
			<table width="250" border="0">
			  <tr><td colspan="3" bgcolor="#999999" >
			  	<span style="font-family: Arial, Helvetica, sans-serif; font-size:12px; color: #FFFF00">
					<xsl:value-of select="question"/><br></br>
					<xsl:value-of select="votesAllowed"/>
				</span>	
			  </td></tr>
			  <tr><td><span style="font-family: Arial, Helvetica, sans-serif; font-size:10px">Choices</span>
			  </td><td><span style="font-family: Arial, Helvetica, sans-serif; font-size:10px">Dial</span>
			  </td><td><span style="font-family: Arial, Helvetica, sans-serif; font-size:10px">Votes</span>
			  </td>
			  </tr>
				  <xsl:for-each select="choices">
			  	    	<xsl:apply-templates/>
			  </xsl:for-each>
			</table>
		<hr size = "1"/>	
		</DIV>
	</xsl:for-each>
<a href="http://www.votapedia.com/index.php?title=Special:CurrentSurveys">More..</a>
</BODY>
</HTML>
</xsl:template>
	
	<xsl:template match="choice">
	<tr>
		<TD width = "155"><span style="font-family: Arial, Helvetica, sans-serif; font-size:10px"><xsl:value-of select="value"/></span></TD>
		<TD width = "75"><span style="font-family: Arial, Helvetica, sans-serif; font-size:10px"><xsl:value-of select="receiver"/></span></TD>
		<TD><span style="font-family: Arial, Helvetica, sans-serif; font-size:10px"><xsl:value-of select="vote"/></span></TD>
	</tr>	
	</xsl:template>
	
</xsl:stylesheet>	