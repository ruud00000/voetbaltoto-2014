<?php
session_start();
if (!isset($_SESSION['loggedin']) or $_SESSION['loggedin']!=1 or $_SESSION['l_level']<2) { header('Location: logout.php'); exit; }
include "includes/config.inc.php";
include_once "includes/page_layout.inc.php";

if (!isset($_POST["save"])) { 
	include("includes/verbinding.inc.php");
	$sql = "SELECT `deelnemer`.*, IF(ISNULL(q1.DeelnrId),'1','0') AS HasPrognoses 
	FROM `deelnemer` 
	LEFT OUTER JOIN (SELECT DISTINCT DeelnrId FROM `scores` WHERE ISNULL(Progtoto)) q1
	ON `deelnemer`.DeelnrId=q1.DeelnrId 
	ORDER BY Achternaam, Voornaam, `deelnemer`.DeelnrId";
	$result = mysql_query($sql);
	$verbreken = mysql_close($verbinding);	// Verbreken van de verbinding met de database
 	$_SESSION['deelnemers'] = array(); 
 	$i = 0;
	while ($row = mysql_fetch_array($result) ) {
		$_SESSION['deelnemers'][$i] = array();		
		$_SESSION['deelnemers'][$i]['DeelnrId'] = $row['DeelnrId'];
		$_SESSION['deelnemers'][$i]['Voornaam'] = $row['Voornaam'];
		$_SESSION['deelnemers'][$i]['Tv'] = $row['Tv'];
		$_SESSION['deelnemers'][$i]['Achternaam'] = $row['Achternaam'];
		$_SESSION['deelnemers'][$i]['Email'] = $row['Email'];
		$_SESSION['deelnemers'][$i]['Gebruikersnaam'] = $row['Gebruikersnaam'];
		$_SESSION['deelnemers'][$i]['Wachtwoord'] = $row['Wachtwoord'];
		$_SESSION['deelnemers'][$i]['Level'] = $row['Level'];
		$_SESSION['deelnemers'][$i]['Login_status'] = $row['Login_status'];
		$_SESSION['deelnemers'][$i]['Activatiecode'] = $row['Activatiecode'];
		$_SESSION['deelnemers'][$i]['Resetcode'] = $row['Resetcode'];
		$_SESSION['deelnemers'][$i]['Betaald'] = $row['Betaald'];
		$_SESSION['deelnemers'][$i]['HasPrognoses'] = $row['HasPrognoses'];
		$i++;
		}
	}
	
if (isset($_POST["save"])) { 
 	$_SESSION['deelnemers'] = array(); 
 	foreach($_POST['DeelnrId'] as $sKey => $aValue) {
		$_SESSION['deelnemers'][$sKey]['DeelnrId'] = $_POST['DeelnrId'][$sKey];
		$_SESSION['deelnemers'][$sKey]['Voornaam'] = $_POST['Voornaam'][$sKey];
		$_SESSION['deelnemers'][$sKey]['Tv'] = $_POST['Tv'][$sKey];
		$_SESSION['deelnemers'][$sKey]['Achternaam'] = $_POST['Achternaam'][$sKey];
		$_SESSION['deelnemers'][$sKey]['Email'] = $_POST['Email'][$sKey];
		$_SESSION['deelnemers'][$sKey]['Gebruikersnaam'] = $_POST['Gebruikersnaam'][$sKey];
		$_SESSION['deelnemers'][$sKey]['Wachtwoord'] = $_POST['Wachtwoord'][$sKey];
		$_SESSION['deelnemers'][$sKey]['Level'] = $_POST['Level'][$sKey];
		$_SESSION['deelnemers'][$sKey]['Login_status'] = $_POST['Login_status'][$sKey];
		$_SESSION['deelnemers'][$sKey]['Activatiecode'] = $_POST['Activatiecode'][$sKey];
		$_SESSION['deelnemers'][$sKey]['Resetcode'] = $_POST['Resetcode'][$sKey];
		$_SESSION['deelnemers'][$sKey]['Betaald'] = '0';
		$_SESSION['deelnemers'][$sKey]['HasPrognoses'] = $_POST['HasPrognoses'][$sKey];
		}
 	foreach($_POST['Betaald'] as $sKey) {   // niet wat gewijzigd (aan/uitgevinkt) is maar alle die aangevinkt 
	 										// staan komen in deze array te staan! dus die moeten allemaal '1' worden, rest '0'
		$_SESSION['deelnemers'][$sKey]['Betaald'] = '1';
 	 	}
	}
$message_lines = "";

if (isset($_POST["save"])) {

	// opslaan in de database
	include("includes/verbinding.inc.php");
	$result = mysql_query("START TRANSACTION;");
	foreach($_SESSION['deelnemers'] as $sKey => $aDnWaardes) {
		$sql = "UPDATE `deelnemer` SET `Betaald` = '".$aDnWaardes['Betaald']."' WHERE `deelnemer`.`DeelnrId` = '".$aDnWaardes['DeelnrId']."';";
		$result = mysql_query($sql);
		}
	$result = mysql_query("COMMIT;");
	$verbreken = mysql_close($verbinding);	// Verbreken van de verbinding met de database
	}

$table_lines = "";
$even = true;		
foreach($_SESSION['deelnemers'] as $sKey => $aDnWaardes) { 
 // bij Betaald[] $sKey meegeven als waarde; alleen wat aangevinkt staat (niet: 'wordt') 
 // komt in de betreffende $_POST['Betaald'] array terecht
	if (!$even) { $even=true; $table_lines .= "<tr class=\"alternate\" style=\"font-weight: normal;\">"; }
	else { $even=false; $table_lines .= "<tr style=\"font-weight: normal;\">"; }
	$table_lines .= "<td><input type=\"checkbox\" name=\"Betaald[]\" value=\"".$sKey."\" ".(($aDnWaardes['Betaald'])?'checked':'')." /></td>";
	$table_lines .= "<td>".(($aDnWaardes['HasPrognoses'])?'j':'<span style="color: red";>n</span>')."</td><td>".$aDnWaardes['DeelnrId']."</td><td>".$aDnWaardes['Voornaam']."</td><td>".$aDnWaardes['Tv']."</td><td>".$aDnWaardes['Achternaam']."</td><td>".$aDnWaardes['Email']."</td><td>".$aDnWaardes['Gebruikersnaam']."</td><td>".(($aDnWaardes['Wachtwoord'])?'j':'n')."</td><td>".$aDnWaardes['Level']."</td><td>".$aDnWaardes['Login_status']."</td><td>".(($aDnWaardes['Activatiecode'])?'j':'n')."</td><td>".(($aDnWaardes['Resetcode'])?'j':'n')."</td>";
	$table_lines .= "<input type=\"hidden\" name=\"HasPrognoses[]\" value=\"".$aDnWaardes['HasPrognoses']."\" />";
	$table_lines .= "<input type=\"hidden\" name=\"DeelnrId[]\" value=\"".$aDnWaardes['DeelnrId']."\" />";
	$table_lines .= "<input type=\"hidden\" name=\"Voornaam[]\" value=\"".$aDnWaardes['Voornaam']."\" />";
	$table_lines .= "<input type=\"hidden\" name=\"Tv[]\" value=\"".$aDnWaardes['Tv']."\" />";
	$table_lines .= "<input type=\"hidden\" name=\"Achternaam[]\" value=\"".$aDnWaardes['Achternaam']."\" />";
	$table_lines .= "<input type=\"hidden\" name=\"Email[]\" value=\"".$aDnWaardes['Email']."\" />";
	$table_lines .= "<input type=\"hidden\" name=\"Gebruikersnaam[]\" value=\"".$aDnWaardes['Gebruikersnaam']."\" />";
	$table_lines .= "<input type=\"hidden\" name=\"Wachtwoord[]\" value=\"".$aDnWaardes['Wachtwoord']."\" />";
	$table_lines .= "<input type=\"hidden\" name=\"Level[]\" value=\"".$aDnWaardes['Level']."\" />";
	$table_lines .= "<input type=\"hidden\" name=\"Login_status[]\" value=\"".$aDnWaardes['Login_status']."\" />";
	$table_lines .= "<input type=\"hidden\" name=\"Activatiecode[]\" value=\"".$aDnWaardes['Activatiecode']."\" />";
	$table_lines .= "<input type=\"hidden\" name=\"Resetcode[]\" value=\"".$aDnWaardes['Resetcode']."\" />";
	$table_lines .= "</td></tr>\n"; 
	}

if (isset($_POST['save'])) {  // $_SESSION['deelnemer'] niet meer nodig
	unset($_SESSION['deelnemer']);	
	}
	
$opslaan = '
			<br />
			<div style="text-align: center;">
				<input type="submit" value="Opslaan" name="save" />
			</div>
	'; 

$html_page = <<<HTMLPAGE
		<!-- breedte in afwijking van css -->
		<div id="paginainhoudbox" style="width: 745px"> 
			<h1>Status controle / Status betaald wijzigen</h1>
			
			{$message_lines}
			<p style="font-size:14px;">Legenda: <br />B: Betaald  P: Prognoses ingevuld   GN: Gebruikersnaam<br />1: Wachtwoord ingevuld   2: Autorisatieniveau  3: Login status  4: Activatiecode ingevuld  5: Resetcode ingevuld</p>
			<form style="width: 730px; " method="post" action="#">  
			<table width="725px" cellpadding="0" cellspacing="0" style="font-size:14px; background: #ECF6FF; border: none; padding: 0px">
			<!-- border en padding in afwijking van table standaard ivm plaatsing binnen form -->
				<tr>
					<td>&nbsp;B</td><td>P</td><td>Id</td><td colspan="3">Naam</td><td>E-mail</td><td>GN</td><td>1</td><td>2</td><td>3</td><td>4</td><td>5</td>
				</tr>
				{$table_lines}
			</table>
			{$opslaan}
			</form>
HTMLPAGE;

echo $html_header.$html_page.$html_footer;