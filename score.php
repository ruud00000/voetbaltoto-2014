<?php
session_start();
if (!isset($_SESSION['loggedin']) or $_SESSION['loggedin']!=1 or $_SESSION['l_level']<2) { header('Location: logout.php'); exit; }

include("includes/page_layout.inc.php");
include("includes/verbinding.inc.php");

$resultaat = mysql_query("SELECT WedstrijdId, Dag, Datum, Tijd, Team1, Team2, Uitslag1, Uitslag2, Utoto FROM `wedstrijd` ORDER BY Datum, Tijd, WedstrijdId");
$verbreken = mysql_close($verbinding);


if(!empty($_SESSION['lerror'])){
   if(is_array($_SESSION['lerror'])){
        foreach($_SESSION['lerror'] as $value){
            $message_lines .= "<p style=\"color:red\">".$value."</p>\n";
        }
   }
   $_SESSION['lerror'] = NULL;
}
if (!empty($_SESSION['message'])) { 
 	$message_lines = $_SESSION['message']; 
 	$_SESSION['message'] = NULL;
	}
else { $message_lines = ""; }

$table_lines = "";
$even = true;
while ($row = mysql_fetch_array($resultaat) ) {
	if (!$even) { $even=true; $table_lines .= "<tr class=\"alternate\" style=\"font-weight: normal;\">"; }
	else { $even=false; $table_lines .= "<tr style=\"font-weight: normal;\">"; }

	$table_lines .= "<td><a href=\"wijzig.php?wedstrijd=".$row['WedstrijdId']."&uitsl1=".$row['Uitslag1']."&uitsl2=".$row['Uitslag2']."\" >Wijzig</a></td><td>".$row['WedstrijdId']."</td><td>".$row['Dag']."</td><td>".$row['Datum']."</td><td>".$row['Tijd']."</td><td>".$row['Team1']."</td><td>".$row['Team2']."</td><td>".$row['Uitslag1']."</td><td>".$row['Uitslag2']."</td></tr>";
}

$html_page = <<<HTMLPAGE
		<!-- breedte in afwijking van css -->
		<div id="paginainhoudbox" style="width: 700px;"> 
			<h1>Uitslag toevoegen of wijzigen</h1>
			
			{$message_lines}
			<table width="700px" border="0" cellpadding="0" cellspacing="0" style="background: #ECF6FF;">
				<tr>
					<td></td><td>Id</td><td colspan="3">Datum</td><td colspan="2">Wedstrijd</td><td colspan="2">Uitslag</td>
				</tr>
				{$table_lines}
			</table>
HTMLPAGE;

echo $html_header.$html_page.$html_footer;