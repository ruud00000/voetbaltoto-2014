<?php
session_start();
if (!isset($_SESSION['loggedin']) or $_SESSION['loggedin']!=1 or $_SESSION['l_level']<2) { header('Location: logout.php'); exit; }
if (isset($_SESSION['aPrognoses_old'])) unset($_SESSION['aPrognoses_old']);
if (isset($_SESSION['aPrognoses_new'])) unset($_SESSION['aPrognoses_new']);
if (isset($_SESSION['table_lines'])) unset($_SESSION['table_lines']);
if (isset($_SESSION['deelnemer'])) unset($_SESSION['deelnemer']);

$deelnemer = (isset($_POST["deelnemer"])) ? addslashes($_POST["deelnemer"]) : NULL;

include("includes/verbinding.inc.php");
include("includes/page_layout.inc.php");

$deelnemerkeuzelijst = mysql_query("SELECT DeelnrId, Voornaam, Tv, Achternaam, Email FROM `deelnemer` ORDER BY achternaam");
$verbreken = mysql_close($verbinding);	
// deelnemerlijstje vullen voor dropdown veld
$select_lines = "";
while ($row = mysql_fetch_array($deelnemerkeuzelijst) ) {
	$select_lines .= "<option value=\"".$row['DeelnrId']."\">".$row['Achternaam'];
	$select_lines .= ($row['Tv']!="") ? ", ".$row['Tv'] : "";
	$select_lines .= ", ".$row['Voornaam'];
	$select_lines .= " (".$row['DeelnrId'].") </option>\n";
}

$html_page = <<<HTMLPAGE
		<!-- breedte in afwijking van css -->
		<div id="paginainhoudbox" style="width: 745px"> 
			<h1>Prognose wijzigen</h1>
			<!-- invoerveldje laten zien voor selectie deelnemer -->
			<form style="width: 625px; " method="post" action="prognose.php">  
				<div style="width: 605px; padding-top: 15px;">
					<span style="float: left; width: 100px;">Deelnemer :</span>
					<select style="float: left; width: 200px;" name="deelnemer">
						{$select_lines}
					</select>
					<input style="margin-left: 15px;" type="submit" value="Selecteer" name="selectie" />
				</div>	
			</form>
HTMLPAGE;

echo $html_header.$html_page.$html_footer;