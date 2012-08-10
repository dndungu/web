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
				<script src="/js/libraries/jquery-1.7.2.min.js" type="text/javascript"></script>
				<script src="/js/base/core.js" type="text/javascript"></script>
				<script src="/l18n" type="text/javascript"></script>
				<script src="/js/base/core.sandbox.js" type="text/javascript"></script>
				<script src="/js/base/core.ajax.js" type="text/javascript"></script>
				<script src="/js/base/core.navigation.js" type="text/javascript"></script>
				<script src="/js/base/core.validator.js" type="text/javascript"></script>
				<script src="/js/base/core.control.js" type="text/javascript"></script>				
				<script src="/js/base/core.control.grid.js" type="text/javascript"></script>
				<script src="/js/base/core.control.form.js" type="text/javascript"></script>
				<script src="/js/apps/studio.js" type="text/javascript"></script>
			</head>
			<body>
				<div id="body-wrapper">
					<div id="sidebar">
						<div id="sidebar-wrapper" class="panelNavigation">
							<h1 class="big-logo"></h1>
							<div id="profile-links">
								<a href="/signout" style="font-size:1.25em;">Sign Out</a>
							</div>
							<ul id="main-nav">
								<xsl:for-each select="/response/core/Navigation/*/panel/root/*">
									<xsl:variable name="id" select="id" />
									<li>
										<a class="{class}"><xsl:value-of select="label"/></a>
										<ul>
											<xsl:for-each select="/response/core/Navigation/*/panel/*[name()=$id]/*">
												<li>
													<a href="{uri}">
														<xsl:value-of select="label"/>
													</a>
												</li>
											</xsl:for-each>											
										</ul>
									</li>
								</xsl:for-each>
							</ul>
						</div>
					</div>					
					<div id="main-content">
						<div class="">
						</div>
						<div class="pageContentContent">
							<xsl:value-of select="/response/content/atomstudio/*" disable-output-escaping="yes"/>
						</div>
						<div id="footer"></div>
					</div>
				</div>
				<script type="text/javascript">
					core.boot();
				</script>
			</body>
		</html>		
	</xsl:template>
</xsl:stylesheet>			