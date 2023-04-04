<?php
// PDF Pages
//
// by Vangelis Zacharioudakis (http://github.com/sugarv)

// include files
use setasign\Fpdi\Fpdi;
require_once('config.php');
require_once('./vendor/autoload.php');

header('Content-type: text/html; charset=utf8'); 
?>
<html>
    <head><title><?= TITLE ?></title></head>
<body>
<link href='http://fonts.googleapis.com/css?family=Roboto+Condensed&subset=greek,latin' rel='stylesheet' type='text/css'>
<LINK href="style.css" rel="stylesheet" type="text/css">
<?php

if (!isset($_POST['afm']))
{
    ?>
	<center>
	<br>
	<h2><?= APP_DNSI ?></h2>
		<h3><?= TITLE ?></h3>
		<h4><?= SUB_TITLE ?></h4>
		* Οι βεβαιώσεις αποδοχών των Αναπληρωτών ΕΣΠΑ και ΠΔΕ έχουν σταλεί στους εκπαιδευτικούς με μήνυμα ηλεκτρονική αλληλογραφίας (mail) μέσω της εφαρμογής του ΥΠΑΙΘ.
		<br>
		<br>
	<?php
	echo "<form name='login' method='post' id='login' action='' autocomplete='off'>
	<table>
	<tr><td><label for='afm'>ΑΦΜ Εκπαιδευτικού</label></td>
	<td><input name='afm' id='afm' type='text' required></td></tr>
	<tr><td><label for='amka'>ΑΜKA Εκπαιδευτικού</label></td>
	<td><input name='amka' id='amka' type='text' required></td></tr>
	<tr><td colspan=2><br><center><input name='submit' id='submit' value='Λήψη' type='submit'></center></td></tr></table>
	</form>";
	echo "<br><br>";
	echo "<small>(c) Δ/νση Δ.Ε. Ηρακλείου - Τμήμα Πληροφορικής και Νέων Τεχνολογιών</small>";
	echo "</center>";
}
else
{
	$emptyForm = 0;
	
	// check if files exist...
	if (!file_exists('data/'.EMPLOYEE_FILENAME))
		die('Σφάλμα: Το αρχείο υπαλλήλων δεν βρέθηκε.');
	elseif (!file_exists('data/'.VEV_FILENAME))
		die('Σφάλμα: Το αρχείο βεβαιώσεων δεν βρέθηκε.');
	
	if (!strlen($_POST['amka']) || !strlen($_POST['afm'])){
		$emptyForm = 1;
	}
	else {
		$afm = $_POST['afm'];
		$amka = $_POST['amka'];
		
		// use ParseCSV to find employee in csv file
		$csvFile = 'data/' . EMPLOYEE_FILENAME;

		$csv = new \ParseCsv\Csv();
		$csv->delimiter = ";";
		// find employee using AFM & AMKA
		$condition = 'afm is '.$afm.' AND amka is '.$amka;
		$csv->conditions = $condition;
		$csv->parseFile($csvFile);
		$parsed = $csv->data;
	}
    // if employee not found or empty form
	if ((isset($parsed) && !count($parsed)) || $emptyForm)
    {
		echo "H είσοδος με ΑΦΜ: $afm & ΑΜKA: $amka απέτυχε...";
		echo "<br>Δοκιμάστε ξανά με έναν έγκυρο συνδυασμό ΑΦΜ - ΑΜKA";
		echo "<FORM><INPUT Type='button' VALUE='Επιστροφή' onClick='history.go(-1);return true;'></FORM>";
	}
	else
	{
		// get all pages (if more than one)
		foreach($parsed as $rec) {
			$pages[] = $rec['page'];
		}

		//override memory limit
		ini_set('memory_limit', '-1');

		// FPDI lib is used. File must be PDF/A (v.1.4)
		//initiate FPDI
		
		$pdf = new FPDI();
		$fn = "./data/".VEV_FILENAME;
		$pdf->setSourceFile($fn);

		foreach ($pages as $page)
		{
			//import page
			$tplIdx = $pdf->importPage($page);
			$size = $pdf->getTemplateSize($tplIdx);
			$pdf->addPage($size['orientation'], $size);	

			//use the imported page 
			$pdf->useTemplate($tplIdx);
		}
		ob_end_clean();
		// output PDF to user's browser
		$pdf->Output();
	}
}
?>
</center>
</body>
</html>