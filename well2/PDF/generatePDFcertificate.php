<?php

// initiate FPDI
$pdf = new \setasign\Fpdi\Fpdi();
// add a page
$pdf->AddPage("L");
// set the source file
$pdf->setSourceFile('PDF/cert_of_completion.pdf');
// import page 1
$tplIdx = $pdf->importPage(1);
// use the imported page and place it at position 10,10 with a width of 100 mm
$pdf->useTemplate($tplIdx, -8, 0, 305);

// now write some text above the imported page
$pdf->SetFont('Arial');
$pdf->SetFontSize(24);
$pdf->SetTextColor(0, 0, 0);
$_w     = $pdf->GetPageWidth();
$_h     = $pdf->GetPageHeight();
$txt    = $loggedInUser->firstname . " " . $loggedInUser->lastname . " on " . date("l") . ", " . date("M d Y");
$_wtext = $pdf->GetStringWidth($txt);

$pdf->SetXY(($_w/2)-($_wtext/2), ($_h/2));
$pdf->Write(0, $txt);
$userfolder = $loggedInUser->id . "_" . $loggedInUser->firstname . "_" . $loggedInUser->lastname;
if (!file_exists("PDF/certs/$userfolder")) {
    mkdir("PDF/certs/$userfolder", 0777, true);
}
$filename 	= array();
$filename[] = $loggedInUser->id;
$filename[] = $loggedInUser->firstname;
$filename[] = $loggedInUser->lastname;
$filename[] = $current_year;
$filename ="PDF/certs/$userfolder/".implode("_",$filename).".pdf";
$pdf->Output($filename,'F');