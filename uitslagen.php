<?php
session_start();
include("includes/page_layout.inc.php");
include("includes/verbinding.inc.php");

/* vervangen door directe query zodat DROP niet nodig is en geen DROP rechten aan public database user toegekend hoeven worden

// tijdelijke tabel met scores maken (eerste bestaande weggooien als die nog bestaat)
// let op smallint gebruiken voor getallen, waarde kan groter dan 127 zijn
$sql = "DROP TABLE IF EXISTS Scoretabel;";
$resultaat = mysql_query($sql);
$sql = "CREATE TABLE IF NOT EXISTS Scoretabel (
  Positie tinyint(4) NOT NULL AUTO_INCREMENT,  
  DeelnrId smallint(6) DEFAULT NULL,
  Voornaam text(25) DEFAULT NULL,
  tv text(10) DEFAULT NULL,
  Achternaam text(25) DEFAULT NULL,
  ProgToto smallint(4) DEFAULT NULL,
  Toto smallint(4) DEFAULT NULL,
  Uitslag smallint(4) DEFAULT NULL,
  Score smallint(4) DEFAULT NULL,
  PRIMARY KEY (Positie)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
$resultaat = mysql_query($sql);

// SQL-tabel vullen vanuit de tabellen scores en deelnemer
$sql = "INSERT INTO Scoretabel (DeelnrId, Voornaam, tv, Achternaam, Toto, Uitslag, Score)
(SELECT scores.deelnrId, voornaam, tv, achternaam, SUM(scoretoto) AS Toto, SUM(scoreuitslag) AS Uitslag, SUM(score) AS Score 
FROM `wedstrijd`, `deelnemer`, `scores` 
WHERE scores.DeelnrId=deelnemer.DeelnrId AND scores.WedstrijdId=wedstrijd.WedstrijdId  AND NOT ISNULL(uitslag1) 
GROUP BY scores.DeelnrId 
ORDER BY Score DESC)";
$resultaat = mysql_query($sql);

// data opvragen uit tijdelijke tabel met scores, gesorteerd op positie in de ranglijst
$sql = "SELECT Positie, DeelnrId, Voornaam, tv, Achternaam, Toto, Uitslag, Score 
FROM `Scoretabel` 
ORDER BY Positie";	
*/

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
$verbreken = mysql_close($verbinding);

$message_lines = "";
$table_lines = "";
$even = true;
while ($row = mysql_fetch_row($resultaat) ) {
	if (!$even) { $even=true; $table_lines .= "<tr class=\"alternate\" style=\"font-weight: normal;\">"; }
	else { $even=false; $table_lines .= "<tr style=\"font-weight: normal;\">"; }
	$table_lines .= "<td>".$row[0]."</td><td class=\"padded\">(".$row[1].")</td><td class=\"padded\">".$row[2]."</td><td class=\"padded\">".$row[3]."</td><td class=\"padded\">".$row[4]."</td><td class=\"padded-c\">".$row[5]."</td></tr>\n";
}	
$wedstrijden = ($aantal==1) ? " wedstrijd" : " wedstrijden";
$h1 = ($aantal<$totaal) ? "Tussenstand na ".$aantal.$wedstrijden : "Eindstand ".$year;
	
$html_page = <<<HTMLPAGE
		<!-- smallere breedte in afwijking van css -->
		<div id="paginainhoudbox" style="width: 500px;"> 
			<h1>{$h1}</h1>
			
			{$message_lines}
			<table width="400px" border="0" cellpadding="0" cellspacing="0" style="background: #ECF6FF;">
				<tr>
					<td>Rang</td><td class="padded" colspan="4">Deelnemer</td><td class="padded">Score</td>
				</tr>
				{$table_lines}
			</table>
HTMLPAGE;

echo $html_header.$html_page.$html_footer;