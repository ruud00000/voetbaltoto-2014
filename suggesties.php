<?php
session_start();
if (!isset($_SESSION['loggedin']) or $_SESSION['loggedin']!=1) { header('Location: logout.php'); exit; }
include("includes/page_layout.inc.php");

$message_lines = "";
if(!empty($_SESSION['lerror'])){
   if(is_array($_SESSION['lerror'])){
        foreach($_SESSION['lerror'] as $value){
            $message_lines .= "<p style=\"color:red\">".$value."</p>\n";
        }
   }
   $_SESSION['lerror'] = NULL;
}
$gebruikersnaam = (isset($_SESSION['l_gebruikersnaam'])) ? $_SESSION['l_gebruikersnaam'] : "";
$voornaam = (isset($_SESSION['l_voornaam'])) ? $_SESSION['l_voornaam'] : "";
$tv = (isset($_SESSION['l_tv'])) ? $_SESSION['l_tv'] : "";
$achternaam = (isset($_SESSION['l_achternaam'])) ? $_SESSION['l_achternaam'] : "";
$opmerking = (isset($_SESSION['opmerking'])) ? $_SESSION['opmerking'] : "";

include("includes/verbinding.inc.php");

$resultaat = mysql_query("SELECT IdeeId, Datum, DeelnrId, Gebruikersnaam, Voornaam, Tv, Achternaam, Naam, Opmerking, Status FROM `ideeenbus` ORDER BY IdeeId DESC");
$verbreken = mysql_close($verbinding);
$table_lines = "";
$even = true;		
while ($row = mysql_fetch_array($resultaat) ) {
	if (!$even) { $even=true; $table_lines .= "<tr class=\"alternate\" style=\"font-weight: normal;\">"; }
	else { $even=false; $table_lines .= "<tr style=\"font-weight: normal;\">"; }
	$table_lines .= "<td>".$row['IdeeId']."</td><td style=\"width: 10em;\">".$row['Datum']."</td>";
	$table_lines .= "<td>".$row['Voornaam'];
	$table_lines .= ($row['Tv']!="") ? " ".$row['Tv'] : "";
	$table_lines .= " ".$row['Achternaam'];
	$table_lines .= " (".$row['DeelnrId'].") </td>";
	$table_lines .= "<td style=\"width: 20em;\">".$row['Opmerking']."</td>";
	$table_lines .= "<td>".$row['Status']."</td></tr>\n";
	}

if (isset($_POST['save']) and !empty($_POST['opmerking']) ) {  // er moet wat opgeslagen worden

 	include("includes/verbinding.inc.php");

	$opmerking = filter_var($_POST['opmerking'], FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);	
	$opmerking = filter_var($opmerking, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_ENCODE_HIGH);
	$opmerking = mysql_real_escape_string($opmerking);

 	$gebruikersnaam = $_SESSION['l_gebruikersnaam'];
 	$voornaam = $_SESSION['l_voornaam'];
 	$tv = $_SESSION['l_tv'];
 	$achternaam = $_SESSION['l_achternaam'];
 	$deelnrId = $_SESSION['l_deelnrId'];
 	
	$result = mysql_query("SELECT COUNT(*) FROM `ideeenbus` WHERE `Opmerking`='".$opmerking."';"); 
	$row = mysql_fetch_row($result);
	if ($row[0]>0) {
		header("Refresh:0");
		exit;
	}
 	if (!mysql_query("INSERT INTO `ideeenbus` (Datum, DeelnrId, Gebruikersnaam, Voornaam, Tv, Achternaam, Opmerking) VALUES (CURRENT_TIMESTAMP, '".$deelnrId."', '".$gebruikersnaam."', '".$voornaam."', '".$tv."', '".$achternaam."', '".$opmerking."')")) {
		$_SESSION['lerror'][] = "Opslaan van suggestie mislukt. Probeer het later nog eens of stuur je suggestie s.v.p. per mail";
		} 
	else {
	 	unset($_POST['opmerking']);
		header("Refresh:0");
		exit;
		}
	
	}

if (isset($_POST['nieuw'])) {  // er is op 'nieuw' geklikt dus formulier laten zien
$html_form = <<<HTMLFORM
					<div style="float: left; padding-bottom: 15px;">
				        <label style="width: 130px; float: left;"for="opmerking">Suggestie</label>
				        <textarea style="display: inline;" rows="2" cols="68" name="opmerking"></textarea>
					</div>	
					<center>	
					<div style="padding-bottom: 15px;">
					    <input style="margin-left: 15px; width: 150px" type="submit" value="Opslaan" name="save"/>
				    </div>
				    </center>
HTMLFORM;
	}
else {
$html_form = <<<HTMLFORM
					<center>	
					<div style="padding-bottom: 15px;">
					    <input style="margin-left: 15px; width: 150px" type="submit" value="Nieuwe suggestie" name="nieuw"/>
				    </div>
				    </center>
HTMLFORM;
	
	}

$html_page = <<<HTMLPAGE
		<!-- breedte in afwijking van css -->
		<div id="paginainhoudbox" style="width: 745px"> 
			<h1>Suggesties voor verbetering van deze site</h1>
			{$message_lines}
			<form style="width: 730px; " method="post" action="#">
				<div style="width: 705px; padding-top: 15px;">
					{$html_form}
					<table width="725px" cellpadding="0" cellspacing="0" style="background: #ECF6FF; border: none; padding: 0px">
					<!-- border en padding in afwijking van table standaard ivm plaatsing binnen form -->
						<tr>
							<td></td><td>Datum</td><td>Naam</td><td>Opmerking</td><td>Status</td>
						</tr>
						{$table_lines}
					</table>
				</div>	
			</form>

HTMLPAGE;

unset($_SESSION['opmerking']);

echo $html_header.$html_page.$html_footer;