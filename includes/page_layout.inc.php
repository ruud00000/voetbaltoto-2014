<?php
include "includes/config.inc.php";

$prognose = ($_SESSION['l_level']==2) ? "prognose_select.php" : "prognose.php";

$menu_lines = "";
$menu_lines .= "<li><a href=\"reglement.php\">Reglement</a></li>";
if ($_SESSION['loggedin'] == 1) $menu_lines .= "<li><a href=\"".$prognose."\">Voorspellen</a></li>";
$menu_lines .= "<li><a href=\"uitslagen.php\">Stand</a></li>";
$menu_lines .= "<li><a href=\"wedstrijd.php\">Statistieken</a></li>";
$menu_lines .= "<li><a href=\"prijzen.php\">Prijzengeld</a></li>";
if ($_SESSION['l_level']==2) $menu_lines .= "<li><a href=\"score.php\">Uitslag toevoegen</a></li>";
if ($_SESSION['l_level']==2) $menu_lines .= "<li><a href=\"deelnemer_select.php\">Deelnemer</a></li>";
if ($_SESSION['loggedin'] == 1) $menu_lines .= "<li><a href=\"suggesties.php\">Suggesties</a></li>";
if ($_SESSION['l_level']==2) $menu_lines .= "<li><a href=\"deelnemer_lijst.php\">Status</a></li>";
if ($_SESSION['l_level']==2) $menu_lines .= "<li><a href=\"instellingen.php\">Instellingen</a></li>";
$menu_lines .= "<br />";
if (empty($_SESSION['loggedin']) or $_SESSION['loggedin'] == 0) $menu_lines .= "<li><a href=\"logout.php\">Inloggen</a></li>";
if ($_SESSION['loggedin'] == 1) $menu_lines .= "<li><a href=\"logout.php\">Uitloggen</a></li>";

$footer_menu = "";
$footer_menu .= "&nbsp;&nbsp;<a href=\"reglement.php\">Reglement</a>";
if ($_SESSION['loggedin'] == 1) $footer_menu .= "&nbsp;&nbsp;<a href=\"".$prognose."\">Voorspellen</a>";
$footer_menu .= "&nbsp;&nbsp;<a href=\"uitslagen.php\">Stand</a>";
$footer_menu .= "&nbsp;&nbsp;<a href=\"wedstrijd.php\">Statistieken</a>";
$footer_menu .= "&nbsp;&nbsp;<a href=\"prijzen.php\">Prijzengeld</a>";
if ($_SESSION['l_level']==2) $footer_menu .= "&nbsp;&nbsp;<a href=\"score.php\">Uitslag toevoegen</a>";
if ($_SESSION['l_level']==2) $footer_menu .= "&nbsp;&nbsp;<a href=\"deelnemer_select.php\">Deelnemer</a>";
if ($_SESSION['loggedin'] == 1) $footer_menu .= "&nbsp;&nbsp;<a href=\"suggesties.php\">Suggesties</a>";
if ($_SESSION['l_level']==2) $footer_menu .= "&nbsp;&nbsp;<a href=\"deelnemer_lijst.php\">Status</a>";
if ($_SESSION['l_level']==2) $footer_menu .= "&nbsp;&nbsp;<a href=\"instellingen.php\">Instellingen</a>";
if (empty($_SESSION['loggedin']) or $_SESSION['loggedin'] == 0) $footer_menu .= "&nbsp;&nbsp;<a href=\"logout.php\">Inloggen</a>";
if ($_SESSION['loggedin'] == 1) $footer_menu .= "&nbsp;&nbsp;<a href=\"logout.php\">Uitloggen</a>";

$paginakop = ($showpaginakop) ? '	
	<div class="paginakopbox">
		<div class="paginakopcenterbox">
			<img style="float: left;" src="'.$logo.'" alt="paginakop" />
				<p class="paginakoptekst">'.$title_short.' '.$year.'</p>
		</div>
	</div>
' : "";
$navigatiekolom = ($shownavigatiekolom) ? '
		<div id="navigatiekolom">
			<ol>
				'.$menu_lines.'
			</ol> 
		</div>' : "";
$header = ($showtopmenu) ? '
	<div id="footer" class="footerbox"> 
		<div class="footercenterbox">
			'.$footer_menu.'
		</div>
	</div>' : "";
$footer = ($showfootermenu) ? '
	<div id="footer" class="footerbox"> 
		<div class="footercenterbox">
			'.$footer_menu.'
		</div>
	</div>' : "";

$html_header = <<<HTMLHEADER
<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=ISO-8859-1" />
	<title>$title</title>
	<link rel="shortcut icon" href="images/ek2012voetbal.ico" />
	<link rel="stylesheet" href="opmaak.css" type="text/css"/>		
</head>
<body>
	<style type="text/css">
	</style>
	{$paginakop}
	<div id="dummypaginacenterbox">
		{$header}
		{$navigatiekolom}
		
HTMLHEADER;

$html_footer = <<<HTMLFOOTER
		</div> 
	</div>
	 
	{$footer}
</body>
</html>
HTMLFOOTER;
?>