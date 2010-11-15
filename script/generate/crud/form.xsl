<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="text"/>
<xsl:template match="/model">&lt;?php
	open_form("model:<xsl:value-of select="@name"/>  action:$action  url:$submit_to", 'id="<xsl:value-of select="@name"/>_form"');
?&gt;
<xsl:apply-templates select="/model/field"/>	&lt;div class="field"&gt;&lt;?php submit("class:big round button  value:Save"); ?&gt;&lt;/div&gt;
&lt;/form&gt;</xsl:template>

<xsl:template match="field">	&lt;div class="field"&gt;&lt;?php <xsl:value-of select="@type"/>("<xsl:value-of select="@name"/><xsl:apply-templates select="@*"/>"); ?&gt;&lt;/div&gt;
</xsl:template>

<xsl:template match="@*"><xsl:if test="(name() != 'name') and (name() != 'type') and (name() != 'id') and (name() != 'label')"><xsl:text>  </xsl:text><xsl:value-of select="name()"/>:<xsl:value-of select="."/></xsl:if></xsl:template>

</xsl:stylesheet>