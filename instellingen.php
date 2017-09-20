<?php
session_start();
if (!isset($_SESSION['loggedin']) or $_SESSION['loggedin']!=1 or $_SESSION['l_level']!=2) { header('Location: logout.php'); exit; }
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

include("includes/verbinding.inc.php");

$result = mysql_query("SELECT AllowPrognoses, MailTussenstandTekst, AllowOnbetaald FROM `instellingen`");
$verbreken = mysql_close($verbinding);
$row = mysql_fetch_array($result);
$allowPrognoses = $row['AllowPrognoses'];
$allowProSelected = ($allowPrognoses==1) ? ' selected="selected"' : '';
$notallowProSelected = ($allowPrognoses==0) ? ' selected="selected"' : ''; 
$mailTussenstandTekst = $row['MailTussenstandTekst'];
$allowOnbetaald = $row['AllowOnbetaald'];
$allowOnbSelected = ($allowOnbetaald==1) ? ' selected="selected"' : '';
$notallowOnbSelected = ($allowOnbetaald==0) ? ' selected="selected"' : ''; 


if (isset($_POST['save']) ) {  // er moet wat opgeslagen worden

 	include("includes/verbinding.inc.php");

	$allowPrognoses = $_POST['allowPrognoses'];
 	$mailTussenstandTekst = $_POST['mailTussenstandTekst'];
	$allowOnbetaald = $_POST['allowOnbetaald'];
	
 	if (!mysql_query("UPDATE `instellingen` SET AllowPrognoses='".$allowPrognoses."', MailTussenstandTekst='".$mailTussenstandTekst."', AllowOnbetaald='".$allowOnbetaald."' ;")) {
		$_SESSION['lerror'][] = "Wijzigen van instelling voor AllowPrognoses mislukt";
		} 
	else {
		$allowProSelected = ($allowPrognoses==1) ? ' selected="selected"' : '';
		$notallowProSelected = ($allowPrognoses==0) ? ' selected="selected"' : ''; 
		$_SESSION['allowPrognoses'] = $allowPrognoses;
	 	unset($_POST['allowPrognoses']);
		$allowOnbSelected = ($allowOnbetaald==1) ? ' selected="selected"' : '';
		$notallowOnbSelected = ($allowOnbetaald==0) ? ' selected="selected"' : ''; 
		$_SESSION['allowOnbetaald'] = $allowOnbetaald;
	 	unset($_POST['allowOnbetaald']);
	 	$message_lines .= "<p>Wijzigingen zijn opgeslagen</p>\n";
		}
	}

$html_page = <<<HTMLPAGE
		<!-- breedte in afwijking van css -->
		<div id="paginainhoudbox" style="width: 745px"> 
			<h1>Instellingen</h1>
			{$message_lines}
			<form style="width: 725px; " method="post" action="#">
				<div style="width: 705px; padding-top: 15px;">
					<div class="schermregel">
				        <label class="label" style="width: 300px" for="allowPrognoses">Prognoses toevoegen en wijzigen</label>
						<select name="allowPrognoses">
						<option value="1"{$allowProSelected}>Toegestaan</option>
						<option value="0"{$notallowProSelected}>Niet toegestaan</option>
						</select>
					</div>	
					<div style="padding-top: 15px;" >
				        <label style="float: left; width: 300px;" for="mailTussenstandTekst">Tekst in tussenstand e-mail</label>
				        <textarea rows="4" name="mailTussenstandTekst" style="width: 350px;">{$mailTussenstandTekst}</textarea>
					</div>	
					<div class="schermregel">
				        <label class="label" style="width: 300px" for="allowOnbetaald">Deelnemers die niet betaald hebben</label>
						<select name="allowOnbetaald">
						<option value="1"{$allowOnbSelected}>Tonen in statistieken</option>
						<option value="0"{$notallowOnbSelected}>Niet tonen in statistieken</option>
						</select>
					</div>	
					
					<center>	
					<div style="padding-top: 15px;">
					    <input style="margin-left: 15px; width: 80px" type="submit" value="Opslaan" name="save"/>
				    </div>
				    </center>
				</div>	
			</form>

HTMLPAGE;

echo $html_header.$html_page.$html_footer;