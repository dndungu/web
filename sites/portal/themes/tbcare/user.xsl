<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:output method="xml" indent="yes" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" />
	<xsl:template match="/">
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
			<head>
				<title><xsl:value-of select="/response/core/SiteSetting/*/title" disable-output-escaping="yes"/> - <xsl:value-of select="/response/user/*/*/title"/></title>
				<link rel="stylesheet" href="/skins/tbcare/css/reset.css" type="text/css" media="screen" />
				<link rel="stylesheet" href="/skins/tbcare/css/style.css" type="text/css" media="screen" />
				<link rel="stylesheet" href="/skins/tbcare/css/invalid.css" type="text/css" media="screen" />
				<!--[if lte IE 7]>
				<link rel="stylesheet" href="/skins/tbcare/css/ie.css" type="text/css" media="screen" />
				<![endif]-->
			</head>
			<body id="login">
				<div id="login-wrapper" class="png_bg">
					<div id="login-top">
						<h1><xsl:value-of select="/response/core/*/*/title"/></h1>
					</div>
					<div id="login-content">
						<xsl:for-each select="/response/core/*/*/message">
							<div class="notification information png_bg">
								<div><xsl:value-of select="node-0"/></div>
							</div>
						</xsl:for-each>
						<xsl:for-each select="/response/core/*/*/error">
							<div class="notification error png_bg">
								<div><xsl:value-of select="node-0"/></div>
							</div>							
						</xsl:for-each>
						<xsl:value-of select="/response/core/*/*/content" disable-output-escaping="yes"/>
						<ul class="authentication">
							<xsl:for-each select="/response/core/Navigation/*/authentication/*/*">
								<xsl:if test="uri != /response/core/SiteSetting/*/uri">
								<li><a href="{uri}"><xsl:value-of select="label"/></a></li>
								</xsl:if>
							</xsl:for-each>
						</ul>						
					</div>
				</div>
			</body>
		</html>		
	</xsl:template>
</xsl:stylesheet>