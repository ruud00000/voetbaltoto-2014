<?php
session_start();
if (!isset($_SESSION['loggedin']) or $_SESSION['loggedin']!=1) { header('Location: logout.php'); exit; }
include("includes/login.inc.php");

if ($_POST['selectie'] and $_POST['selectie']=="Verwijder")	{
 	$deelnrId = $_POST['deelnemer'];

	include("includes/verbinding.inc.php");
 	$result = $_dnr->VerwijderDeelnemer($deelnrId);
	$verbreken = mysql_close($verbinding);	
 	switch ($result) {
 	case 1:
		$_SESSION['lerror'][] = "Verwijderen mislukt (starten sql transactie niet geslaagd, niets toegevoegd).";
 	case 2:
		$_SESSION['lerror'][] = "Verwijderen mislukt (verwijderen scores van deelnemer mislukt).";
 	case 3:
		$_SESSION['lerror'][] = "Verwijderen mislukt (verwijderen deelnemer mislukt).";
 	case 4:
		$_SESSION['lerror'][] = "Verwijderen mislukt (afronden sql transactie niet geslaagd, check niets verwijderd).";
 	case 5:
		$_SESSION['lerror'][] = "Verwijderen van deelnemer nummer ".$deelnrId." inclusief prognoses voltooid.";
		}
	
	header('Location: deelnemer_select.php');
	exit;
	}

if ($_POST['save'])	{
	include("includes/verbinding.inc.php");

	if ($_dnr->CheckVoornaam($_POST['voornaam']) == 3) {
		$_SESSION['lerror'][] = "Voornaam moet ingevuld worden.";
		}
	if ($_dnr->CheckVoornaam($_POST['voornaam']) == 2) {
		$_SESSION['lerror'][] = "Voornaam bevat ongeldige tekens (alleen letters toegestaan).";
		}
	if ($_dnr->CheckTv($_POST['tv']) == 2) {
		$_SESSION['lerror'][] = "Tussenvoegsel bevat ongeldige tekens (alleen letters toegestaan).";
		}
	if ($_dnr->CheckAchternaam($_POST['achternaam']) == 3) {
		$_SESSION['lerror'][] = "Achternaam moet ingevuld worden.";
		}
	if ($_dnr->CheckAchternaam($_POST['achternaam']) == 2) {
		$_SESSION['lerror'][] = "Achternaam bevat ongeldige tekens (alleen letters toegestaan).";
		}
	if ($_dnr->CheckEmailAdres($_POST['email']) == 3) {
		$_SESSION['lerror'][] = "E-mail adres moet ingevuld worden.";
		}
	if ($_dnr->CheckEmailAdres($_POST['email']) == 2) {
		$_SESSION['lerror'][] = "Vul een geldig e-mail adres in.";
		}
	if ($_dnr->CheckGebruikersnaam($_POST['gebruikersnaam']) == 4) {
		$_SESSION['lerror'][] = "Gebruikersnaam moet ingevuld worden.";
		}
	if ($_dnr->CheckGebruikersnaam($_POST['gebruikersnaam']) == 3) {
		$_SESSION['lerror'][] = "Gebruikersnaam bevat ongeldige tekens (alleen letters, cijfers, -, _ toegestaan).";
		}
	if ($_SESSION['gebruikersnaam_orig'] != $_POST['gebruikersnaam']) {
		if ($_dnr->CheckGebruikersnaam($_POST['gebruikersnaam']) == 2) {
			$_SESSION['lerror'][] = "Gebruikersnaam bestaat al.";
			}
		}

	$_SESSION['action'] = 1;
	if ($_SESSION['lerror']) {
		$verbreken = mysql_close($verbinding);	
		header('Location: deelnemer.php');
		exit;
		}
	else {
	 	$result = $_dnr->WijzigDeelnemer($_POST['deelnemer']);
		$verbreken = mysql_close($verbinding);	
	 	switch ($result) {
	 	case 1:
			$_SESSION['lerror'][] = "Wijzigen mislukt";
			}

		header('Location: deelnemer.php');
		exit;
	 	
		}
	}
	
if (($_POST['selectie'] and $_POST['selectie']=="Wijzig") or $_SESSION['action'])	{ 
 	// als teruggekeerd wordt vanuit het action script, zie codeblok 'if ($_POST['save'])' hierboven, is $_SESSION['action'] == 1

	include("includes/page_layout.inc.php");
	
	$message_lines = "";
	$error_validate = false;
	if(!empty($_SESSION['lerror'])){
	   if(is_array($_SESSION['lerror'])){
	        foreach($_SESSION['lerror'] as $value){
	            $message_lines .= "<p style=\"color:red\">".$value."</p>\n";
	        	}
	   		}
	   	$_SESSION['lerror'] = NULL;
	   	$error_validate = true;
		}
	else {
		if ($_SESSION['action']) $message_lines .= "<p>Wijzigingen zijn opgeslagen</p>\n";	
		}

	if (!isset($_SESSION['gebruikersnaam'])) { // gegevens van deelnemer ophalen uit database en in sessievariabelen zetten
		include("includes/verbinding.inc.php");
		$deelnrId = $_POST['deelnemer'];
		$result = $_dnr->GetDeelnemer($deelnrId); 
		$_SESSION['gebruikersnaam_orig'] = $_SESSION['gebruikersnaam'];
		$verbreken = mysql_close($verbinding);	
		} 
	$voornaam = $_SESSION['voornaam'];
	$tv = $_SESSION['tv'];
	$achternaam = $_SESSION['achternaam'];
	$email = $_SESSION['email'];
	$gebruikersnaam = $_SESSION['gebruikersnaam'];
	

$html_page = <<<HTMLPAGE
		<!-- breedte in afwijking van css -->
		<div id="paginainhoudbox" style="width: 745px"> 
			<h1>Deelnemer wijzigen</h1>
			
			{$message_lines}
			<form style="width: 525px; " method="post" action="#">
				<div style="width: 505px; padding-top: 15px;">
					<div class="schermregel">
				        <label class="label" for="voornaam">Voornaam</label>
				        <input type="text" style="width: 360px;" name="voornaam" id="voornaam" value="{$voornaam}" />
					</div>	
					<div class="schermregel">
				        <label class="label" for="tv">Tussenvoegsel</label>
				        <input type="text" style="width: 360px;" name="tv" id="tv" value="{$tv}" />
					</div>	
					<div class="schermregel">
				        <label class="label" for="achternaam">Achternaam</label>
				        <input type="text" style="width: 360px;" name="achternaam" id="achternaam" value="{$achternaam}" />
					</div>	
					<div class="schermregel">
				        <label class="label" for="email">E-mail</label>
				        <input type="text" style="width: 360px;" name="email" id="email" value="{$email}" />
					</div>	
					<div class="schermregel">
				        <label class="label" for="gebruikersnaam">Gebruikersnaam</label>
				        <input type="text" style="width: 360px;" name="gebruikersnaam" id="gebruikersnaam" value="{$gebruikersnaam}"/>
					</div>	
					<center>	
					<div style="padding-top: 15px;">
					    <input style="margin-left: 15px; width: 80px" type="submit" value="Opslaan" name="save"/>
				    </div>
				    </center>
				</div>	
			</form>
HTMLPAGE;

	unset($_SESSION['voornaam']);
	unset($_SESSION['tv']);
	unset($_SESSION['achternaam']);
	unset($_SESSION['email']);
	unset($_SESSION['gebruikersnaam']);

	if ($_SESSION['action']) {
		unset($_SESSION['action']);
		if (!$error_validate) {
			// Sessie beëindigen en terug naar selectiepagina
			$host  = $_SERVER['HTTP_HOST'];
			$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
			$extra = 'deelnemer_select.php';
			header("Refresh: 5; url=http://$host$uri/$extra");	
			}
		}

	echo $html_header.$html_page.$html_footer;	
	
	}
	
?>
