<?php
session_start();
if (!isset($_SESSION['loggedin']) or $_SESSION['loggedin']!=1) { header('Location: logout.php'); exit; }
include "includes/config.inc.php";

if ($_SESSION['l_level']==2) {
	// if (!isset($_SESSION['deelnemer'])) { $_SESSION['deelnemer'] = $_POST["deelnemer"]; }
	$_SESSION['deelnemer'] = $_POST["deelnemer"]; // dit levert lege $_SESSION['deelnemer'] + foutmelding op bij tonen scherm na opslaan door 
	// iemand met beheerdersrechten...
	}
else {
	$_SESSION['deelnemer'] = $_SESSION["l_deelnrId"];
	}
$deelnemer = $_SESSION['deelnemer'];

// waarden ophalen uit database
include_once "includes/page_layout.inc.php";

if (!isset($_SESSION['aPrognoses_old'])) { $_SESSION['aPrognoses_old'] = array(); } 
if (!isset($_SESSION['aPrognoses_new'])) { $_SESSION['aPrognoses_new'] = array(); } 
if (!isset($aPrognoses)) {
	include("includes/verbinding.inc.php");
	$sql = "SELECT scores.WedstrijdId, ScoreId, Dag, Datum, Tijd, Team1, Team2, IF(ISNULL(Prognose1),\"\",Prognose1) AS Prognose1, IF(ISNULL(Prognose2),\"\",Prognose2) AS Prognose2 
	FROM `scores` 
	LEFT OUTER JOIN `wedstrijd` 
	ON wedstrijd.WedstrijdId=scores.WedstrijdId 
	WHERE DeelnrId=".$deelnemer." 
	ORDER BY Datum, Tijd, scores.WedstrijdId";
	$prognoses = mysql_query($sql);
	$verbreken = mysql_close($verbinding);	// Verbreken van de verbinding met de database
	$aPrognoses[] = array();
	while ($row = mysql_fetch_array($prognoses) ) {
		$aPrognoses[$row[1]]['wedstrijdId'] = $row['WedstrijdId'];
		$aPrognoses[$row[1]]['dag'] = $row['Dag'];
		$aPrognoses[$row[1]]['datum'] = $row['Datum'];
		$aPrognoses[$row[1]]['tijd'] = $row['Tijd'];
		$aPrognoses[$row[1]]['team1'] = $row['Team1'];
		$aPrognoses[$row[1]]['team2'] = $row['Team2'];
		$aPrognoses[$row[1]]['prognose1'] = $row['Prognose1'];
		$aPrognoses[$row[1]]['prognose2'] = $row['Prognose2'];
		}
	}
// fetch old or posted values into arrays (sessionvariables)
foreach($aPrognoses as $sKey => $sValue) {
	$_SESSION['aPrognoses_old'][$sKey] = $sValue;
	}
if (!isset($_POST["save"])) { // aPrognoses_new initieren om later alleen de GEWIJZIGDE velden te hoeven bijwerken
	foreach($aPrognoses as $sKey => $sValue) {
		$_SESSION['aPrognoses_new'][$sKey] = $sValue;
		}
	} 
else { // bijwerken van de velden prognose1 en prognose2 in aPrognose_new
 	foreach($_POST['prognose1'] as $sKey => $sProg1) {
 	 	$scorenr = $_POST['scoreId'][$sKey];
		$sProg2 = $_POST['prognose2'][$sKey];
		$_SESSION['aPrognoses_new'][$scorenr]['prognose1'] = $sProg1;
		$_SESSION['aPrognoses_new'][$scorenr]['prognose2'] = $sProg2;
		$aPrognoses[$scorenr]['prognose1'] = $sProg1;
		$aPrognoses[$scorenr]['prognose2'] = $sProg2;
		}
	}

// validate in case something was posted
// echo '<pre>$_POST: '.print_r($_POST).'</pre>';
$message_lines = "";
if (isset($_POST["save"])) {
    // Arrays declareren voor opslag van fouten en data
    $aErrors = array();
	$error_validate = false;
	$error_database = false;

    // Alle formuliervelden doorlopen en array $aErrors vullen
    $i = 0;
    $j = 0;
 	$options = array(
	    'options' => array(
	        'min_range' => 0,
	        'max_range' => 20
	    ),
	);

	foreach($_POST['prognose1'] as $sProg1) {
		$sProg2 = $_POST['prognose2'][$i];
	 	if ((!filter_var($sProg1, FILTER_VALIDATE_INT, $options) and $sProg1!='0') or 
		    (!filter_var($sProg2, FILTER_VALIDATE_INT, $options) and $sProg2!='0')) {
			if (empty($aErrors)) {
				$aErrors[$j] = 'Je hebt de prognose van wedstrijd '.$_POST['wedstrijdId'][$i];	
				}
			else {
				$aErrors[$j] .= ', '.$_POST['wedstrijdId'][$i];
				}
			}
		$i++;
		}
	if (!empty($aErrors)) {
		$aErrors[$j] .= ' niet (correct) ingevuld';
        foreach($aErrors as $sError) {
         	$message_lines .= "<p style=\"color:red\">".$sError."</p>\n";
        	}
        unset($aErrors);
        $error_validate = true;
		} 
		
	if (!$error_validate) {
		// store aPrognoses_new values in the database
		include("includes/verbinding.inc.php");
		$sql = "START TRANSACTION;";
		$aResult = array();
		$aResult[] = mysql_query($sql);
		foreach($aPrognoses as $iScoreId => $aPrognose) {
		 	if ($iScoreId > 0) {
				if ($aPrognose['prognose1']>$aPrognose['prognose2']) {$progtoto=2;}
				elseif ($aPrognose['prognose1']==$aPrognose['prognose2']) {$progtoto=1;}
				else {$progtoto=0;}
		 	 	
				$sql = "UPDATE `scores` SET `Prognose1` = '".mysql_real_escape_string($aPrognose['prognose1'])."', `Prognose2` = '".mysql_real_escape_string($aPrognose['prognose2'])."', `ProgToto` = '".$progtoto."' WHERE `scores`.`ScoreId` = '".$iScoreId."';";
				$aResult[] = mysql_query($sql);
				}
			}
		$sql = "COMMIT;";
		$aResult[] = mysql_query($sql);
		$verbreken = mysql_close($verbinding);	// Verbreken van de verbinding met de database
		$aErrors_tmp = array();
		foreach($aResult as $sKey => $sValue) {
		 	if ($sValue == '0') {
			$aErrors_tmp[$j] = (empty($aErrors)) ? 'Opslaan in de database is mislukt op query regelnummer '.$sKey : ', '.$sKey.'\nProbeer het nogmaals';
				}
			}

		if (!empty($aErrors_tmp)) {
			$aErrors[$j] = $aErrors_tmp[$j];
	        foreach($aErrors as $sError) {
	         	$message_lines .= "<p style=\"color:red\">".$sError."</p>\n";
	        	}
	        // inform the webadmin of error writing to database
			$subject = 'Waarschuwing: toevoegen aan database mislukt voor deelnemer '. $deelnemer;
			$message = 'Een poging om prognoses aan de database toe te voegen is mislukt'.'<br /><br />';
			$message .= 'Deelnemer: '.$deelnemer.'<br />';
			$message .= 'Datum en tijd: '.date('d-m-Y').' om '.date('H:i').' uur'.'<br /><br />';
			$message .= 'Waarden voor update: '.'<br />';
			$message .= '<table width="725px" border="0" cellpadding="0" cellspacing="0" style="background: #ECF6FF;"><tr>';
			$message .= '<td colspan="4">&nbsp;&nbsp;Datum</td><td colspan="2">&nbsp;&nbsp;Wedstrijd</td><td colspan="2">&nbsp;&nbsp;Prognose</td></tr>';
			foreach($_SESSION['aPrognoses_old'] as $sKey => $aValue) {
				if ($sKey!='0') {$message .= '<tr><td>&nbsp;&nbsp;'.$aValue['wedstrijdId'].'</td><td>&nbsp;&nbsp;'.$aValue['dag'].'</td><td>&nbsp;&nbsp;'.$aValue['datum'].'</td><td>&nbsp;&nbsp;'.$aValue['tijd'].'</td><td>&nbsp;&nbsp;'.$aValue['team1'].'</td><td>&nbsp;&nbsp;'.$aValue['team2'].'</td><td>&nbsp;&nbsp;'.$aValue['prognose1'].'</td><td>&nbsp;&nbsp;'.$aValue['prognose2'].'</td></tr>'; }
				}
			$message .= '</table><br />'.'Ingetypte waarden: '.'<br />';
			$message .= '<table width="725px" border="0" cellpadding="0" cellspacing="0" style="background: #ECF6FF;"><tr>';
			$message .= '<td colspan="4">&nbsp;&nbsp;Datum</td><td colspan="2">&nbsp;&nbsp;Wedstrijd</td><td colspan="2">&nbsp;&nbsp;Prognose</td></tr>';
			foreach($_SESSION['aPrognoses_new'] as $sKey => $aValue) {
				if ($sKey!='0') {$message .= '<tr><td>&nbsp;&nbsp;'.$aValue['wedstrijdId'].'</td><td>&nbsp;&nbsp;'.$aValue['dag'].'</td><td>&nbsp;&nbsp;'.$aValue['datum'].'</td><td>&nbsp;&nbsp;'.$aValue['tijd'].'</td><td>&nbsp;&nbsp;'.$aValue['team1'].'</td><td>&nbsp;&nbsp;'.$aValue['team2'].'</td><td>&nbsp;&nbsp;'.$aValue['prognose1'].'</td><td>&nbsp;&nbsp;'.$aValue['prognose2'].'</td></tr>'; }
				}
			$message .= '</table><br />';
			
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			$headers .= 'From: '.$from_text.' '.$year.' <'.$from.'>' . "\r\n";
	
	   		mail($webadmin, $subject, $message, $headers);
	   		$error_database = true;
			}
        unset($aErrors_tmp);
		}

	if (!$error_validate and !$error_database) {
        $message_lines = "<p>Jouw prognoses zijn opgeslagen. Je ontvangt binnen 15 minuten ter bevestiging een e-mail met de door jou ingevulde scores.";
		$message_lines .= "<br />Bewaar deze e-mail totdat de competitie voorbij is, als  reservekopie.</p>";

		// send e-mail with a copy of the stored prognoses
    	$gebruikersnaam = $_SESSION['l_gebruikersnaam'];
		$voornaam = $_SESSION['l_voornaam'];
    	$tv = (empty($_SESSION['l_tv'])) ? "" : " ".$_SESSION['l_tv'];
    	$achternaam = $_SESSION['l_achternaam'];
    	$email = $_SESSION['l_email'];
		$to = $voornaam.$tv." ".$achternaam." <".$email.">";
		$bcc = $bcc1.', '.$bcc2;  // multiple recipients werkt niet (kennelijk php bug) dus forward vanaf mijn schaersvoorde account...
		$subject = "Prognoses ontvangen van ".$voornaam.$tv." ".$achternaam." (".$gebruikersnaam.")";
		
		$message = '
		<html>
		<head>
		  <title>Prognoses ontvangen van '.$voornaam.$tv.' '.$achternaam.' ['.$gebruikersnaam.'] ('.$deelnemer.')</title>
		</head>
		<body>
			<p>De volgende prognoses werden van '.$voornaam.$tv.' '.$achternaam.' ['.$gebruikersnaam.'] ('.$deelnemer.') ontvangen op '.date('d-m-Y').' om '.date('H:i').' uur:</p>
			<table width="725px" border="0" cellpadding="0" cellspacing="0" style="background: #ECF6FF;">
				<tr>
					<td colspan="4">&nbsp;&nbsp;Datum</td><td colspan="2">&nbsp;&nbsp;Wedstrijd</td><td colspan="2">&nbsp;&nbsp;Prognose</td>
				</tr>';
		foreach($_SESSION['aPrognoses_new'] as $sKey => $aValue) {
			if ($sKey!='0') {$message .= '<tr><td>&nbsp;&nbsp;'.$aValue['wedstrijdId'].'</td><td>&nbsp;&nbsp;'.$aValue['dag'].'</td><td>&nbsp;&nbsp;'.$aValue['datum'].'</td><td>&nbsp;&nbsp;'.$aValue['tijd'].'</td><td>&nbsp;&nbsp;'.$aValue['team1'].'</td><td>&nbsp;&nbsp;'.$aValue['team2'].'</td><td>&nbsp;&nbsp;'.$aValue['prognose1'].'</td><td>&nbsp;&nbsp;'.$aValue['prognose2'].'</td></tr>'; }
			}

		$message .= 
				'
			</table>
		</body>
		</html>
		';
		
		// To send HTML mail, the Content-type header must be set
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		
		// Additional headers
		// $headers .= 'To: '.$to. "\r\n";
		$headers .= 'From: '.$from_text.' '.$year.' <'.$from.'>' . "\r\n";
		$headers .= 'Bcc: '.$bcc. "\r\n";
		
		// Mail it
		mail($to, $subject, $message, $headers);
		}
	}

// fill table contents into variable
$table_lines = "";
$even = true;		
foreach($aPrognoses as $iScoreId => $aPrognose) {
 	if ($iScoreId > 0) {
		if (!$even) { $even=true; $table_lines .= "<tr class=\"alternate\" style=\"font-weight: normal;\">"; }
		else { $even=false; $table_lines .= "<tr style=\"font-weight: normal;\">"; }
		$table_lines .= "<td>&nbsp;&nbsp;".$aPrognose['dag']."</td><td>&nbsp;&nbsp;".$aPrognose['datum']."</td><td>&nbsp;&nbsp;".$aPrognose['team1']."</td><td>&nbsp;&nbsp;".$aPrognose['team2']."</td>";
		$table_lines .= "<td>&nbsp;&nbsp;<input type=\"text\" size=\"2\" name=\"prognose1[]\" value=\"".$aPrognose['prognose1']."\" /></td>";
		$table_lines .= "<td>-&nbsp;&nbsp;<input type=\"text\" size=\"2\" name=\"prognose2[]\" value=\"".$aPrognose['prognose2']."\" />";
		$table_lines .= "<input type=\"hidden\" id=\"scoreId\" name=\"scoreId[]\" value=\"".$iScoreId."\" />";
		$table_lines .= "<input type=\"hidden\" id=\"wedstrijdId\" name=\"wedstrijdId[]\" value=\"".$aPrognose['wedstrijdId']."\" /></td>";
		$table_lines .= "</tr>\n"; 
		}
	}
$_SESSION['table_lines'] = $table_lines;
unset($iScoreId);

if ($_SESSION['allowPrognoses']==1) {
	$opslaan = '
			<br />
			<div style="text-align: center;">
				<input type="submit" value="Opslaan" name="save" />
			</div>
	'; }
else {
 	$opslaan = '';
	$message_lines .= "<p>De inschrijving is gesloten. Er kunnen geen prognoses meer doorgegeven worden.</p>\n";
	}

$html_page = <<<HTMLPAGE
		<!-- breedte in afwijking van css -->
		<div id="paginainhoudbox" style="width: 745px"> 
			<h1>Prognose wijzigen</h1>
			
			{$message_lines}
			<form style="width: 730px; " method="post" action="#">  
			<table width="725px" cellpadding="0" cellspacing="0" style="background: #ECF6FF; border: none; padding: 0px">
			<!-- border en padding in afwijking van table standaard ivm plaatsing binnen form -->
				<tr>
					<td colspan="2">&nbsp;&nbsp;Datum</td><td colspan="2">&nbsp;&nbsp;Wedstrijd</td><td colspan="2">&nbsp;&nbsp;Prognose</td>
				</tr>
				{$table_lines}
			</table>
			{$opslaan}
			</form>
HTMLPAGE;


echo $html_header.$html_page.$html_footer;

if (isset($_POST["save"]) and !$error_validate and !$error_database) {
 	unset($_SESSION['aPrognoses_old']);
 	unset($_SESSION['aPrognoses_new']);
 	unset($_SESSION['table_lines']);
 	// unset($_SESSION['deelnemer']);
	}