<?php
ini_set('display_errors', 'Off');
// error_reporting(E_ALL);
session_start();
include("includes/page_layout.inc.php");

$_SESSION['loggedin'] = 0;
$_SESSION['l_level'] = 0;

$message_lines = "";
if(!empty($_SESSION['lerror'])){
   if(is_array($_SESSION['lerror'])){
        foreach($_SESSION['lerror'] as $value){
            $message_lines .= "<p style=\"color:red\">".$value."</p>\n";
        	}
	   }
	   $_SESSION['lerror'] = NULL;
	}

if (isset($_SESSION['registered'])) {
 	unset($_SESSION['registered']);
    $message_lines .= "<p>Er is een bevestigingsmail gestuurd naar het door jou opgegeven e-mail adres.";
    $message_lines .= "<br />Klik in die mail op de activatielink om de registratie te voltooien.</p>";
	}
	
if (isset($_SESSION['register']) and $_SESSION['register']) {  // wordt in login.php geïnitialiseerd
	$voornaam = (isset($_SESSION['voornaam'])) ? $_SESSION['voornaam'] : "";
	$tv = (isset($_SESSION['tv'])) ? $_SESSION['tv'] : "";
	$achternaam = (isset($_SESSION['achternaam'])) ? $_SESSION['achternaam'] : "";
	$email = (isset($_SESSION['email'])) ? $_SESSION['email'] : "";
	$gebruikersnaam = (isset($_SESSION['gebruikersnaam'])) ? $_SESSION['gebruikersnaam'] : "";
$html_page = <<<HTMLPAGE
		<!-- breedte in afwijking van css -->
		<div id="paginainhoudbox" style="width: 745px"> 
			<h1>Registreren</h1>
			
			{$message_lines}
			<form style="width: 525px; " method="post" action="login.php">
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
				        <input type="text" style="width: 360px;" name="gebruikersnaam" id="gebruikersnaam" value="{$gebruikersnaam}" />
					</div>	
					<div class="schermregel">
				        <label class="label" for="wachtwoord">Wachtwoord</label>
				        <input type="password" style="width: 360px;" name="wachtwoord" id="wachtwoord" />
					</div>
					<div class="schermregel">
				        <label class="label" for="wachtwoord_nogmaals">Wachtwoord (nogmaals)</label>
				        <input type="password" style="width: 360px;" name="wachtwoord_nogmaals" id="wachtwoord_nogmaals" />
					</div>
					<center>	
					<div style="padding-top: 15px;">
					    <input style="margin-left: 15px; width: 80px" type="submit" value="Opslaan" name="save"/>
				    </div>
				    </center>
				</div>	
			</form>
HTMLPAGE;
	}
	
elseif (isset($_SESSION['reset_request'])) {  // reset wachtwoord stap 1 van 3
$message_lines .= "<p>Gebruikersnaam waarvoor een nieuw wachtwoord moet worden opgegeven</p>";
$html_page = <<<HTMLPAGE
		<!-- breedte in afwijking van css -->
		<div id="paginainhoudbox" style="width: 745px"> 
			<h1>Wachtwoord reset (stap 1 van 3)</h1>
			
			{$message_lines}
			<form style="width: 525px; " method="post" action="login.php">
				<div style="width: 505px; padding-top: 15px;">
					<div class="schermregel">
				        <label class="label" for="gebruikersnaam">Gebruikersnaam</label>
				        <input type="text" style="width: 360px;" name="gebruikersnaam" id="gebruikersnaam" />
					</div>	
					<center>	
					<div style="padding-top: 15px;">
					    <input style="margin-left: 15px; width: 150px" type="submit" value="Stuur mail met reset-link" name="reset_request"/>
				    </div>
				    </center>
				</div>	
			</form>
HTMLPAGE;
	}
	
else if (isset($_SESSION['reset_requested'])) {  // reset wachtwoord stap 2 van 3
 	unset($_SESSION['reset_requested']);
    $message_lines .= "<p>Er is een mail gestuurd naar jouw e-mail adres.";
    $message_lines .= "<br />Klik in die mail op de reset-link om een nieuw wachtwoord op te kunnen geven.</p>";
$html_page = <<<HTMLPAGE
		<!-- breedte in afwijking van css -->
		<div id="paginainhoudbox" style="width: 745px"> 
			<h1>Wachtwoord reset (stap 2 van 3)</h1>
			
			{$message_lines}
HTMLPAGE;
	}
	
else if (isset($_GET['resetcode']) or isset($_SESSION['resetcode'])) {  // reset wachtwoord stap 3 van 3
 	if (isset($_SESSION['resetcode'])) { $resetcode = $_SESSION['resetcode']; }
 	if (isset($_GET['resetcode'])) { $resetcode = $_GET['resetcode']; }
 	unset($_SESSION['resetcode']);

$html_page = <<<HTMLPAGE
		<!-- breedte in afwijking van css -->
		<div id="paginainhoudbox" style="width: 745px"> 
			<h1>Wachtwoord reset (stap 3 van 3)</h1>
			
			{$message_lines}
			<form style="width: 525px; " method="post" action="login.php">
				<div style="width: 505px; padding-top: 15px;">
					<div class="schermregel">
				        <label class="label" for="wachtwoord">Wachtwoord</label>
				        <input type="password" style="width: 360px;" name="wachtwoord" id="wachtwoord" />
					</div>
					<div class="schermregel">
				        <label class="label" for="wachtwoord_nogmaals">Wachtwoord (nogmaals)</label>
				        <input type="password" style="width: 360px;" name="wachtwoord_nogmaals" id="wachtwoord_nogmaals" />
					</div>
					<input type="hidden" name="resetcode" id="resetcode" value="{$resetcode}"/>
					<center>	
					<div style="padding-top: 15px;">
					    <input style="margin-left: 15px; width: 80px" type="submit" value="Opslaan" name="reset"/>
				    </div>
				    </center>
				</div>	
			</form>
HTMLPAGE;
	}

else {
 	
 	if (isset($_GET['activatiecode'])) {
		if ($instelling == "SW") { // versie voor scouting winterswijk met redirect:
			include("includes/verbinding.inc.php");
			$_activatiecode = $_GET['activatiecode'];
			$sql = "UPDATE `deelnemer` SET `Login_status` = '2' WHERE `Activatiecode` = '".$_activatiecode."';";
			if (!mysql_query($sql))	{ die('Ophogen login status naar geactiveerd mislukt'); } 
			$verbreken = mysql_close($verbinding);	
			header('Location: '.$redirectto);
			exit; 
			} 
		else {
			$activatiecode = (isset($_GET['activatiecode'])) ? $_GET['activatiecode'] : "";	
			}
		}
	$url = "http://".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\')."/login.php";	

$html_page = <<<HTMLPAGE
		<!-- breedte in afwijking van css -->
		<div id="paginainhoudbox" style="width: 745px"> 
			<h1>Inloggen</h1>
			
			{$message_lines}
			<form style="width: 525px; " method="post" action="login.php">
				<div style="width: 505px; padding-top: 15px;">
					<div class="schermregel">
				        <label class="label" for="gebruikersnaam">Gebruikersnaam</label>
				        <input type="text" style="width: 360px;" name="gebruikersnaam" id="gebruikersnaam" />
					</div>	
					<div class="schermregel">
				        <label class="label" for="wachtwoord">Wachtwoord</label>
				        <input type="password" style="width: 360px;" name="wachtwoord" id="wachtwoord" />
					</div>
					<input type="hidden" name="activatiecode" id="activatiecode" value="{$activatiecode}"/>
					<center>	
					<div style="padding-top: 15px;">
					    <input style="margin-left: 15px; width: 80px" type="submit" value="Inloggen" name="login"/>
					    <input style="margin-left: 15px; width: 80px" type="submit" value="Nieuw" name="nieuw"/>
				    </div>
				    <a style="margin-top: 5px; font-size: 0.7em;" href="$url?reset_request">Wachtwoord vergeten?</a>
				    </center>
				</div>	
			</form>
HTMLPAGE;
	}
	
echo $html_header.$html_page.$html_footer;