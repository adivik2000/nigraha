<?php

echo "<h2>Confirmation of Registration</h2>";

echo "<p><b>Payment Made</b>&nbsp;&nbsp;&nbsp;&nbsp;</b>";
switch ($aInfo[0]["accounts"]["mode"]) {
	case "CA": 
				echo "By Cash: ";
				break;
	case "DD": 
				echo "By Demand Draft - No. ".$aInfo[0]["accounts"]["number"].": ";
				break;
}
echo "<b>Rs. ".$aInfo[0]["accounts"]["amount"]."/-</b></p>";

echo "<h3>Personal Information</h3>";
echo "<table>";
foreach ($sInfo as $k=>$v) {
	echo "<tr><td width=\"20%\">$k</td><td>$v</td></tr>";
		
}
echo "</table>";

echo "<h3>Courses Registered</h3>";
echo "<table>";
echo "<tr><td width=\"20%\"><b>Course Code</b></td><td><b>Course Name<b></td><td><b>Credits</b></td></tr>";
foreach ($cInfo as $k=>$v) {
	echo "<tr><td>$k</td><td>$v[0]</td><td>$v[1]</td></tr>";
}
echo "<tr><td colspan=\"2\">&nbsp;</td>";
echo "<td><b>Total Credits:</b> $cTot</td></tr>";
echo "</table>";

echo "<table border=\"0\"><tr><td>";
echo "I hereby declare that the above information is correct to the best of my knowledge.</td>";
echo "<td>&nbsp;</td></tr><td colspan=\"2\">&nbsp;</td></tr><td width=\"60%\">";
echo "Signature of Student";
echo "</td><td>Signature of Course Co-ordinator</td></table>";
echo "<br />".date('l\, d F Y\. H:i')."</p>";

?>
