<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Instructions</title>
<style>
#ex2_container { text-align:left; font-size:120%;}
#ex1_container { text-align:left; font-size:120%;}
</style>
</head>

<?php

$browser = $_SERVER['HTTP_USER_AGENT'];
$validbrowser = 1;

if ( strpos($browser, "Safari") ) {
	$validbrowser = 2;
}

if ( strpos($browser, "Chrome") ) {
      $validbrowser = 1;
}
?>
 
<body onload="loadEventHandler()">
<div id="ex2_container">
<br><br><br>
Welcome to our study!<br><br>
<font color='red'><b>IMPORTANT!</b> This study runs best in Firefox, on a desktop/laptop.<br><br> 
The study will <b>NOT </b> run on Safari, or a mobile device.</font><br><br>

In this study you will look for an exit in a maze. <br><br> 
After this task, you will be asked 3 unrelated reasoning questions, and asked to provide demographic information.<br><br>
The study is expected to take 10-15 minutes.<br><br>
Thanks for participating!
<br><br>

<p><font size='2'>
Informed Consent <br>
By answering the following questions, you are participating in a study performed by cognitive scientists in the MIT Department of Brain and Cognitive Science. If you have questions
about this research, please contact the experimenter. Your participation in this research is voluntary. You may decline to answer any or all of the following questions. You may decline further participation, at any time, without adverse consequences. Your anonymity is assured; the researchers who have requested your participation will not receive
 any personal identifying information about you. By clicking 'I agree' you indicate your consent to participate in this study.
</font></p> <br>

</div>

<div id="ex1_container">
<form name="frm" action="" method="post" onsubmit="return validateForm()">
  <input type="text" name="UID" hidden>
  <input type="submit" value="I agree"/>
</form>

</div>

<script>

var browserok = <?php global  $validbrowser; echo  $validbrowser; ?>;
var bs = "<?php global  $browser; echo  $browser; ?>";

function loadEventHandler() {
 
  document.forms["frm"].action = "int_exp1.php";

  if (browserok == 2) {
//      s  = document.getElementById("ex2_container").innerHTML;
//      document.getElementById("ex1_container").innerHTML = "<br><br><font color='red'>UNFORTUNATELY YOUR BROWSER IS NOT SUPPORTED.</font>";
  }

}


function validateForm() {
    if (browserok == 2) {
//       alert("Unfortunately your browser is not supported.");
  //     return false;
     }

    var UID = "S";
    UID = UID+Math.floor((Math.random() * 100000000) + 1); 

    // generate subject ID
    document.forms["frm"]["UID"].value = UID;
    return true;  
}

</script>
</body>
</html>
