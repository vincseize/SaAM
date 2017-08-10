<?php

	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC'] . "/checkConnect.php" );
	require_once ('directories.php');
	require_once ('dates.php');
	require($_SESSION['INSTALL_PATH'] . '/classes/fpdf/fpdf.php');

function pdf_project($ID_project,$titleProj){
	
	$file = $_SESSION['INSTALL_PATH'].'datas/projects/'.$ID_project.'_'.$titleProj.'/'.$ID_project.'_'.$titleProj.'.pdf';

	$fh = fopen($file, 'w');
	//fwrite($file, $doc);	
	fclose($fh);
	chmod($file,0644);	
	
	$pdf = new FPDF();
	$pdf->AddPage();
	$pdf->SetFont('Arial','B',16);
	$pdf->Cell(40,10,'PROJECT : '.$titleProj);
	//$pdf->Output();
	//$doc = $pdf->Output('', 'S');
	
	$pdf->Output($file, 'F');	
	


}
?>
