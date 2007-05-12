<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="en"><head>
<link rel="stylesheet" type="text/css" href="safe.css"><title>Build Hostel
Database</title>
<meta http-equiv="pragma" content="no-cache">
<meta http-equiv="expires" content="Wed, 11 Jun 2001 00:00:01 GMT">
<meta name="description" content="Undergraduate, Graduate and Part-time course admissions at MNIT">
<meta name="keywords" content="mnit, university, undergraduate, graduate, admissions, postgraduate, prospectus, part-time, degree, degrees, course, courses">
<meta name="build" content="mnit template v1">
<script language="JavaScript" type="text/javascript">

<!-- Begin
function handler(){
var URL = document.form.site.options[document.form.site.selectedIndex].value;
window.location.href = URL;
}
// End -->

</script></head><body>
<table summary="" class="width100" border="0" cellpadding="5" cellspacing="5">
<tbody><tr>
<td id="leftcol" valign="top">
<a name="top"></a>
<div class="textcenter">
<a href="http://www.mnit.ac.in/">
<img src="mnit_logo.gif" alt="MNIT page" height="107" width="107"></a>
</div>

<a href="#content">
<img src="blank.gif" alt="Skip navigation" height="1" width="150"></a>
<hr>


<a href="http://mnit.ac.in/">Home</a><br><hr>

<a href="http://mnit.ac.in/aboutmnit/">About MNIT</a><br><hr>
<a href="http://mnit.ac.in/academic/">Academic</a><br><hr>
<a href="http://mnit.ac.in/admissions/">Admissions</a><br><hr>
<a href="http://mnit.ac.in/administration/">Administration</a><br><hr>

<a href="http://mnit.ac.in/departments/">Departments</a><br><hr>
<a href="http://mnit.ac.in/students/">Students</a><br><hr>
<a href="http://mnit.ac.in/libraries/">Libraries</a><br><hr>
<a href="http://mnit.ac.in/placement/">Placement</a><br><hr>

<a href="http://mnit.ac.in/creativearts/">Creative Arts</a><br><hr>


</td>

<td id="rightcol" valign="top">
<table summary="" border="0" width="100%">
<tbody><tr width="100%">

<td align="left" valign="right" width="100%">
<a name="content"></a><h1>MNIT Hostel Allocation</h1>
<p>Room Allocation Of Given Students</td>

<td align="left" valign="right" width="100%">
<p><label for="search">
<img src="searchblue.gif" alt="Site Search:" width="107" height="14">&nbsp;</label><br>
 
<!--
<label for="logo"><img src="../images/logo.gif" width="107" height="100" alt="Institute Logo:">&nbsp;</label><br>-->
<label for="search">
<img src="atoz1.gif" alt="Institute Logo:" usemap="#AtoZ" align="bottom" height="54" width="255">&nbsp;</label><br>
<!--  <td rowspan="2"><img
 src="../images/atoz1.gif" alt="A to Z Index"
 width="255" height="54" border="0" usemap="#AtoZ">-->
</p></td>
</tr>
<tr><td colspan="2">

<img src="blueline.gif" alt="" height="3" width="100%"><br></td></tr>
<!--

<tr valign="top"><td><img src="../images/welcome.gif" width="200" height="16" alt="Welcome to MNIT Jaipur"></td><td>&nbsp;</td></tr>
-->
<tr valign="top"><td colspan="2">

&nbsp;<p>
<?php
/////////////////////////////////////////
include 'config.php';
function CheckId($id)
{
	GLOBAL $hostname, $username, $password, $database;
	$conn = mysql_connect($hostname, $username, $password) or die(mysql_error());
	//echo $database."\n";
	mysql_select_db($database)
		or die("Database don't exist  ".mysql_error());
	$sql = "Select * From studentroom Where StudentID = ".$id;
	//echo $sql."<br>";
	$result = mysql_query($sql)
			or die("Query Error :$sql<br>".mysql_error());
	if (mysql_affected_rows() != 0)
	{
        echo mysql_affected_rows();
        mysql_close();
		return 1;
	}
	else
	{
		mysql_close();
		return 0;
	}
}

function RandomAlloc($id)
{
	GLOBAL $hostname, $username, $password, $database;

	$hostelid = $_POST['HostelNo'];
	$type = $_POST['type'];

	$conn = mysql_connect($hostname, $username, $password) or die(mysql_error());
	mysql_select_db($database)
		or die("Database don't exist ".mysql_error());
	$sql = "Select RoomKeyID From tblroomkey Where Occupied = 0 AND HostelID =".$hostelid." And Type =".$type;
	//echo $sql;
	$result = mysql_query($sql) or
			die("Invalid Query: ".$mysql_error());
	//echo "\n<br>$mysql_num_rows($result)";
	$rowcount = mysql_num_rows($result);
	if ($rowcount <= 0)
	{
		echo "Error: Not enough room in the hostel";
		return -1;
	}

	//randomize random no. seed
	srand(microtime()+100000);
	$rand = rand(1, $rowcount); //insert call to rnd here;
	if ($rand > $rowcount) $rand = $rowcount;
	$count = 0;
	while ($row = mysql_fetch_array($result))
	{
		$count = $count + 1;
		if ($count == $rand)
		{
			$roomkeyid = $row['RoomKeyID'];
			break;
		}
	}

	//Set Occupied = True for that Room in the Database
	$sql = "Update tblroomkey Set Occupied=1 Where RoomKeyID=$roomkeyid";

	$result = mysql_query($sql) or
			die("Invalid Query: $sql");

	//Set StudentRoom Database that room is allocated to that user
	$sql = "Insert Into studentroom (StudentID, RoomKeyID) Values (".$id.",".$roomkeyid.")";
	$result = mysql_query($sql) or
			die("Invalid Query: ".$mysql_error());
	//close the connection now
	mysql_close();
	return $roomkeyid;
}

function AddRoom($id, $room)
{
	GLOBAL $hostname, $username, $password, $database;

	$hostelid = $_POST['HostelNo'];
	$type = $_POST['type'];

	$conn = mysql_connect($hostname, $username, $password) or die(mysql_error());
	mysql_select_db($database)
		or die("Database don't exist ".mysql_error());

	$sql = "Update tblroomkey Set Occupied=1 Where RoomKeyID =".$room;
	$result = mysql_query($sql) or
			die("Update Query Failed: ".$mysql_error());

	// // // // //
	$sql = "Insert Into studentroom (StudentID, RoomKeyID) Values ('".$id."','".$room."')";
	//echo "<br>$sql";
	$result = mysql_query($sql) or
			die("Insert Into StudentRoom Query Failed in AddRoom:$sql<br>".mysql_error());

	mysql_close();
	//echo "Room Added";
}
?>
<?php
if (!isset($_POST['Submit'])){
?>
<form method="POST">
  <div align="center">
    <center>
    <table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#111111" width="624" height="294">
      <tr>
        <td width="624" height="89" colspan="2">
        <h1>Room Allocation</h1>
        </td>
      </tr>
      <tr>
        <td width="241" height="29" valign="top">Student IDs:</td>
        <td width="377" height="29" valign="top">
        <input type="text" name="StudentID1" size="20"></td>
      </tr>
      <tr>
        <td width="241" height="29" valign="top">Student 2:</td>
        <td width="377" height="29" valign="top">
        <input type="text" name="StudentID2" size="20"></td>
      </tr>
      <tr>
        <td width="241" height="29" valign="top">Student 3:</td>
        <td width="377" height="29" valign="top">
        <input type="text" name="StudentID3" size="20"></td>
      </tr>
      <tr>
        <td width="241" height="29" valign="top">Hostel No.</td>
        <td width="377" height="29" valign="top">
        <select name="HostelNo">
<?php

	$conn = mysql_connect($hostname, $username, $password) or die(mysql_error());
	mysql_select_db($database)
		or die(mysql_error());
	$sql = "SELECT HostelName, HostelID From tblHostel Order By HostelName";
	$result = mysql_query($sql) or die("Invalid Query". mysql_error());
	while ($row = mysql_fetch_array($result))
		{
		echo "<option value =" . $row['HostelID'] . ">".$row['HostelName']."</option>\n";
		}
	mysql_close();
	?>
        </select></td>
      </tr>
      <tr>
        <td width="241" height="27" valign="top">Room Choice</td>
        <td width="377" height="27" valign="top">
        <input type="radio" value="1" name="type" checked>Single<input type="radio" value="2" name="type">Double
        <input type="radio" value="3" name="type">Triple</td>
      </tr>
      <tr>
        <td width="618" height="26" valign="top" colspan="2">&nbsp;</td>
      </tr>
      <tr>
        <td width="624" height="32" valign="top" colspan="2">
        <p align="center"><input type="submit" value="Submit" name="Submit">&nbsp;&nbsp;
        <input type="reset" value="Reset" name="B2"></td>
      </tr>
    </table>
    </center>
  </div>
</form>
<?php
}
else {
//////////////////////////////////////////////////////////
// Room Allocation goes here
//////////////////////////////////////////////////////////
$type = $_POST['type'];
$id[0] = $_POST['StudentID1'];
$id[1] = $_POST['StudentID2'];
$id[2] = $_POST['StudentID3'];
// get student ID not student college ID!!
// error corrected on "27/10/05"
mysql_connect($hostname, $username, $password) or die('Error connecting to database');
mysql_select_db($dbname) or die('Database connectivity error');
$sql = "SELeCT * FROM tblstudents WHERE StudentID = '$id[0]'";
//////////////////////////////
$ret = mysql_query("$sql") or die("Error in $sql");
$row = mysql_fetch_array($ret);
$id[0] = $row[0];
if ($id[0] == ""){
	echo "Please select atleast one user id.";
	exit();
}
if ($type == 2)  {
  if ($id[1]==''){
	echo "You selected Double room but typed only one Student ID.";
	exit();}
  else {
    $sql = "SELeCT * FROM tblstudents WHERE StudentID = $id[1]";
    $ret = mysql_query("$sql") or die("Error in $sql");
    $row = mysql_fetch_array($ret);
    $id[1] = $row["ID"];
  }
 }
else if ($type == 3)
{
	if ($id[2] == ""){
	   echo "You selected triple type room but typed unsfficient user ids";
	   exit();
	}
	else{
    $sql = "SELeCT * FROM tblstudents WHERE StudentID = $id[2]";
    $ret = mysql_query("$sql") or die("Error in $sql");
    $row = mysql_fetch_array($ret);
    $id[2] = $row["ID"];
 }
}
if (CheckID($id[0]) == 1) {
	die ("ID already exist in the database");
}
else {
	$roomalloc = RandomAlloc($id[0]);
	if ($roomalloc != -1){
		print ("<br>Room successfully allocated.");
	}
	else{
		$err = 1;
	}
}
if ($type == 2 and $id[1] != '') {
  if (CheckID($id[1])!=1){
     AddRoom($id[1], $roomalloc);
	}
  else {
    echo "Second user is already allocated a room!";
  }
}
if ($type == 2) {
  if ($id[2]!=''){
  if (CheckID($id[2])!=1){
 	 AddRoom($id[1], $roomalloc);
	}
  else {
    echo "Third user is already allocated a room!";
  }
  }
}
echo "<b>Processing Successful.</b><br>";
echo "<a href='index.htm'>Home</a> <a href='roomalllocation.php'>Allocate More</a>";
}
?>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</td>
                                      
                                      </tr>
                                      <tr>
<!--                                       <td valign="top" width="196"
 eight="9">         <br>
                           </td>
                                     </tr>
                                                                        
                                                              
  </tbody>                                  
</table>

</td>
<td>

</td></tr>
</table>
</p>

</td>

<td rowspan="2" id="rhinfo"><center> -->
<!--text
<img src="../images/student22.gif" width="60" height="50" alt="MNIT students">
</center>
<h2>Useful information</h2>

<div class="bbullet">
Financial and scholarship information for <a href="admission/financing.html">undergraduates</a>, <a href="admission/financing.html">graduates</a> and <a href="admission/financing.html">international students</a><span class="hidden">;</span></div>

<div class="bbullet">
<a href="admissions/international.html">International Student Guide</a><span class="hidden">;</span></div>
<div class="bbullet">
<a href="/"></a><span class="hidden">;</span></div>


<div class="bbullet"><a href=""></a><span class="hidden">;</span></div>

                       
                     


-->

</tr>
</tbody></table>
<hr>
<div id="footer"><a href="http://mnit.ac.in/contact/">Contact</a>.  Enquiries to Webmaster@mnit.ac.in</div>

</td></tr>
</tbody></table>



<!--start of search-->                                                                    
<map name="AtoZ"><area shape="rect" coords="224,28,247,54" href="http://mnit.ac.in/search/searchresult.php?search=z" alt="Z"><area shape="rect" coords="206,28,228,54" href="http://mnit.ac.in/search/searchresult.php?search=y" alt="Y"><area shape="rect" coords="190,28,205,54" href="http://mnit.ac.in/search/searchresult.php?search=x" alt="X"><area shape="rect" coords="168,28,189,54" href="http://mnit.ac.in/search/searchresult.php?search=w" alt="W"><area shape="rect" coords="153,28,167,54" href="http://mnit.ac.in/search/searchresult.php?search=v" alt="V"><area shape="rect" coords="134,28,152,54" href="http://mnit.ac.in/search/searchresult.php?search=u" alt="U"><area shape="rect" coords="115,28,133,54" href="http://mnit.ac.in/search/searchresult.php?search=t" alt="T"><area shape="rect" coords="95,28,114,54" href="http://mnit.ac.in/search/searchresult.php?search=s" alt="S"><area shape="rect" coords="76,28,95,54" href="http://mnit.ac.in/search/searchresult.php?search=r" alt="R"><area shape="rect" coords="54,28,76,54" href="http://mnit.ac.in/search/searchresult.php?search=q" alt="Q"><area shape="rect" coords="39,28,57,54" href="http://mnit.ac.in/search/searchresult.php?search=p" alt="P"><area shape="rect" coords="18,28,36,54" href="http://mnit.ac.in/search/searchresult.php?search=o" alt="O"><area shape="rect" coords="0,28,19,54" href="http://mnit.ac.in/search/searchresult.php?search=n" alt="N"><area shape="rect" coords="224,0,247,25" href="http://mnit.ac.in/search/searchresult.php?search=m" alt="M"><area shape="rect" coords="206,0,228,25" href="http://mnit.ac.in/search/searchresult.php?search=l" alt="L"><area shape="rect" coords="190,0,209,25" href="http://mnit.ac.in/search/searchresult.php?search=k" alt="K"><area shape="rect" coords="180,0,198,25" href="http://mnit.ac.in/search/searchresult.php?search=j" alt="J"><area shape="rect" coords="155,0,172,25" href="http://mnit.ac.in/search/searchresult.php?search=i" alt="I"><area shape="rect" coords="134,0,152,25" href="http://mnit.ac.in/search/searchresult.php?search=h" alt="H"><area shape="rect" coords="115,0,133,25" href="http://mnit.ac.in/search/searchresult.php?search=g" alt="G"><area shape="rect" coords="95,0,114,25" href="http://mnit.ac.in/search/searchresult.php?search=f" alt="F"><area shape="rect" coords="76,0,95,25" href="http://mnit.ac.in/search/searchresult.php?search=e" alt="E"><area shape="rect" coords="57,0,76,25" href="http://mnit.ac.in/search/searchresult.php?search=d" alt="D"><area shape="rect" coords="39,0,57,25" href="http://mnit.ac.in/search/searchresult.php?search=c" alt="C"><area shape="rect" coords="18,0,36,25" href="http://mnit.ac.in/search/searchresult.php?search=b" alt="B"><area shape="rect" coords="0,0,19,25" href="http://mnit.ac.in/search/searchresult.php?search=a" alt="A"><area shape="rect" coords="4,6,146,19" href="http://mnit.ac.in/search/searchresult.php?search=" alt="Index of MNIT sites"></map>
           <!-- InstanceEnd --><br>

</body></html>
