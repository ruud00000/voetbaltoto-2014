<?php
session_start();
if (!isset($_SESSION['loggedin']) or $_SESSION['loggedin']!=1 or $_SESSION['l_level']<2) { header('Location: logout.php'); exit; }

include("includes/page_layout.inc.php");
include("includes/verbinding.inc.php");

// Inlezen van de variabelen uit score.php, m.b.v. addslashes() om misbruik (SQL-injection) te voorkomen
if ($_GET["wedstrijd"]) {
	$WedstrijdId = addslashes($_GET["wedstrijd"]);
	$Uitslag1 = addslashes($_GET["uitsl1"]);
	$Uitslag2 = addslashes($_GET["uitsl2"]);
	}
else {
	$WedstrijdId = addslashes($_POST["wedstrijd"]);
	$Uitslag1 = addslashes($_POST["uitsl1"]);
	$Uitslag2 = addslashes($_POST["uitsl2"]);
	}

$message_lines = "";

$html_page = <<<HTMLPAGE
		<!-- breedte in afwijking van css -->
		<div id="paginainhoudbox" style="width: 745px"> 
			<h1>Uitslag toevoegen of wijzigen</h1>
			
			{$message_lines}
			<form style="width: 525px; " method="post" action="doemaar.php">
				<div style="width: 505px; padding-top: 15px;">
					<div class="schermregel">
				        <label class="label" for="wedstrijd">Wedstrijd</label>
				        <input type="text" style="width: 360px;" name="wedstrijd" id="wedstrijd" value="{$WedstrijdId}"/>
					</div>	
					<div class="schermregel">
				        <label class="label" for="nieuweuitsl1">Nieuwe uitslag</label>
				        <input type="text" style="width: 50px;" name="nieuweuitsl1" id="nieuweuitsl1" value="{$Uitslag1}" />
				        <label for="nieuweuitsl2"> - </label>
				        <input type="text" style="width: 50px;" name="nieuweuitsl2" id="nieuweuitsl2" value="{$Uitslag2}" />
					</div>
					<center>	
					<div style="padding-top: 15px;">
					    <input style="margin-left: 15px; width: 80px" type="submit" value="Opslaan" name="opslaan"/>
				    </div>
				    </center>
				</div>	
			</form>
HTMLPAGE;

echo $html_header.$html_page.$html_footer;