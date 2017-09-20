<?php
session_start();
include_once "includes/page_layout.inc.php";
include "includes/config.inc.php";

$html_page = <<<HTMLPAGE
		<!-- smallere breedte in afwijking van css -->
		<div id="paginainhoudbox" style="width: 720px;"> 
			<h1>Prijzen</h1>
			<p style="font-weight: normal;"> 
			{$prijzen}
<br />
			</p>	
			
HTMLPAGE;

echo $html_header.$html_page.$html_footer;