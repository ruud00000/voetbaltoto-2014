<?php
session_start();
if (!isset($_SESSION['loggedin']) or $_SESSION['loggedin']!=1 or $_SESSION['l_level']<2) { header('Location: logout.php'); exit; }

$deelnemer = (isset($_POST["deelnemer"])) ? addslashes($_POST["deelnemer"]) : NULL;

include("includes/page_layout.inc.php");
include("includes/verbinding.inc.php");

$message_lines = "";
if(!empty($_SESSION['lerror'])){
   if(is_array($_SESSION['lerror'])){
        foreach($_SESSION['lerror'] as $value){
            $message_lines .= "<p style=\"color:red\">".$value."</p>\n";
        }
   }
   $_SESSION['lerror'] = NULL;
}

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
			<h1>Deelnemer</h1>

			{$message_lines}
			<!-- invoerveldje laten zien voor selectie deelnemer -->
			<form style="width: 625px; " method="post" action="deelnemer.php">  
				<div style="width: 605px; padding-top: 15px;">
					<span style="float: left; width: 100px;">Deelnemer :</span>
					<select style="float: left; width: 200px;" name="deelnemer">
						{$select_lines}
					</select>
					<input style="margin-left: 15px;" type="submit" value="Verwijder" name="selectie" />
					<input style="margin-left: 15px;" type="submit" value="Wijzig" name="selectie" />
				</div>	
			</form>
HTMLPAGE;

echo $html_header.$html_page.$html_footer;