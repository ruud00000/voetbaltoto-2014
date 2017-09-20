<?php
session_start();
include "includes/config.inc.php";

class DeelnemerFunctions
{
var $wachtwoord;
var $gebruikersnaam;
var $email;
var $deelnrId;
var $voornaam;
var $tv;
var $achternaam;
var $level;

function CheckGebruikersnaam($gebruikersnaam) {
    $gebruikersnaam = trim($gebruikersnaam);
	$_SESSION['gebruikersnaam'] = $gebruikersnaam;
    if (empty($gebruikersnaam)) return 4; // gebruikersnaam niet ingevuld
	$aValid = array('-', '_'); 
	if(!ctype_alnum(str_replace($aValid, '', $gebruikersnaam))) return 3;  // ongeldige tekens in gebruikersnaam 
   	$check = mysql_query('SELECT `DeelnrId`,`Gebruikersnaam` FROM `deelnemer` WHERE `Gebruikersnaam`="'.$gebruikersnaam.'"');
   	if (mysql_num_rows($check) != 0) return 2; // gebruikersnaam bestaat al
    list($_deelnrId,$gn) = mysql_fetch_array($check);
    $this->gebruikersnaam = $gebruikersnaam;
    $this->deelnrId = $_deelnrId;
    return 1; // ok
    }

function CheckWachtwoorden($ww1,$ww2) {
    $ww1 = trim($ww1);
    $ww2 = trim($ww2);
    if (!empty($ww1) && !empty($ww1)) {
		if (isset($_SESSION['resetcode'])) { // gebruikersnaam etc. ophalen met resetcode
			$check = mysql_query('SELECT `DeelnrId`, `Gebruikersnaam`, `Level`, `Voornaam`, `Tv`, `Achternaam`, `Email`, `Login_status`, `Activatiecode`, `Resetcode` FROM `deelnemer` WHERE `Resetcode`="'.$_SESSION['resetcode'].'"');
			if (mysql_num_rows($check) == 1) {
				list($_deelnrId, $gn, $_level,$_voornaam, $_tv, $_achternaam, $_email, $login_status, $activatiecode, $_resetcode) = mysql_fetch_array($check);
				$_SESSION['resetten'] = 1; // zorg dat gereset wordt als de rest van de inlogprocedure slaagt
				$this->gebruikersnaam = $gebruikersnaam;
				$this->deelnrId = $_deelnrId;
				$this->level = $_level;
				$this->voornaam = $_voornaam;
				$this->tv = $_tv;
				$this->achternaam = $_achternaam;
				$this->email = $_email;
			 	}
			}
        $ww1 = trim($ww1);
        $ww2 = trim($ww2);
        if ($ww1 == $ww2) {
            $this->wachtwoord = md5(trim($ww1).'efjd');
            return 1;
            }
        else {
            return 2;
            }
        }
    else {
		return 3;
		}
    }

function CheckEmailAdres($email) {
	$email = trim($email);
	$_SESSION['email'] = $email;
	if (!empty($email)) {
	 	if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$this->email = $email;
		$_SESSION['email'] = $this->email;
		return 1;
			}
		else {
			return 2;
			}
		}
	else {
		return 3;
		}
	}

function CheckVoornaam($voornaam) {
	$voornaam = trim($voornaam);
	$_SESSION['voornaam'] = $voornaam;
	if (!empty($voornaam)) {
		$aValid = array('-', '_', ' '); 
		if(ctype_alpha(str_replace($aValid, '', $voornaam))) {
			$this->voornaam = $voornaam;
			$_SESSION['voornaam'] = $this->voornaam;
			return 1;
			}
		else {
			return 2;
			}
		}
	else {
		return 3;
		}
	}

function CheckTv($tv) {
	$tv = trim($tv);
	$_SESSION['tv'] = $tv;
	if (!empty($tv)) {
		if(ctype_alpha($tv)) { 
			$this->tv = $tv;
			$_SESSION['tv'] = $this->tv;
			return 1;
			}
		else {
			return 2;
			}
		}
	else {
		return 1;
		}
	}
	
function CheckAchternaam($achternaam) {
	$_SESSION['achternaam'] = $achternaam;
	$achternaam = trim($achternaam);
	if (!empty($achternaam)) {
		$aValid = array('-', '_', ' '); 
		if(ctype_alpha(str_replace($aValid, '', $achternaam))) {
			$this->achternaam = $achternaam;
			$_SESSION['achternaam'] = $this->achternaam;
			return 1;
			}
		else {
			return 2;
			}
		}
	else {
		return 3;
		}
	}

function Register() {
 	if (!mysql_query("START TRANSACTION;")) 
		{ return 1; } // starten sql transactie niet geslaagd
    if (!mysql_query("INSERT INTO `deelnemer` (Voornaam, Tv, Achternaam, Email, Gebruikersnaam, Wachtwoord, Login_status) VALUES ('".$this->voornaam."','".$this->tv."','".$this->achternaam."','".$this->email."','".$this->gebruikersnaam."','".$this->wachtwoord."','0')")) 
		{ return 2; } // toevoegen deelnemer en scores niet geslaagd 
	$result = mysql_query("SELECT MAX(DeelnrId) AS max FROM `deelnemer` ORDER BY `deelnrId`");
	$row = mysql_fetch_array($result);
	$this->deelnrId = $row['max'];
	if (!$this->deelnrId) 
		{ return 3; } // ophalen autoincrementwaarde van deelnrId mislukt
	$result = mysql_query("SELECT `WedstrijdId` FROM `wedstrijd` ORDER BY `WedstrijdId`");
	if (!$result) 
		{ return 4; } // ophalen wedstrijden mislukt
	$i = 100;
	while ($row = mysql_fetch_array($result) ) {
		if (!mysql_query("INSERT INTO `scores` (DeelnrId, WedstrijdId) VALUES ('".$this->deelnrId."', '".$row['WedstrijdId']."')")) 
			{ return $i; } // toevoegen lege scorerecords (deels?) niet geslaagd, check deelnemer toegevoegd 
		$i++;
		}
	if (!mysql_query("COMMIT;")) { return 5; } // afronden sql transactie niet geslaagd, check niets toegevoegd
	$arandom = getdate();
	$random = $this->deelnrId.$this->gebruikersnaam.$arandom[0];
	$activatiecode = md5($random);
    if (!mysql_query("UPDATE `deelnemer` SET `Login_status` = '1', `Activatiecode` = '".$activatiecode."' WHERE `DeelnrId` = '".$this->deelnrId."' AND `Login_status` = '0';")) 
		{ return 6; } // registreren gelukt maar ophogen login status en maken activatiecode niet

	// bevestigingsmail voor activeren sturen
	global $year, $from, $from_text, $webadmin;
	$gebruikersnaam = $this->gebruikersnaam;
	$voornaam = $this->voornaam;
	$tv = (empty($this->tv)) ? "" : " ".$this->tv;
	$achternaam = $this->achternaam;
	$email = $this->email;
	$to = $voornaam.$tv." ".$achternaam." <".$email.">";
	$bcc = $webadmin;
	$subject = "Activatie Voetbaltoto account van ".$voornaam.$tv." ".$achternaam." (".$gebruikersnaam.")";
	$href = "http://".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\')."/index.php?activatiecode=".$activatiecode;	
	
$message = <<<ACTIVATIETEKST
	<html>
	<head>
	  <title>Activatie Voetbaltoto account van $voornaam$tv $achternaam [$gebruikersnaam] ($deelnemer)</title>
	</head>
	<body>
		<p>Klik op onderstaande link om terug te keren naar het inlogscherm van de voetbaltoto site om in te loggen met de gebruikersnaam en het wachtwoord dat je zelf hebt aangemaakt.</p><br />
		<p><a href="$href">Activeer mijn account<a/></p>
	</body>
	</html>
ACTIVATIETEKST;
	
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= 'From: '.$from_text.' '.$year.' <'.$from.'>' . "\r\n";
	$headers .= 'Bcc: '.$bcc. "\r\n";
	
	if (!mail($to, $subject, $message, $headers)) 
		{ return 7; } // registreren geslaagd maar verzenden bevestigingsmail voor activatie mislukt (stap 1)

	return 8; // ok
    }

function StuurResetMail() {
	$arandom = getdate();
	$random = $this->deelnrId.$this->gebruikersnaam.$arandom[0];
	$resetcode = md5($random);
    if (!mysql_query("UPDATE `deelnemer` SET `Resetcode` = '".$resetcode."' WHERE `DeelnrId` = '".$this->deelnrId."';")) 
		{ return 1; } // resetcode opslaan mislukt

	// bevestigingsmail voor reset sturen
	global $year, $from, $from_text, $webadmin;
	$gebruikersnaam = $this->gebruikersnaam;
	$voornaam = $this->voornaam;
	$tv = (empty($this->tv)) ? "" : " ".$this->tv;
	$achternaam = $this->achternaam;
	$email = $this->email;
	$to = $voornaam.$tv." ".$achternaam." <".$email.">";
	$bcc = $webadmin;
	$subject = "Aanvraag reset wachtwoord voor Voetbaltoto account van ".$voornaam.$tv." ".$achternaam." (".$gebruikersnaam.")";
	$href = "http://".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\')."/index.php?resetcode=".$resetcode;	

	
$message = <<<ACTIVATIETEKST
	<html>
	<head>
	  <title>Aanvraag reset wachtwoord voor Voetbaltoto account van $voornaam$tv $achternaam [$gebruikersnaam] ($deelnemer)</title>
	</head>
	<body>
		<p>Klik op onderstaande link om terug te keren naar het inlogscherm van de voetbaltoto site om in te loggen met jouw gebruikersnaam en een nieuw wachtwoord.</p><br />
		<p><a href="$href">Reset mijn wachtwoord<a/></p>
	</body>
	</html>
ACTIVATIETEKST;
	
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= 'From: '.$from_text.' '.$year.' <'.$from.'>' . "\r\n";
	$headers .= 'Bcc: '.$bcc. "\r\n";
	
	if (!mail($to, $subject, $message, $headers)) 
		{ return 2; } // resetcode aangemaakt maar verzenden bevestigingsmail voor reset mislukt (stap 2)

	return 3; // ok
    }
    
function LoginCheckGebruikersnaam($gebruikersnaam) {
    if (!empty($gebruikersnaam)) {
		$check = mysql_query('SELECT `DeelnrId`, `Gebruikersnaam`, `Level`, `Voornaam`, `Tv`, `Achternaam`, `Email`, `Login_status`, `Activatiecode`, `Resetcode` FROM `deelnemer` WHERE `Gebruikersnaam`="'.$gebruikersnaam.'"');
		if (mysql_num_rows($check) == 1) {
			list($_deelnrId, $gn, $_level,$_voornaam, $_tv, $_achternaam, $_email, $login_status, $activatiecode, $_resetcode) = mysql_fetch_array($check);
			if ($login_status==0) { return 1; } // gebruiker is niet geregistreerd
			if ($login_status==1) { 
			 	if (empty($_POST['activatiecode'])) { return 6;} // geen activatiecode ontvangen, gebruik de link in de activatie e-mail om jouw account te activeren
			 	if ($_POST['activatiecode']!=$activatiecode) { return 7; } // activatiecode ongeldig, gebruik de link in de activatie e-mail om jouw account te activeren
			 	$_SESSION['activeer'] = 1; // zorg dat geactiveerd wordt als de rest van de inlogprocedure slaagt
				} 
			/*
			if (!empty($_resetcode)) { // wachtwoord moet gereset worden
			 	if (empty($_POST['resetcode'])) { return 8;} // geen resetcode ontvangen, gebruik de link in de reset e-mail om jouw wachtwoord te resetten 
			 	if ($_POST['resetcode']!=$_resetcode) { return 9; } // resetcode ongeldig, gebruik de link in de reset e-mail om jouw wachtwoord te resetten
				$_SESSION['resetten'] = 1; // zorg dat gereset wordt als de rest van de inlogprocedure slaagt
				}
			*/
			$this->gebruikersnaam = $gebruikersnaam;
			$this->deelnrId = $_deelnrId;
			$this->level = $_level;
			$this->voornaam = $_voornaam;
			$this->tv = $_tv;
			$this->achternaam = $_achternaam;
			$this->email = $_email;
			return 5; //ok
			}
		else {
			return 3; // gebruiker bestaat niet
			}
        }
    else {
		return 4; // gebruikersnaam is niet ingevuld
		}
    }

function LoginCheckWachtwoord($ww) {
	if (!empty($ww)) {
       $ww = md5($ww.'efjd');
		$check = mysql_query("SELECT `Wachtwoord` FROM `deelnemer` WHERE `Wachtwoord`='".$ww."' AND `Gebruikersnaam`='".$this->gebruikersnaam."'");
		if (mysql_num_rows($check) == 1) {
			$this->wachtwoord = $ww;
			return true;
			}
		else {
			return false;
			}
		}
	else {
		return false;
		}
    }

function Login() {
    if ($_SESSION['activeer']) { // er moet nog geactiveerd worden
		if (empty($this->wachtwoord) or empty($this->gebruikersnaam)) { return 2; } // wachtwoord en gebruikersnaam moeten ingevuld worden
		unset($_SESSION['activeer']);
		if (!mysql_query("UPDATE `deelnemer` SET `Login_status` = '2' WHERE `DeelnrId` = '".$this->deelnrId."';"))
			{ return 1; } // ophogen login status naar geactiveerd mislukt
		}
	else if ($_SESSION['resetten']) { // gereset wachtwoord moet nog opgeslagen worden 
		if (empty($this->wachtwoord)) { return 5; } // wachtwoord moet ingevuld worden
		unset($_SESSION['resetten']);
	 	if (!mysql_query("UPDATE `deelnemer` SET `Wachtwoord` = '".$this->wachtwoord."', `Resetcode`=NULL WHERE `Resetcode` = '".$_SESSION['resetcode']."';")) { 
			unset($_SESSION['resetcode']);
			return 4; // opslaan nieuw wachtwoord mislukt
			} 
		else {
			unset($_SESSION['resetcode']);
			}
		}
	else if (empty($this->wachtwoord) or empty($this->gebruikersnaam)) { return 2; } // wachtwoord en gebruikersnaam moeten ingevuld worden
	
	$_SESSION['loggedin'] = 1;
	$result = mysql_query("SELECT AllowPrognoses, AllowOnbetaald FROM `instellingen`");
	$row = mysql_fetch_array($result);
	$_SESSION['allowPrognoses'] = $row['AllowPrognoses'];
	$_SESSION['allowOnbetaald'] = $row['AllowOnbetaald'];
	// er wordt onderscheid gemaakt tussen gebruikersnaam etc waarmee ingelogd is versus die ergens geselecteerd is (door admin)
	$_SESSION['l_gebruikersnaam'] = $this->gebruikersnaam;
	$_SESSION['l_deelnrId'] = $this->deelnrId;
	$_SESSION['l_level'] = $this->level;
	$_SESSION['l_voornaam'] = $this->voornaam;
	$_SESSION['l_tv'] = $this->tv;
	$_SESSION['l_achternaam'] = $this->achternaam;
	$_SESSION['l_email'] = $this->email;
	return 3; // ok
    }

function Logout() {
    if ($_SESSION['loggedin'] == 1) {
		$_SESSION['loggedin'] = 0;
		return true;
		}
    else {
		return false;
		}
    }

function VerwijderDeelnemer($deelnrId) {
 	if (!mysql_query("START TRANSACTION;")) 
		{ return 1; } // starten sql transactie niet geslaagd
    if (!mysql_query("DELETE FROM `scores` WHERE `scores`.`DeelnrId` = '".$deelnrId."';")) 
		{ return 2; } // verwijderen scores van deelnemer mislukt 
	if (!mysql_query("DELETE FROM `deelnemer` WHERE `deelnemer`.`DeelnrId` = '".$deelnrId."';")) 
		{ return 3; } // verwijderen deelnemer mislukt
	if (!mysql_query("COMMIT;")) 
		{ return 4; } // afronden sql transactie niet geslaagd, check niets verwijderd
	return 5; // OK: picklist opnieuw opbouwen en tonen
    }
    
function GetDeelnemer($deelnrId) {
   	$result = mysql_query("SELECT `DeelnrId`,`Gebruikersnaam`, `Voornaam`, `Tv`, `Achternaam`, `Email` FROM `deelnemer` WHERE `DeelnrId` = '".$deelnrId."';");
   	if (!result) 
	   { return 1; } // ophalen van gebruikersgegevens mislukt
	$row = mysql_fetch_array($result);
	$_SESSION['deelnrId'] = $deelnrId;
	$_SESSION['voornaam'] = $row['Voornaam'];
	$_SESSION['tv'] = $row['Tv'];
	$_SESSION['achternaam'] = $row['Achternaam'];
	$_SESSION['email'] = $row['Email'];
	$_SESSION['gebruikersnaam'] = $row['Gebruikersnaam'];
	return 2;
	}

function WijzigDeelnemer($deelnrId) {
 	$gebruikersnaam = mysql_real_escape_string($_SESSION['gebruikersnaam']);
 	$voornaam = mysql_real_escape_string($_SESSION['voornaam']);
 	$tv = mysql_real_escape_string($_SESSION['tv']);
 	$achternaam = mysql_real_escape_string($_SESSION['achternaam']);
 	$email = mysql_real_escape_string($_SESSION['email']);
 	$deelnrId = $_SESSION['deelnrId'];
 	
 	if (!mysql_query("UPDATE `deelnemer` SET `Gebruikersnaam` = '".$gebruikersnaam."', `Voornaam` = '".$voornaam."', `Tv` = '".$tv."', `Achternaam` = '".$achternaam."', `Email` = '".$email."' WHERE `deelnemer`.`DeelnrId` = '".$deelnrId."';")) 
		{ return 1; } // opslaan mislukt
	if ($_SESSION['l_level'] == 1) { // dan zijn de deelnemergegevens de gegevens van degene die ingelogd is; 
									 // admin moet maar even browser herstarten als hij gegevens van zichzelf gewijzigd heeft...
		$_SESSION['l_gebruikersnaam'] = $this->gebruikersnaam;
		$_SESSION['l_voornaam'] = $this->voornaam;
		$_SESSION['l_tv'] = $this->tv;
		$_SESSION['l_achternaam'] = $this->achternaam;
		$_SESSION['l_email'] = $this->email;
		}
		
	return 2; // ok
    }

}
$_dnr = new DeelnemerFunctions();
?>