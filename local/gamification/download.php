<?php
//============================================================+
// File name   : download.php
// Begin       : 2017-09-21
// Last Update : 2017-09-28
//
// Description : Download for the gamification report for the user 
//               
// Author: Maheshchandra
//
// (c) Copyright:
//               Maheshchandra
//               www.eabyas.in
//============================================================+

/**
 * Creates an download PDF document using TCPDF
 * @abstract Download for the gamification report for the user
 * @author Maheshchandra
 * @since 2017-09-21
 */

// Include the main TCPDF library (search for installation path).
// require_once('tcpdf_include.php');
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/lib/tcpdf/tcpdf.php');

// create new PDF document
global $USER, $OUTPUT, $CFG, $DB;
$count = $DB->count_records('block_gm_events');
$save = optional_param('save', 1, PARAM_INT);


















if($save){
$output = $DB->get_records('user');
    foreach($output as $userdata){
/*check if user_reports directory id available in instance or not
	*if not create one
	*if available skip the step
   */
      $width = 210;
      $height = 210+$count*70;
      // $height = 490;

      $pageLayout = array($width, $height);
      $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, $pageLayout, true, 'UTF-8', false);
      $pdf->setPrintHeader(false);
      $pdf->setPrintFooter(false);
      $pdf->SetMargins(0, 0, 0, true);
      $pdf->SetAutoPageBreak(FALSE, PDF_MARGIN_BOTTOM);
      $right = 190;
      $center = 135;
      $left = 85;
      $right1 = 118;
      $center1 = 65;
      $left1 = 12;
      $fontsize = 14;
      // set document information
      $pdf->SetCreator(PDF_CREATOR);
      $pdf->SetAuthor('Maheshchandra');
      $pdf->SetTitle('Gamification');
      $pdf->SetSubject('TCPDF');
      $pdf->SetKeywords('TCPDF, PDF, example, test, guide');
      $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
      $pdf->setFontSubsetting(true);
      $pdf->SetFont('helveticaB', '', 14, '', true);

      $pdf->AddPage();
      $value = 10;


      $pdf->Image('images/bg-slices/bg and overdrops-01.png', 0,0, 210, 70, 'PNG');
      $pdf->Image('images/bg-slices/bg and overdrops-02.png',0,0,210,70,'PNG');
      $pdf->writeHTMLCell(206,60,130,19, '<h1 style="font-size:28pt; color:white; font-weight:normal">'.fullname($userdata).'</h1>', 0, 0, 0, true, '', true);
      $pdf->writeHTMLCell(206,60,130,30, '<h3 style="color:white; font-weight:normal">'.$userdata->email.'</h1>', 0, 0, 0, true, '', true);



      $data = $DB->get_record('block_gm_overall_site', array('userid' => $userdata->id));
      $userpicture = $OUTPUT->user_picture($userdata, array('size' => 100, 'class'=>'profilepicture', 'link' => false));
      $divheight1 = 70; 

      $pdf->Image('images/slices/slice-07.jpg', 0,$divheight1, 210, 70, 'JPG');




      $pdf->writeHTMLCell(0, 0, '103', $divheight1+23, '<h6 style = "color:#000;font-size:15pt;font-weight:normal">Rank</h6>', 3, 0, false, true, ' ', false);
      $pdf->writeHTMLCell(0, 0, '168', $divheight1+23, '<h6 style = "color:#000;font-size:14pt;font-weight:normal">Badges</h6>', 3, 0, false, true, ' ', false);
      $pdf->writeHTMLCell(0, 0, '103', $divheight1+55, '<h6 style = "color:#000;font-size:14pt;font-weight:normal">Points</h6>', 3, 0, false, true, ' ', false);
      $pdf->writeHTMLCell(0, 0, '168', $divheight1+55, '<h6 style = "color:#000;font-size:14pt;font-weight:normal">Level</h6>', 3, 0, false, true, ' ', false);
      $pdf->writeHTMLCell(0,0,'103',$divheight1+10,'<h1 style="color:#10D5D7; font-size:30pt">'.$data->rank.'</h1>',3, 0, false, true, ' ', false);
      $pdf->writeHTMLCell(0,0,'168',$divheight1+10,'<h1 style="color:#10D5D7; font-size:30pt">'.$data->badgecount.'</h1>',3, 0, false, true, ' ', false);
      $pdf->writeHTMLCell(0,0,'103',$divheight1+40,'<h1 style="color:#10D5D7; font-size:30pt">'.$data->achievedpoints.'</h1>',3, 0, false, true, ' ', false);
      $pdf->writeHTMLCell(0,0,'168',$divheight1+40,'<h1 style="color:#10D5D7; font-size:30pt">'.$data->level.'</h1>',3, 0, false, true, ' ', false);
      $pdf->writeHTMLCell(0,0,'17',$divheight1+25 ,'<span style="border-radius:50%">'.$userpicture.'</span>');





      $divheight2 = 140;
      $data = $DB->get_record('block_gm_overall_site', array('userid' => $userdata->id));
      $dataweek = $DB->get_record('block_gm_weekly_site', array('userid' => $userdata->id));
      $datamonth = $DB->get_record('block_gm_monthly_site', array('userid' => $userdata->id));
      $datamonth->rank = $datamonth->rank ? $datamonth->rank: 'NA';
      $dataweek->rank = $dataweek->rank ? $dataweek->rank : 'NA';
      $data->rank =  $data->rank ? $data->rank :'NA';
      $pdf->Image('images/slices/slice-01.jpg', 0,$divheight2, 210, 70, 'JPG');
      $pdf->writeHTMLCell(0, 0, $left-2, '185', '<h6 style = "color:#fff;font-size:'.$fontsize.'pt;font-weight:normal">Rank</h6>', 3, 0, false, true, ' ', false);
      $pdf->writeHTMLCell(0, 0, $center, '185', '<h6 style = "color:#fff;font-size:'.$fontsize.'pt;font-weight:normal">Week</h6>', 3, 0, false, true, ' ', false);
      $pdf->writeHTMLCell(0, 0, $right-3, '185', '<h6 style = "color:#fff;font-size:'.$fontsize.'pt;font-weight:normal">Month</h6>', 3, 0, false, true, ' ', false);  
      $pdf->writeHTMLCell(0,0,$left-2,'172','<h1 style="color:#fff; font-size:25pt">'.$data->rank.'</h1>');
      $pdf->writeHTMLCell(0,0,$center,'172','<h1 style="color:#fff; font-size:25pt">'.$dataweek->rank.'</h1>');
      $pdf->writeHTMLCell(0,0,$right-2,'172','<h1 style="color:#fff; font-size:25pt">'.$datamonth->rank.'</h1>');
      $pdf->writeHTMLCell(0,0,'90',$divheight2+2,'<h2 style="color:white; font-weight:normal">Site Level</h2>');




      if($count >= 1){
      $divheight3 = 210;
      $data = $DB->get_record('block_gm_overall_cc', array('userid' => $userdata->id));
      $dataweek = $DB->get_record('block_gm_weekly_cc', array('userid' => $userdata->id));
      $datamonth = $DB->get_record('block_gm_monthly_cc', array('userid' => $userdata->id));
      $datamonth->rank = $datamonth->rank ? $datamonth->rank: 'NA';
      $dataweek->rank = $dataweek->rank ? $dataweek->rank : 'NA';
      $data->rank =  $data->rank ? $data->rank :'NA';
      $pdf->Image('images/slices/slice-04.jpg', 0,$divheight3, 210, 70, 'JPG');
      $pdf->writeHTMLCell(0, 0, $left1, '255', '<h6 style = "color:#000;font-size:'.$fontsize.'pt;font-weight:normal">Rank</h6>', 3, 0, false, true, ' ', false);
      $pdf->writeHTMLCell(0, 0, $center1, '255', '<h6 style = "color:#000;font-size:'.$fontsize.'pt;font-weight:normal">Week</h6>', 3, 0, false, true, ' ', false);
      $pdf->writeHTMLCell(0, 0, $right1-2, '255', '<h6 style = "color:#000;font-size:'.$fontsize.'pt;font-weight:normal">Month</h6>', 3, 0, false, true, ' ', false);  
      $pdf->writeHTMLCell(0,0,$left1,'244','<h1 style="color:#000; font-size:25pt">'.$data->rank.'</h1>');
      $pdf->writeHTMLCell(0,0,$center1,'244','<h1 style="color:#000; font-size:25pt">'.$dataweek->rank.'</h1>');
      $pdf->writeHTMLCell(0,0,$right1,'244','<h1 style="color:#000; font-size:25pt">'.$datamonth->rank.'</h1>');
      $pdf->writeHTMLCell(0,0,'80',$divheight3+2,'<h2 style="color:orangered; font-weight:normal">Course Completions</h2>');
      }




      if($count >= 2){
      $data = $DB->get_record('block_gm_overall_certc', array('userid' => $userdata->id));
      $dataweek = $DB->get_record('block_gm_weekly_certc', array('userid' => $userdata->id));
      $datamonth = $DB->get_record('block_gm_monthly_certc', array('userid' => $userdata->id));
      $datamonth->rank = $datamonth->rank ? $datamonth->rank: 'NA';
      $dataweek->rank = $dataweek->rank ? $dataweek->rank : 'NA';
      $data->rank =  $data->rank ? $data->rank :'NA';
      $divheight4 = 280;
      $pdf->Image('images/slices/slice-02.jpg', 0,$divheight4, 210, 70, 'JPG');
      $pdf->writeHTMLCell(0, 0, $left-4, '325', '<h6 style = "color:#fff;font-size:'.$fontsize.'pt;font-weight:normal">Rank</h6>', 3, 0, false, true, ' ', false);
      $pdf->writeHTMLCell(0, 0, $center-1, '325', '<h6 style = "color:#fff;font-size:'.$fontsize.'pt;font-weight:normal">Week</h6>', 3, 0, false, true, ' ', false);
      $pdf->writeHTMLCell(0, 0, $right-4, '325', '<h6 style = "color:#fff;font-size:'.$fontsize.'pt;font-weight:normal">Month</h6>', 3, 0, false, true, ' ', false);  
      $pdf->writeHTMLCell(0,0,$left-2,'314','<h1 style="color:#fff; font-size:25pt">'.$data->rank.'</h1>');
      $pdf->writeHTMLCell(0,0,$center,'314','<h1 style="color:#fff; font-size:25pt">'.$dataweek->rank.'</h1>');
      $pdf->writeHTMLCell(0,0,$right-3,'314','<h1 style="color:#fff; font-size:25pt">'.$datamonth->rank.'</h1>');
      $pdf->writeHTMLCell(0,0,'80',282,'<h2 style="color:white; font-weight:normal">Certification Completions</h2>');
      }




      if($count >= 3){
      $data = $DB->get_record('block_gm_overall_clc', array('userid' => $userdata->id));
      $dataweek = $DB->get_record('block_gm_weekly_clc', array('userid' => $userdata->id));
      $datamonth = $DB->get_record('block_gm_monthly_clc', array('userid' => $userdata->id));
      $datamonth->rank = $datamonth->rank ? $datamonth->rank: 'NA';
      $dataweek->rank = $dataweek->rank ? $dataweek->rank : 'NA';
      $data->rank =  $data->rank ? $data->rank :'NA';
      $divheight4 = 350;
      $pdf->Image('images/slices/slice-05.jpg', 0,$divheight4, 210, 70, 'JPG');
      $pdf->writeHTMLCell(0, 0, $left1+4, '395', '<h6 style = "color:#000;font-size:'.$fontsize.'pt;font-weight:normal">Rank</h6>', 3, 0, false, true, ' ', false);
      $pdf->writeHTMLCell(0, 0, $center1+4, '395', '<h6 style = "color:#000;font-size:'.$fontsize.'pt;font-weight:normal">Week</h6>', 3, 0, false, true, ' ', false);
      $pdf->writeHTMLCell(0, 0, $right1+3, '395', '<h6 style = "color:#000;font-size:'.$fontsize.'pt;font-weight:normal">Month</h6>', 3, 0, false, true, ' ', false);  
      $pdf->writeHTMLCell(0,0,$left1+4,'385','<h1 style="color:#000; font-size:25pt">'.$data->rank.'</h1>');
      $pdf->writeHTMLCell(0,0,$center1+4,'385','<h1 style="color:#000; font-size:25pt">'.$dataweek->rank.'</h1>');
      $pdf->writeHTMLCell(0,0,$right1+4,'385','<h1 style="color:#000; font-size:25pt">'.$datamonth->rank.'</h1>');
      $pdf->writeHTMLCell(0,0,'85',$divheight4+2,'<h2 style="color:orangered; font-weight:normal">Classroom Completions</h2>');
      }





      if($count >= 4){
      $data = $DB->get_record('block_gm_overall_lpc', array('userid' => $userdata->id));
      $dataweek = $DB->get_record('block_gm_weekly_lpc', array('userid' => $userdata->id));
      $datamonth = $DB->get_record('block_gm_monthly_lpc', array('userid' => $userdata->id));
      $datamonth->rank = $datamonth->rank ? $datamonth->rank: 'NA';
      $dataweek->rank = $dataweek->rank ? $dataweek->rank : 'NA';
      $data->rank =  $data->rank ? $data->rank :'NA';
      $divheight5 = 420;
      $pdf->Image('images/slices/slice-03.jpg', 0,$divheight5, 210, 70, 'JPG');
      $pdf->writeHTMLCell(0, 0, $left-4, '467', '<h6 style = "color:#fff;font-size:'.$fontsize.'pt;font-weight:normal">Rank</h6>', 3, 0, false, true, ' ', false);
      $pdf->writeHTMLCell(0, 0, $center-1, '467', '<h6 style = "color:#fff;font-size:'.$fontsize.'pt;font-weight:normal">Week</h6>', 3, 0, false, true, ' ', false);
      $pdf->writeHTMLCell(0, 0, $right-4, '467', '<h6 style = "color:#fff;font-size:'.$fontsize.'pt;font-weight:normal">Month</h6>', 3, 0, false, true, ' ', false);  
      $pdf->writeHTMLCell(0,0,$left-2,'456','<h1 style="color:#fff; font-size:25pt">'.$data->rank.'</h1>');
      $pdf->writeHTMLCell(0,0,$center,'456','<h1 style="color:#fff; font-size:25pt">'.$dataweek->rank.'</h1>');
      $pdf->writeHTMLCell(0,0,$right-3,'456','<h1 style="color:#fff; font-size:25pt">'.$datamonth->rank.'</h1>');
      $pdf->writeHTMLCell(0,0,'85',422,'<h2 style="color:white; font-weight:normal">Learning Plans</h2>');
      }





      if($count >= 5){
      $data = $DB->get_record('block_gm_overall_progc', array('userid' => $userdata->id));
      $dataweek = $DB->get_record('block_gm_weekly_progc', array('userid' => $userdata->id));
      $datamonth = $DB->get_record('block_gm_monthly_progc', array('userid' => $userdata->id));
      $datamonth->rank = $datamonth->rank ? $datamonth->rank: 'NA';
      $dataweek->rank = $dataweek->rank ? $dataweek->rank : 'NA';
      $data->rank =  $data->rank ? $data->rank :'NA';
      $divheight6 = 490;
      $pdf->Image('images/slices/slice-06.jpg', 0,$divheight6, 210, 70, 'JPG');
      $pdf->writeHTMLCell(0, 0, $left1-1, '535', '<h6 style = "color:#000;font-size:'.$fontsize.'pt;font-weight:normal">Rank</h6>', 3, 0, false, true, ' ', false);
      $pdf->writeHTMLCell(0, 0, $center1-2, '535', '<h6 style = "color:#000;font-size:'.$fontsize.'pt;font-weight:normal">Week</h6>', 3, 0, false, true, ' ', false);
      $pdf->writeHTMLCell(0, 0, $right1-3, '535', '<h6 style = "color:#000;font-size:'.$fontsize.'pt;font-weight:normal">Month</h6>', 3, 0, false, true, ' ', false);  
      $pdf->writeHTMLCell(0,0,$left1-1,'525','<h1 style="color:#000; font-size:25pt">'.$data->rank.'</h1>');
      $pdf->writeHTMLCell(0,0,$center1-2,'525','<h1 style="color:#000; font-size:25pt">'.$dataweek->rank.'</h1>');
      $pdf->writeHTMLCell(0,0,$right1-2,'525','<h1 style="color:#000; font-size:25pt">'.$datamonth->rank.'</h1>');

      $pdf->writeHTMLCell(0,0,'75',$divheight6+2,'<h2 style="color:orangered; font-weight:normal">Program Completions</h2>');
      }

        if (!file_exists($CFG->dataroot.'/gamificationpdf')) {
	        mkdir($CFG->dataroot.'/gamificationpdf', 0777, true);
        }
   
   /*save report file in moodle data*/
        $pdf->Output($CFG->dataroot.'/gamificationpdf/'.$userdata->id.'-user_gamification_report.pdf', 'F');
        chmod($CFG->dataroot.'/gamificationpdf/'.$userdata->id.'-user_gamification_report.pdf', 0777);
    }
}
else{
    $count = $DB->count_records('block_gm_events');
    $width = 210;
    $height = 210+$count*70;
    // $height = 490;

    $pageLayout = array($width, $height);
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, $pageLayout, true, 'UTF-8', false);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(0, 0, 0, true);
    $pdf->SetAutoPageBreak(FALSE, PDF_MARGIN_BOTTOM);
    $right = 190;
    $center = 135;
    $left = 85;
    $right1 = 118;
    $center1 = 65;
    $left1 = 12;
    $fontsize = 14;
    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Maheshchandra');
    $pdf->SetTitle('Gamification');
    $pdf->SetSubject('TCPDF');
    $pdf->SetKeywords('TCPDF, PDF, example, test, guide');
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    $pdf->setFontSubsetting(true);
    $pdf->SetFont('helveticaB', '', 14, '', true);

    $pdf->AddPage();
    $value = 10;


    $pdf->Image('images/bg-slices/bg and overdrops-01.png', 0,0, 210, 70, 'PNG');
    $pdf->Image('images/bg-slices/bg and overdrops-02.png',0,0,210,70,'PNG');
    $pdf->writeHTMLCell(206,60,130,19, '<h1 style="font-size:28pt; color:white; font-weight:normal">'.fullname($USER).'</h1>', 0, 0, 0, true, '', true);
    $pdf->writeHTMLCell(206,60,130,30, '<h3 style="color:white; font-weight:normal">'.$USER->email.'</h1>', 0, 0, 0, true, '', true);



    $data = $DB->get_record('block_gm_overall_site', array('userid' => $USER->id));
    $data->rank = $data->rank ? $data->rank : 'NA';
    $data->badgecount = $data->badgecount ? $data->badgecount : 'NA';
    $data->points = $data->points ? $data->points : 'NA';
    $data->level = $data->level ? $data->level : 'NA';
    
    $userpicture = $OUTPUT->user_picture($USER, array('size' => 100, 'class'=>'profilepicture','link' => false));
    $divheight1 = 70; 

    $pdf->Image('images/slices/slice-07.jpg', 0,$divheight1, 210, 70, 'JPG');




    $pdf->writeHTMLCell(0, 0, '103', $divheight1+23, '<h6 style = "color:#000;font-size:15pt;font-weight:normal">Rank</h6>', 3, 0, false, true, ' ', false);
    $pdf->writeHTMLCell(0, 0, '168', $divheight1+23, '<h6 style = "color:#000;font-size:14pt;font-weight:normal">Badges</h6>', 3, 0, false, true, ' ', false);
    $pdf->writeHTMLCell(0, 0, '103', $divheight1+55, '<h6 style = "color:#000;font-size:14pt;font-weight:normal">Points</h6>', 3, 0, false, true, ' ', false);
    $pdf->writeHTMLCell(0, 0, '168', $divheight1+55, '<h6 style = "color:#000;font-size:14pt;font-weight:normal">Level</h6>', 3, 0, false, true, ' ', false);
    $pdf->writeHTMLCell(0,0,'103',$divheight1+10,'<h1 style="color:#10D5D7; font-size:30pt">'.$data->rank.'</h1>',3, 0, false, true, ' ', false);
    $pdf->writeHTMLCell(0,0,'168',$divheight1+10,'<h1 style="color:#10D5D7; font-size:30pt">'.$data->badgecount.'</h1>',3, 0, false, true, ' ', false);
    $pdf->writeHTMLCell(0,0,'103',$divheight1+40,'<h1 style="color:#10D5D7; font-size:30pt">'.$data->points.'</h1>',3, 0, false, true, ' ', false);
    $pdf->writeHTMLCell(0,0,'168',$divheight1+40,'<h1 style="color:#10D5D7; font-size:30pt">'.$data->level.'</h1>',3, 0, false, true, ' ', false);
    $pdf->writeHTMLCell(0,0,'17',$divheight1+25 ,'<span style="border-radius:50%">'.$userpicture.'</span>');





    $divheight2 = 140;
    $data = $DB->get_record('block_gm_overall_site', array('userid' => $USER->id));
    $dataweek = $DB->get_record('block_gm_weekly_site', array('userid' => $USER->id));
    $datamonth = $DB->get_record('block_gm_monthly_site', array('userid' => $USER->id));
    $datamonth->rank = $datamonth->rank ? $datamonth->rank: 'NA';
    $dataweek->rank = $dataweek->rank ? $dataweek->rank : 'NA';
    $data->rank =  $data->rank ? $data->rank :'NA';
    $pdf->Image('images/slices/slice-01.jpg', 0,$divheight2, 210, 70, 'JPG');
    $pdf->writeHTMLCell(0, 0, $left-2, '185', '<h6 style = "color:#fff;font-size:'.$fontsize.'pt;font-weight:normal">Rank</h6>', 3, 0, false, true, ' ', false);
    $pdf->writeHTMLCell(0, 0, $center, '185', '<h6 style = "color:#fff;font-size:'.$fontsize.'pt;font-weight:normal">Week</h6>', 3, 0, false, true, ' ', false);
    $pdf->writeHTMLCell(0, 0, $right-3, '185', '<h6 style = "color:#fff;font-size:'.$fontsize.'pt;font-weight:normal">Month</h6>', 3, 0, false, true, ' ', false);  
    $pdf->writeHTMLCell(0,0,$left-2,'172','<h1 style="color:#fff; font-size:25pt">'.$data->rank.'</h1>');
    $pdf->writeHTMLCell(0,0,$center,'172','<h1 style="color:#fff; font-size:25pt">'.$dataweek->rank.'</h1>');
    $pdf->writeHTMLCell(0,0,$right-2,'172','<h1 style="color:#fff; font-size:25pt">'.$datamonth->rank.'</h1>');
    $pdf->writeHTMLCell(0,0,'90',$divheight2+2,'<h2 style="color:white; font-weight:normal">Site Level</h2>');




    if($count >= 1){
    $divheight3 = 210;
    $data = $DB->get_record('block_gm_overall_cc', array('userid' => $USER->id));
    $dataweek = $DB->get_record('block_gm_weekly_cc', array('userid' => $USER->id));
    $datamonth = $DB->get_record('block_gm_monthly_cc', array('userid' => $USER->id));
    $datamonth->rank = $datamonth->rank ? $datamonth->rank: 'NA';
    $dataweek->rank = $dataweek->rank ? $dataweek->rank : 'NA';
    $data->rank =  $data->rank ? $data->rank :'NA';
    $pdf->Image('images/slices/slice-04.jpg', 0,$divheight3, 210, 70, 'JPG');
    $pdf->writeHTMLCell(0, 0, $left1, '255', '<h6 style = "color:#000;font-size:'.$fontsize.'pt;font-weight:normal">Rank</h6>', 3, 0, false, true, ' ', false);
    $pdf->writeHTMLCell(0, 0, $center1, '255', '<h6 style = "color:#000;font-size:'.$fontsize.'pt;font-weight:normal">Week</h6>', 3, 0, false, true, ' ', false);
    $pdf->writeHTMLCell(0, 0, $right1-2, '255', '<h6 style = "color:#000;font-size:'.$fontsize.'pt;font-weight:normal">Month</h6>', 3, 0, false, true, ' ', false);  
    $pdf->writeHTMLCell(0,0,$left1,'244','<h1 style="color:#000; font-size:25pt">'.$data->rank.'</h1>');
    $pdf->writeHTMLCell(0,0,$center1,'244','<h1 style="color:#000; font-size:25pt">'.$dataweek->rank.'</h1>');
    $pdf->writeHTMLCell(0,0,$right1,'244','<h1 style="color:#000; font-size:25pt">'.$datamonth->rank.'</h1>');
    $pdf->writeHTMLCell(0,0,'80',$divheight3+2,'<h2 style="color:orangered; font-weight:normal">Course completions</h2>');
    }




    if($count >= 2){
    $data = $DB->get_record('block_gm_overall_certc', array('userid' => $USER->id));
    $dataweek = $DB->get_record('block_gm_weekly_certc', array('userid' => $USER->id));
    $datamonth = $DB->get_record('block_gm_monthly_certc', array('userid' => $USER->id));
    $datamonth->rank = $datamonth->rank ? $datamonth->rank: 'NA';
    $dataweek->rank = $dataweek->rank ? $dataweek->rank : 'NA';
    $data->rank =  $data->rank ? $data->rank :'NA';
    $divheight4 = 280;
    $pdf->Image('images/slices/slice-02.jpg', 0,$divheight4, 210, 70, 'JPG');
    $pdf->writeHTMLCell(0, 0, $left-4, '325', '<h6 style = "color:#fff;font-size:'.$fontsize.'pt;font-weight:normal">Rank</h6>', 3, 0, false, true, ' ', false);
    $pdf->writeHTMLCell(0, 0, $center-1, '325', '<h6 style = "color:#fff;font-size:'.$fontsize.'pt;font-weight:normal">Week</h6>', 3, 0, false, true, ' ', false);
    $pdf->writeHTMLCell(0, 0, $right-4, '325', '<h6 style = "color:#fff;font-size:'.$fontsize.'pt;font-weight:normal">Month</h6>', 3, 0, false, true, ' ', false);  
    $pdf->writeHTMLCell(0,0,$left-2,'314','<h1 style="color:#fff; font-size:25pt">'.$data->rank.'</h1>');
    $pdf->writeHTMLCell(0,0,$center,'314','<h1 style="color:#fff; font-size:25pt">'.$dataweek->rank.'</h1>');
    $pdf->writeHTMLCell(0,0,$right-3,'314','<h1 style="color:#fff; font-size:25pt">'.$datamonth->rank.'</h1>');
    $pdf->writeHTMLCell(0,0,'80',282,'<h2 style="color:white; font-weight:normal">Certification Completions</h2>');
    }




    if($count >= 3){
    $data = $DB->get_record('block_gm_overall_clc', array('userid' => $USER->id));
    $dataweek = $DB->get_record('block_gm_weekly_clc', array('userid' => $USER->id));
    $datamonth = $DB->get_record('block_gm_monthly_clc', array('userid' => $USER->id));
    $datamonth->rank = $datamonth->rank ? $datamonth->rank: 'NA';
    $dataweek->rank = $dataweek->rank ? $dataweek->rank : 'NA';
    $data->rank =  $data->rank ? $data->rank :'NA';
    $divheight4 = 350;
    $pdf->Image('images/slices/slice-05.jpg', 0,$divheight4, 210, 70, 'JPG');
    $pdf->writeHTMLCell(0, 0, $left1+4, '395', '<h6 style = "color:#000;font-size:'.$fontsize.'pt;font-weight:normal">Rank</h6>', 3, 0, false, true, ' ', false);
    $pdf->writeHTMLCell(0, 0, $center1+4, '395', '<h6 style = "color:#000;font-size:'.$fontsize.'pt;font-weight:normal">Week</h6>', 3, 0, false, true, ' ', false);
    $pdf->writeHTMLCell(0, 0, $right1+3, '395', '<h6 style = "color:#000;font-size:'.$fontsize.'pt;font-weight:normal">Month</h6>', 3, 0, false, true, ' ', false);  
    $pdf->writeHTMLCell(0,0,$left1+4,'385','<h1 style="color:#000; font-size:25pt">'.$data->rank.'</h1>');
    $pdf->writeHTMLCell(0,0,$center1+4,'385','<h1 style="color:#000; font-size:25pt">'.$dataweek->rank.'</h1>');
    $pdf->writeHTMLCell(0,0,$right1+4,'385','<h1 style="color:#000; font-size:25pt">'.$datamonth->rank.'</h1>');
    $pdf->writeHTMLCell(0,0,'85',$divheight4+2,'<h2 style="color:orangered; font-weight:normal">Classroom Completions</h2>');
    }





    if($count >= 4){
    $data = $DB->get_record('block_gm_overall_lpc', array('userid' => $USER->id));
    $dataweek = $DB->get_record('block_gm_weekly_lpc', array('userid' => $USER->id));
    $datamonth = $DB->get_record('block_gm_monthly_lpc', array('userid' => $USER->id));
    $datamonth->rank = $datamonth->rank ? $datamonth->rank: 'NA';
    $dataweek->rank = $dataweek->rank ? $dataweek->rank : 'NA';
    $data->rank =  $data->rank ? $data->rank :'NA';
    $divheight5 = 420;
    $pdf->Image('images/slices/slice-03.jpg', 0,$divheight5, 210, 70, 'JPG');
    $pdf->writeHTMLCell(0, 0, $left-4, '467', '<h6 style = "color:#fff;font-size:'.$fontsize.'pt;font-weight:normal">Rank</h6>', 3, 0, false, true, ' ', false);
    $pdf->writeHTMLCell(0, 0, $center-1, '467', '<h6 style = "color:#fff;font-size:'.$fontsize.'pt;font-weight:normal">Week</h6>', 3, 0, false, true, ' ', false);
    $pdf->writeHTMLCell(0, 0, $right-4, '467', '<h6 style = "color:#fff;font-size:'.$fontsize.'pt;font-weight:normal">Month</h6>', 3, 0, false, true, ' ', false);  
    $pdf->writeHTMLCell(0,0,$left-2,'456','<h1 style="color:#fff; font-size:25pt">'.$data->rank.'</h1>');
    $pdf->writeHTMLCell(0,0,$center,'456','<h1 style="color:#fff; font-size:25pt">'.$dataweek->rank.'</h1>');
    $pdf->writeHTMLCell(0,0,$right-3,'456','<h1 style="color:#fff; font-size:25pt">'.$datamonth->rank.'</h1>');
    $pdf->writeHTMLCell(0,0,'85',422,'<h2 style="color:white; font-weight:normal">Learning Plans</h2>');
    }





    if($count >= 5){
    $data = $DB->get_record('block_gm_overall_progc', array('userid' => $USER->id));
    $dataweek = $DB->get_record('block_gm_weekly_progc', array('userid' => $USER->id));
    $datamonth = $DB->get_record('block_gm_monthly_progc', array('userid' => $USER->id));
    $datamonth->rank = $datamonth->rank ? $datamonth->rank: 'NA';
    $dataweek->rank = $dataweek->rank ? $dataweek->rank : 'NA';
    $data->rank =  $data->rank ? $data->rank :'NA';
    $divheight6 = 490;
    $pdf->Image('images/slices/slice-06.jpg', 0,$divheight6, 210, 70, 'JPG');
    $pdf->writeHTMLCell(0, 0, $left1-1, '535', '<h6 style = "color:#000;font-size:'.$fontsize.'pt;font-weight:normal">Rank</h6>', 3, 0, false, true, ' ', false);
    $pdf->writeHTMLCell(0, 0, $center1-2, '535', '<h6 style = "color:#000;font-size:'.$fontsize.'pt;font-weight:normal">Week</h6>', 3, 0, false, true, ' ', false);
    $pdf->writeHTMLCell(0, 0, $right1-3, '535', '<h6 style = "color:#000;font-size:'.$fontsize.'pt;font-weight:normal">Month</h6>', 3, 0, false, true, ' ', false);  
    $pdf->writeHTMLCell(0,0,$left1-1,'525','<h1 style="color:#000; font-size:25pt">'.$data->rank.'</h1>');
    $pdf->writeHTMLCell(0,0,$center1-2,'525','<h1 style="color:#000; font-size:25pt">'.$dataweek->rank.'</h1>');
    $pdf->writeHTMLCell(0,0,$right1-2,'525','<h1 style="color:#000; font-size:25pt">'.$datamonth->rank.'</h1>');

    $pdf->writeHTMLCell(0,0,'75',$divheight6+2,'<h2 style="color:orangered; font-weight:normal">Program Completions</h2>');
    }
    $pdf->Output('sample.pdf', 'I');
}