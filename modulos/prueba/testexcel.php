<?php
/****************************************************************
* Script		 : test script for class BiffWriter
* Author		 : Christian Novak - cnovak@gmx.net
*
******************************************************************/
require_once('biff.php');
/* 
** error reporting at full level. May be annoying but 
** reveals unitialized variables and helps to prevent 
** undesired type casting
*/
error_reporting (E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
/*
** this sample  shows how to _self_ call a submitting form too
*/
if (empty($_GET['submit'])) {
	echo '<html>';
	echo '<head>';
	echo '<title>BiffWriter - PHP class to output Excel compatible files</title>';
	echo '<link rel="stylesheet" href="../styles.css" type="text/css">';
	echo '</head><body>';
	echo '<form method=get action="testexcel.php">';
	echo '<b>BiffWriter - Life demo</h1></b><hr>';
	echo '<input type="radio" name="cmd" value="demo" checked>Create a generic demo file showing all functions<br>';
	echo '<input type="radio" name="cmd" value="test">Show demo source<br>';
	echo '<input type="radio" name="cmd" value="numtest">Numeric boundary check file<br>';
	/*
	** here we disable the call to creating a huge file if the server is www.cnovak.com
	** since I really don't want to exceed my monthy download limit of 6 GB
	*/
	if ($HTTP_SERVER_VARS["HTTP_HOST"] != 'www.cnovak.com') {
		echo '<input type="radio" name="cmd" value="big">Create a huge file &gt; 4MB<br><br>';
		echo '<input type="checkbox" name="cr_file" value="true">Save to file, otherwise stream contents to browser: ';
		echo '<input type="text" name="fname" value="test.xls"><br>';
	}
	echo '<hr>&nbsp;<input type="submit" name="submit" value="submit">';
	echo '</form>';
	echo '</body>';
	echo '</html>';
}
else {
	if (empty($_GET['cr_file'])) {
		$fname = '';
	}
	else {
	    $fname = $_GET['fname'];
	}
	switch ($_GET['cmd']) {
		 case 'test' :
			highlight_file('testexcel.php');
			exit;
		 case 'big' :
			/*
			** here we call the base class with NO error checking, faster but slightly
			** dangerous. When using this class, you need to supply ALL the arguments to each 
			** function, i.e.
			** xlsWriteNumber(1, 1, 1.2345) is WRONG
			** xlsWriteNumber(1, 1, 1.2345, 10, 0, FONT_0, ALIGN_RIGHT, 0) is CORRECT
			*/
			BigFile(1000);
			break;
		 case 'numtest' :
			Numtest();
			break;
		 case 'demo' :
			Demo();
	 }
	 if (!empty($fname)) {
		 print 'Get your file <a href="' .$fname. '">' .$fname. '</a>';
	 }
}

/* 
* function to show all of the current capabilities
*/
function Demo() 
{
	global $fname;
	$myxls = new BiffWriter();
	// file name suggested to the browser (stream)
	$myxls->outfile = 'demo.xls';

	// create 4 default fonts
	$myxls->xlsSetFont('Arial', 10, FONT_NORMAL);
	$myxls->xlsSetFont('Arial', 12, FONT_BOLD);
	$myxls->xlsSetFont('Courier New', 8, FONT_NORMAL);
	$myxls->xlsSetFont('Courier New', 8, FONT_BOLD);
	
	// set default row height to 14 points
	$myxls->xlsSetDefRowHeight(14);
	
	// turn  backup flag on (xlk)
	$myxls->xlsSetBackup();

	// print grid lines
	$myxls->xlsSetPrintGridLines();

	// print row (1,2...) and col (A, B..) references
	$myxls->xlsSetPrintHeaders();

	// protect the sheet against changes, pw = ABCD
	// all EMPTY cells AND cells having the status CELL_LOCKED are protected
	$myxls->xlsProtectSheet('ABCD', TRUE);

	// print header
	$myxls->xlsHeader('&12BiffWriter Demo (c) C. Novak - cnovak@gmx.net');

	// print footer
	$myxls->xlsFooter('&L&12Page &P of &N');

	// print margin in inches
	$myxls->xlsPrintMargins(1, 1, 1, 1);

	// set a page break after row 2
	$myxls->xlsAddHPageBreak(2);

	// set a page break before column C
	$myxls->xlsAddVPageBreak('C');

	// create a head line
	
	// set row height for the first row to 20 points
	$myxls->xlsWriteText('A1', 0, 'BiffWriter Demo (c) C. Novak - (c) by www.web-aware.com', -1, 0, FONT_1, CELL_BOTTOM_BORDER, CELL_LOCKED);
	$myxls->xlsCellNote('A1', 0, 'This cell is protected with CELL_LOCKED. Notes can contain up to 2048 characters currently');
	$myxls->xlsCellNote('D1', 0, 'This row has an individual height of 20 points. It is further froozen together with column A');
	// freeze row 1 and column 1 and set the height to 20 points
	$myxls->xlsFreeze('B2');
	$myxls->xlsSetRow(0, 20);
	// create some content
	$myxls->xlsWriteText('A2', 0, 'Biff 2.1 allows for up to 4 fonts having different attributes:',-1);
	$myxls->xlsCellNote('D2', 0, 'Up to 4 fonts can be set individually with Biff. Just remember to use generic fonts available on your clients computer too');
	$myxls->xlsWriteText('A3', 0, 'Arial', 0, 0, FONT_0, ALIGN_RIGHT);
	$myxls->xlsWriteText('A4', 0, 'Arial bold', 0, 0, FONT_1, ALIGN_RIGHT);
	$myxls->xlsWriteText('A5', 0, 'Courier', 0, 0, FONT_2, ALIGN_RIGHT);
	$myxls->xlsWriteText('A6', 0, 'Courier bold', 0, 0, FONT_3, ALIGN_RIGHT);
	$myxls->xlsWriteText('A7', 0, 'BIFF 2.1 comes with 21 predefined formats, custom formats can be added:',-1);
	$myxls->xlsCellNote('A7', 0, 'The cells below are set to "autowidth" and determine the width of column A');

	// add a user defined picture (format)
	$idx_fmt = $myxls->xlsAddFormat('[Blue] 0 "US$"');
	// print all defined picture strings using 33333.3333
	for ($x = 0; $x < count($myxls->picture); $x++) {
		$myxls->xlsWriteText($x+7, 0, 'Format id ' .strval($x), 0, 0, FONT_2);
		$myxls->xlsWriteNumber($x+7, 1, 33333.3333, 20, $x, FONT_2);
		if (empty($myxls->picture[$x])) {
			$myxls->xlsWriteText($x+7, 2, 'predefined', 0, 0, FONT_2);
		}
		else {
			 $myxls->xlsWriteText($x+7, 2, 'custom '. $myxls->picture[$x], 0, 0, FONT_3, CELL_BOX_BORDER);
		}
	}
	// add another user defined picture (format) 
	// and write a SQL datetime stamp value
	$idx_fmt = $myxls->xlsAddFormat('DD-MM-YYYY h:m:s');
	$myxls->xlsWriteText($x+7, 0, 'SQL datetime conversion 20020508101125', 0, 0, FONT_2);
	$myxls->xlsWriteDateTime($x+7, 1, '20020508101125', 0, $idx_fmt, FONT_2);
    $myxls->xlsWriteText($x+7, 2, 'custom '. $myxls->picture[$idx_fmt], 0, 0, FONT_3, CELL_BOX_BORDER);

	// assemble the stream 
	// and either send the stream to the browser or 
	// save it as a file in the location/filename passed via $fname
	$myxls->xlsParse($fname);
	return;
} // end func

/*
* function to check numeric streams and boundaries, implemented after the huge 
* bug with floating point numbers, RUN THIS and VERIFY it is correct on your 
* Unix box!!!!!
*/
function Numtest() 
{
	global $fname;
	$myxls = new BiffWriter();
	$myxls->outfile = 'numtest.xls';
	$myxls->xlsWriteText(0,0, 'Largest allowed positive number 9.99999999999999E307 verify:', 60);
	$myxls->xlsWriteText(1,0, 'Smallest allowed negative number -9.99999999999999E307 verify:', -1);
	$myxls->xlsWriteText(2,0, 'Smallest allowed positive number 1E-307 verify:', -1);
	$myxls->xlsWriteText(3,0, 'Largest allowed negative number -1E-307 verify:', -1);
	$myxls->xlsWriteNumber(0,1, 9.99999999999999E307, 10);
	$myxls->xlsWriteNumber(1,1, -9.99999999999999E307);
	$myxls->xlsWriteNumber(2,1, 1E-307);
	$myxls->xlsWriteNumber(3,1, -1E-307);
	$myxls->xlsParse($fname);
	return;
}

/*
** function to check server load,
** mainly used to analyze and to improve execution time 
** of the biffwriter class. 
** Note that this function uses straight BiffBase
** 
*/
function BigFile($iter) 
{
	global $fname;
	$myxls = new BiffBase();
	$myxls->outfile = 'big.xls';
	$x = 1;
	for ($r = 0 ; $r < $iter; $r++) {
		for ($c = 0; $c <= MAX_COLS; $c++ ) {
			$myxls->xlsWriteNumber($r, $c, $x++, 10, 0, FONT_0, ALIGN_RIGHT, 0);
		}
	}
	$myxls->xlsParse($fname);
	return;
} // end func
?>