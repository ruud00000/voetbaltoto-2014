<?php
session_start();

include_once "includes/page_layout.inc.php";
include("includes/verbinding.inc.php");

$whereBetaald = ($_SESSION['allowOnbetaald']==1) ? "1" : "Betaald='1'";
$sql = "SELECT scores.WedstrijdId, Dag, Datum, Team1, Team2, Uitslag1, Uitslag2, SUM(Score) AS Totaal, IFNULL(SVW10.Score10,0) AS `# 10`, IFNULL(SVW5.Score5,0) AS `# 5` 
FROM `scores` 
LEFT OUTER JOIN `wedstrijd` ON scores.WedstrijdId = wedstrijd.WedstrijdId 
LEFT OUTER JOIN (
	SELECT scores.WedstrijdId,  COUNT(Score) AS Score10 
	FROM `scores` 
	LEFT OUTER JOIN `wedstrijd` ON scores.WedstrijdId = wedstrijd.WedstrijdId 
	WHERE Score = 10 
	GROUP BY WedstrijdId 
	ORDER BY Score10 ) AS SVW10 ON SVW10.WedstrijdId = scores.WedstrijdId 
LEFT OUTER JOIN (
	SELECT scores.WedstrijdId,  COUNT(Score) AS Score5 
	FROM `scores` 
	LEFT OUTER JOIN `wedstrijd` ON scores.WedstrijdId = wedstrijd.WedstrijdId 
	WHERE Score = 5 
	GROUP BY WedstrijdId 
	ORDER BY Score5) AS SVW5 ON SVW5.WedstrijdId = scores.WedstrijdId 
LEFT OUTER JOIN `deelnemer` ON scores.DeelnrId = deelnemer.DeelnrId 
WHERE NOT ISNULL(Score) AND ".$whereBetaald." 
GROUP BY WedstrijdId 
ORDER BY Totaal;";
$resultaat = mysql_query($sql);
$verbreken = mysql_close($verbinding);

$message_lines = "";
$table_lines = "";
$even = true;		
while ($row = mysql_fetch_row($resultaat) ) {
	if (!$even) { $even=true; $table_lines .= "<tr class=\"alternate\" style=\"font-weight: normal;\">"; }
	else { $even=false; $table_lines .= "<tr style=\"font-weight: normal;\">"; }
	$table_lines .= "<td>".$row[0]."</td><td>&nbsp;&nbsp;".$row[1]."</td><td>&nbsp;&nbsp;".$row[2]."</td><td>&nbsp;&nbsp;".$row[3]."</td><td>&nbsp;&nbsp;".$row[4]."</td><td class=\"center\">&nbsp;&nbsp;".$row[5]."</td><td class=\"center\">&nbsp;&nbsp;".$row[6]."</td><td class=\"center\">&nbsp;&nbsp;".$row[7]."</td><td class=\"center\">&nbsp;&nbsp;".$row[8]."</td><td class=\"center\">&nbsp;&nbsp;".$row[9]."</td></tr>\n";
	}

$html_page = <<<HTMLPAGE
		<!-- smallere breedte in afwijking van css -->
		<div id="paginainhoudbox" style="width: 720px;"> 
			<h1>Slechtst voorspelde wedstrijden</h1>
			<p style="font-weight: normal;"> Overzicht van de grootste verrassingen (bovenaan) 
				en de grootste inkoppers (onderaan de lijst) van deze toto!<br /><br />
				Per wedstrijd staat ook vermeld het aantal keer dat de juiste uitslag is voorspeld: '#10'
				en het aantal keer dat de juiste toto-uitslag voorspeld is: '#5'<br /><br />
				Het aantal juist voorspelde uitslagen * 10 + het aantal juiste toto-uitslagen * 5 is dus  
				gelijk aan het in de kolom 'Score' (d.i. het totaal van alle behaalde scores) vermelde aantal.<br />
			</p>	
			
			{$message_lines}
			<table width="700px" border="0" cellpadding="0" cellspacing="0" style="background: #ECF6FF;">
				<tr>
					<td>Nr</td><td colspan="2">&nbsp;&nbsp;Datum</td><td colspan="2">&nbsp;&nbsp;Wedstrijd</td><td colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;Uitslag</td><td>&nbsp;&nbsp;Score</td><td>&nbsp;&nbsp;#10</td><td>&nbsp;&nbsp;#5</td>
				</tr>
				{$table_lines}
			</table>
HTMLPAGE;

echo $html_header.$html_page.$html_footer;