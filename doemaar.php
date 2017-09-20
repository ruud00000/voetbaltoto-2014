<?php
session_start();
if (!isset($_SESSION['loggedin']) or $_SESSION['loggedin']!=1 or $_SESSION['l_level']<2) { header('Location: logout.php'); exit; }

include("includes/page_layout.inc.php");
include("includes/verbinding.inc.php");

$WedstrijdId = mysql_real_escape_string($_POST["wedstrijd"]);
$Uitslag1 = mysql_real_escape_string($_POST["nieuweuitsl1"]);
$Uitslag2 = mysql_real_escape_string($_POST["nieuweuitsl2"]);

// nieuwe Utoto veldwaarde bepalen (let op '==' ipv '='!)
if ($Uitslag1>$Uitslag2) {$Utoto = 2;}
elseif ($Uitslag1==$Uitslag2) {$Utoto = 1;}
else {$Utoto = 0;}

// Nu kunnen we de wedstrijdtabel bijwerken
if (!mysql_query("UPDATE `wedstrijd` SET `Uitslag1` = '".$Uitslag1."', `Uitslag2` = '".$Uitslag2."', `Utoto` = '".$Utoto."' WHERE `wedstrijd`.`WedstrijdId` = '".$WedstrijdId."';")) { $_SESSION['lerror'][] = "Uitslag in wedstrijdtabel zetten is mislukt"; }
// dan veld ScoreToto bijwerken
if (!mysql_query("UPDATE `scores`, `wedstrijd` SET ScoreToto = (SELECT IF(Utoto=ProgToto,5,0)) WHERE scores.WedstrijdId= '".$WedstrijdId."' AND wedstrijd.WedstrijdId= '".$WedstrijdId."'")) { $_SESSION['lerror'][] = "Veld ScoreToto in tabel Score bijwerken voor wedstrijdId ".$WedstrijdId." is mislukt"; }
// dan ScoreUitslag bijwerken
if (!mysql_query("UPDATE `scores`, `wedstrijd` SET ScoreUitslag = (SELECT IF((Uitslag1=Prognose1 AND Uitslag2=Prognose2),10,0)) WHERE scores.WedstrijdId= '".$WedstrijdId."' AND wedstrijd.WedstrijdId= '".$WedstrijdId."'")) { $_SESSION['lerror'][] = "Veld ScoreUitslag in tabel Score bijwerken voor wedstrijdId ".$WedstrijdId." is mislukt"; }
// dan Score bijwerken
if (!mysql_query("UPDATE `scores`, `wedstrijd` SET Score = (SELECT IF((ScoreUitslag>0),ScoreUitslag,ScoreToto)) WHERE scores.WedstrijdId= '".$WedstrijdId."' AND wedstrijd.WedstrijdId= '".$WedstrijdId."'")) { $_SESSION['lerror'][] = "Veld Score in tabel Score bijwerken voor wedstrijdId ".$WedstrijdId." is mislukt"; }
// wedstrijden tellen
$aantal = 0;
$result_totaal = mysql_query("SELECT COUNT(*) FROM `wedstrijd`");
$result_aantal = mysql_query("SELECT COUNT(*) FROM `wedstrijd` WHERE Utoto IS NOT NULL");
if (!$result_aantal or !$result_totaal) { $_SESSION['lerror'][] = "Aantal wedstrijden bepalen mislukt";}
else {
 	$row = mysql_fetch_row($result_aantal);
	$aantal = $row[0];
 	$row = mysql_fetch_row($result_totaal);
	$totaal = $row[0];
	}
// e-mail adressen ophalen
$result = mysql_query("SELECT Email FROM `deelnemer` WHERE Login_status > 0;");
$emails = array();
if (!$result) { $_SESSION['lerror'][] = "E-mailadressen ophalen mislukt"; }
else {
	while ($row = mysql_fetch_row($result)) {
		$emails[] = $row[0];
		}
	}
// query voor mailtje doen
$whereBetaald = ($_SESSION['allowOnbetaald']==1) ? "1" : "t1.Betaald='1'";
$sql = "SELECT @Positie:=@Positie+1 AS Positie, t1.DeelnrId, t1.Voornaam, t1.Tv, t1.Achternaam, t1.Score, t1.Betaald 
FROM (
 SELECT  scores.DeelnrId, Voornaam, Tv, Achternaam, SUM(Score) AS Score, Betaald
 FROM deelnemer, scores
 WHERE scores.DeelnrId = deelnemer.DeelnrId
 GROUP BY scores.DeelnrId
 ORDER BY Score DESC
) t1, (SELECT @Positie:=0) t2 WHERE ".$whereBetaald.";";
$resultaat = mysql_query($sql);
if (!$resultaat) { $_SESSION['lerror'][] = "Ranglijst opvragen uit database is mislukt"; }
// mail tekst ophalen
$result = mysql_query("SELECT MailTussenstandTekst FROM `instellingen`");
if (!result) { $mailTussenstandTekst = ""; }
else { 
	$row = mysql_fetch_row($result);
	$mailTussenstandTekst = $row[0]."<br /><br />";
	}
$verbreken = mysql_close($verbinding);

// mailtje maken
if(empty($_SESSION['lerror'])){
	$bcc = "";
	foreach($emails as $sKey => $sValue) {
		$bcc .= $sValue.", ";
		}
	$bcc = (!empty($bcc)) ? substr_replace($bcc, '', strlen($bcc)-2, 2) : "";
	$to = "Deelnemers@EK-toto-".$instelling.".nl"; // tijdelijk/test
	$wedstrijden = ($aantal==1) ? " wedstrijd" : " wedstrijden";
	$subject = ($aantal<$totaal) ? "Tussenstand ".$title." - ".$year." na ".$aantal.$wedstrijden : "Eindstand ".$year;

	$table_lines = "";
	$even = true;
	while ($row = mysql_fetch_row($resultaat) ) {
		if (!$even) { $even=true; $table_lines .= "<tr style=\"background: #F0F0F0;\">"; }
		else { $even=false; $table_lines .= "<tr style=\"\">"; }
		$table_lines .= "<td>".$row[0]."</td><td>(".$row[1].")</td><td>".$row[2]."</td><td>".$row[3]."</td><td>".$row[4]."</td><td>".$row[5]."</td></tr>\r\n";
	}		

$message = <<<MESSAGE
	<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html;charset=ISO-8859-1" />
	</head>
	<body>
		<!-- smallere breedte in afwijking van css -->
		<div id="paginainhoudbox" style="width: 500px;"> 
			<h2>{$subject}</h2>
			<br />
			{$mailTussenstandTekst}
			<table width="400px" border="0" cellpadding="0" cellspacing="0" style="background: #ECF6FF;">
				<colgroup span="4" style="padding-left: 10px;"></colgroup><colgroup style="padding-left: 10px; text-align: center;"></colgroup>
				<tr style="font-weight:bold;">
					<td>Rang</td><td>Deelnemer</td><td>Score</td>
				</tr>
				{$table_lines}
			</table>
		</div>
	</body>
	</html>
MESSAGE;
	
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= 'From: '.$from_text.' '.$year.' <'.$from.'>' . "\r\n";
	$headers .= 'Bcc: '.$bcc. "\r\n";
	
	// Mail it
	mail($to, $subject, $message, $headers);
 
    $_SESSION['message'] = "<p>Geactualiseerde Toto tussenstand is gemaild.</p>\n";
	}

// Terug naar score.php
$host  = $_SERVER['HTTP_HOST'];
$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$extra = 'score.php';
header("Location: http://$host$uri/$extra");	
exit;