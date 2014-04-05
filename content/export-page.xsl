<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

    <xsl:template match="/">
        <h2><a href="{data/csv/link}">Download "<xsl:value-of select="data/csv/name" />" CSV file</a></h2>
        <h2><a href="{data/csv/home-link}">Back to import/export page</a></h2>
    </xsl:template>

</xsl:stylesheet>