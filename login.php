<?php
session_start();
include("includes/login.inc.php");
include("includes/verbinding.inc.php");

if ($_POST['login']) {
 	$result = $_dnr->LoginCheckGebruikersnaam(mysql_real_escape_string($_POST['gebruikersnaam']));
 	switch ($result) {
	case 1: 
		$_SESSION['lerror'][] = "Gebruiker is niet geregistreerd.";
		break;
	case 2:
		$_SESSION['lerror'][] = "Ophogen login status mislukt.";
		break;
	case 3:
		$_SESSION['lerror'][] = "Gebruikersnaam niet bekend.";
		break;
	case 4:
		$_SESSION['lerror'][] = "Gebruikersnaam is niet ingevuld.";
		break;
	case 6:
		$_SESSION['lerror'][] = "Geen activatiecode ontvangen, gebruik de link in de activatie e-mail om jouw account te activeren.";
		break;
	case 7:
		$_SESSION['lerror'][] = "Activatiecode ongeldig, gebruik de link in de activatie e-mail om jouw account te activeren.";
		break;
		}
	
	if (!$_dnr->LoginCheckWachtwoord($_POST['wachtwoord'])) {
		$_SESSION['lerror'][] = "Wachtwoord onjuist. Probeer nogmaals.";
		}
	
	if ($_SESSION['lerror']) {
		$verbreken = mysql_close($verbinding);	
		header('Location: index.php');
		exit;
		}
	else {
		if ($_dnr->Login()==1) {
			$_SESSION['lerror'][] = "Ophogen login status naar geactiveerd mislukt.";
			$verbreken = mysql_close($verbinding);	
			header('Location: index.php');
			exit;
			}
		else if ($_dnr->Login()==2) {
			$_SESSION['lerror'][] = "Gebruikersnaam en wachtwoord moeten ingevuld worden.";
			$verbreken = mysql_close($verbinding);	
			header('Location: index.php');
			exit;
			}
		else { // Login geslaagd, inclusief zonodig nog activatie vanuit bevestigingsmail
			$verbreken = mysql_close($verbinding);	
			$_SESSION['loggedin'] = 1;
			header('Location: uitslagen.php');
			exit;
			}
		}
	}
	
if ($_POST['nieuw']) {
	$_SESSION = array();
	session_destroy();
	session_start();

	$_SESSION['register'] = 1;
	header('Location: index.php');
	exit;
	}
	
if (isset($_GET['reset_request'])) { // gebruikersnaam vragen
	unset($_GET);
	$_SESSION['reset_request'] = 1;
	header('Location: index.php');
	exit;
	}

if ($_POST['reset_request']) {
 	$result = $_dnr->LoginCheckGebruikersnaam(mysql_real_escape_string($_POST['gebruikersnaam']));
 	switch ($result) {
	case 1: 
		$_SESSION['lerror'][] = "Gebruiker is niet geregistreerd.";
		break;
	case 2:
		$_SESSION['lerror'][] = "Ophogen login status mislukt.";
		break;
	case 3:
		$_SESSION['lerror'][] = "Gebruikersnaam niet bekend.";
		break;
	case 4:
		$_SESSION['lerror'][] = "Gebruikersnaam is niet ingevuld.";
		break;
	case 6:
		$_SESSION['lerror'][] = "Geen activatiecode ontvangen, gebruik de link in de activatie e-mail om jouw account te activeren.";
		break;
	case 7:
		$_SESSION['lerror'][] = "Activatiecode ongeldig, gebruik de link in de activatie e-mail om jouw account te activeren.";
		break;
	case 8:
		$_SESSION['lerror'][] = "Geen resetcode ontvangen, gebruik de link in de reset e-mail om jouw wachtwoord te resetten.";
		break;
	case 9:
		$_SESSION['lerror'][] = "Resetcode ongeldig, gebruik de link in de reset e-mail om jouw wachtwoord te resetten.";
		break;
		}
	if ($_SESSION['lerror']) {
		$verbreken = mysql_close($verbinding);	
		header('Location: index.php'); // $_SESSION['reset_request'] is nog steeds 1
		exit;
		}
	else {
	 	$result = $_dnr->StuurResetMail();
		$verbreken = mysql_close($verbinding);	
	 	switch ($result) {
	 	case 1:
			$_SESSION['lerror'][] = "Reset mislukt (stap 1).";
			break;
	 	case 2:
			$_SESSION['lerror'][] = "Reset mislukt (stap 2).";
			break;
	 	case 3:
	 		/* Nu is de reset request met succes voltooid: 
			- resetcode is aangemaakt 
			- bevestigingsmail voor reset verzonden
	 		*/
			// Zodra het wachtwoord gereset is vanuit de bevestigingsmail wordt het nieuwe wachtwoord opgeslagen en kan er weer ingelogd worden.
			$_SESSION['reset_requested'] = 1;  // doorgeven dat registratie geslaagd is
			unset($_SESSION['reset_request']);
			header('Location: index.php');
			exit;
			}

		header('Location: index.php');
		exit;
		}
	}

if ($_POST['reset']) {
 	$_SESSION['resetcode'] = $_POST['resetcode'];  // voor als reset-formulier opnieuw geladen moet worden na validatiefout
 	$result = $_dnr->CheckWachtwoorden($_POST['wachtwoord'], $_POST['wachtwoord_nogmaals']);
	if ($result == 3) {
		$_SESSION['lerror'][] = "Wachtwoordvelden moeten worden ingevuld.";
		}
	if ($result == 2) {
		$_SESSION['lerror'][] = "Wachtwoorden komen niet overeen.";
		}

	if ($_SESSION['lerror']) {
		$verbreken = mysql_close($verbinding);	
		header('Location: index.php'); // $_SESSION['resetcode'] is nog steeds set
		exit;
		}
	else {
	 	$result = $_dnr->Login();
		$verbreken = mysql_close($verbinding);	
		if ($result==1) {
			$_SESSION['lerror'][] = "Ophogen login status naar geactiveerd mislukt.";
			}
		else if ($result==2) {
			$_SESSION['lerror'][] = "Gebruikersnaam en wachtwoord moeten ingevuld worden.";
			}
		else if ($result==4) {
			$_SESSION['lerror'][] = "Opslaan nieuw wachtwoord mislukt.";
			}
		else if ($result==5) {
			$_SESSION['lerror'][] = "Wachtwoord moet ingevuld worden.";
			}
		else { // Login geslaagd, inclusief wachtwoord reset vanuit bevestigingsmail
			unset($_SESSION['resetcode']);
			$_SESSION['loggedin'] = 1;
			header('Location: uitslagen.php');
			exit;
			}
		header('Location: index.php');
		exit;
		}
	}
	
if ($_POST['save'])	{

	$_SESSION['register'] = 1;

	if ($_dnr->CheckVoornaam(mysql_real_escape_string($_POST['voornaam'])) == 3) {
		$_SESSION['lerror'][] = "Voornaam moet ingevuld worden.";
		}
	if ($_dnr->CheckVoornaam(mysql_real_escape_string($_POST['voornaam'])) == 2) {
		$_SESSION['lerror'][] = "Voornaam bevat ongeldige tekens (alleen letters toegestaan).";
		}
	if ($_dnr->CheckTv(mysql_real_escape_string($_POST['tv'])) == 2) {
		$_SESSION['lerror'][] = "Tussenvoegsel bevat ongeldige tekens (alleen letters toegestaan).";
		}
	if ($_dnr->CheckAchternaam(mysql_real_escape_string($_POST['achternaam'])) == 3) {
		$_SESSION['lerror'][] = "Achternaam moet ingevuld worden.";
		}
	if ($_dnr->CheckAchternaam(mysql_real_escape_string($_POST['achternaam'])) == 2) {
		$_SESSION['lerror'][] = "Achternaam bevat ongeldige tekens (alleen letters toegestaan).";
		}
	if ($_dnr->CheckEmailAdres(mysql_real_escape_string($_POST['email'])) == 3) {
		$_SESSION['lerror'][] = "E-mail adres moet ingevuld worden.";
		}
	if ($_dnr->CheckEmailAdres(mysql_real_escape_string($_POST['email'])) == 2) {
		$_SESSION['lerror'][] = "Vul een geldig e-mail adres in.";
		}
	if ($_dnr->CheckGebruikersnaam(mysql_real_escape_string($_POST['gebruikersnaam'])) == 4) {
		$_SESSION['lerror'][] = "Gebruikersnaam moet ingevuld worden.";
		}
	if ($_dnr->CheckGebruikersnaam(mysql_real_escape_string($_POST['gebruikersnaam'])) == 3) {
		$_SESSION['lerror'][] = "Gebruikersnaam bevat ongeldige tekens (alleen letters, cijfers, -, _ toegestaan).";
		}
	if ($_dnr->CheckGebruikersnaam(mysql_real_escape_string($_POST['gebruikersnaam'])) == 2) {
		$_SESSION['lerror'][] = "Gebruikersnaam bestaat al.";
		}
	if ($_dnr->CheckWachtwoorden($_POST['wachtwoord'], $_POST['wachtwoord_nogmaals']) == 3) {
		$_SESSION['lerror'][] = "Wachtwoordvelden moeten worden ingevuld.";
		}
	if ($_dnr->CheckWachtwoorden($_POST['wachtwoord'], $_POST['wachtwoord_nogmaals']) == 2) {
		$_SESSION['lerror'][] = "Wachtwoorden komen niet overeen.";
		}

	if ($_SESSION['lerror']) {
		$verbreken = mysql_close($verbinding);	
		header('Location: index.php');
		exit;
		}
	else {
	 	$result = $_dnr->Register();
		$verbreken = mysql_close($verbinding);	
	 	switch ($result) {
	 	case 1:
			$_SESSION['lerror'][] = "Registreren mislukt (starten sql transactie niet geslaagd, niets toegevoegd).";
			break;
	 	case 2:
			$_SESSION['lerror'][] = "Registreren mislukt (toevoegen deelnemer en scores niet geslaagd).";
			break;
	 	case 3:
			$_SESSION['lerror'][] = "Registreren mislukt (ophalen autoincrementwaarde van deelnrId mislukt).";
			break;
	 	case 4:
			$_SESSION['lerror'][] = "Registreren mislukt (ophalen wedstrijden mislukt, niets toegevoegd).";
			break;
	 	case 5:
			$_SESSION['lerror'][] = "Registreren mislukt (afronden sql transactie niet geslaagd, niets toegevoegd).";
			break;
	 	case 6:
			$_SESSION['lerror'][] = "Registreren gelukt maar ophogen login status mislukt.";
			break;
	 	case 7:
			$_SESSION['lerror'][] = "Registreren gelukt maar verzenden bevestigingsmail voor activatie mislukt.";
			break;
	 	case 8:
	 		/* Nu is het registreren geslaagd: 
			- Login_status is opgehoogd van 0 naar 1. 
			- activatiecode is aangemaakt
			- bevestigingsmail voor activatie verzonden
	 		*/
			// Zodra het account geactiveerd is vanuit de bevestigingsmail wordt Login_status 2 en kunnen er prognoses ingevuld worden.
			$_SESSION['registered'] = 1;  // doorgeven dat registratie geslaagd is
			unset($_SESSION['register']);
			header('Location: index.php');
			exit;
	 	default:
	 		$volgnr = $result - 100;
			$_SESSION['lerror'][] = "Registreren mislukt (toevoegen lege scorerecords na nr ".$volgnr." niet geslaagd, deelnemer niet toegevoegd).";
			}
		
		if ($result != 8) {
			header('Location: index.php');
			exit;
			}
		}
	}
?>
