<!DOCTYPE html>
<html>
<title>
Instructions quiz</title>
<head>
<style>
h2{
    text-align: center;
}
#ex1_container { align:center; text-align: center;}
</style>
</head>
<body>

<?php

// form parametres
function test_input($data) {
   $data = trim($data);
   $data = stripslashes($data);
   $data = htmlspecialchars($data);
   return $data;
}

$UID =  "";
$instructionsOK = 0; 
$dir = "att-test";

$UID = "subjectTMP";
$ip=$_SERVER['REMOTE_ADDR'];
$date = date('d/F/Y h:i:s');
$browser = $_SERVER['HTTP_USER_AGENT'];
$browser = str_replace(' ', '_', $browser);

$steps=0;

if ($_SERVER["REQUEST_METHOD"] == "GET") {

     $s = $dir . "/" . $UID . ".txt";        
     $f = fopen($s, "a") or die("Unable to open file!" . $s);
     fwrite($f, $ip . "\t". $date . "\t" . $browser . "\tQUIZGETREQUEST\n");
     fclose($f);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
   $UID = test_input($_POST["UID"]);
   //echo $UID;

   if(empty($UID)) {
        $UID = "empty uid";
   } else {
        if (!is_writable($dir)) {
          echo 'The directory is not writable ' . $dir . '<br>';
        }
        $s = $dir . "/" . $UID . ".txt";
        $f = fopen($s, "a") or die("101: Unable to open file!" . $s);


        fclose($f);
   }
}

?>

<div id="ex1_container">
<h2>Great, you have finished <b>Practice</b>!</h2><br>

<p  align='center'> 
Please answer the quiz questions below to move on.<br><br>
<font color='red' size=4>Question 1: My task is to .. </font>
<form name="frm" style='border:0' action="test.php" method="post" onsubmit="return validateForm()">
        <fieldset style='border:0'>
            <input style='border:0' type='radio' id = 'id2' name='objective' value='2' /> visit every square in the maze</><br>
            <input style='border:0' type='radio' id = 'id3' name='objective' value='3' /> see how lucky I am</><br>
            <input style='border:0' type='radio' id = 'id1' name='objective' value='1' /> solve the mazes in as few steps as possible</><br>
            <input style='border:0' type='radio' id = 'id4' name='objective' value='4' /> click as fast as possible</><br><br>
        </fieldset>
            <br><font color='red' size=4>Question 2: Please select the image which correctly shows which parts of the maze the character has not seen yet (black squares).</font><br>
        <br> 
        <table align='center'>
         <tr>
           <td><img src='webfile/image1B.png' style='display: block;'></td>
           <td><img src='webfile/image1A.png' style='display: block;'></td>
         </tr>
           <tr><td>A.</td><td>B.</td></tr>
        </table>    
        <fieldset style='border:0'>
            <input style='border:0' type='radio' id = 'image1_id_a' name='imageset1' value='1' />Image A</><input style='border:0' type='radio' id = 'image1_id_b' name='imageset1' value='2' />Image B</> 
        </fieldset>
        <br><br>
        <br>
        <br>
        <fieldset style='border:0'> 
            <input type='text' name='UID' hidden>
            <input type='text' name='quizcorrect' hidden>
            <input type='text' name='quizAnswer' hidden>
            <input type="submit" value="Submit" onclick='submitQuizClicked()'/>
        </fieldset>
</form>
 </p>

</div>
</body>

<script>
var validInput=0;

function submitQuizClicked() {

    var x = null;
    if (document.getElementById('id1').checked) {
      x="1";
    } else if (document.getElementById('id2').checked) {
      x="2";
    } else if (document.getElementById('id3').checked) {
      x="3";
    } else if (document.getElementById('id4').checked) {
      x="4";
    }

    var im1 = null;

    if (document.getElementById('image1_id_b').checked) {
      im1="b";
    } else if (document.getElementById('image1_id_a').checked) {
      im1="a";
    }
 
    if (x == null || x == "" || im1 ==null ) {
        alert("Please answer all questions.");
        validInput = 0;
        return false;
    } else {
        validInput = 1;
        var u_id = "<?php global $UID;  echo $UID ?>";
 
        if (x!="1" || im1!="b") {
           alert("Incorrect, please read the instructions carefully.");
           document.forms["frm"]["quizcorrect"].value = "no";
        } else {
           document.forms["frm"]["quizcorrect"].value = "yes";
        }

        document.forms["frm"]["quizAnswer"].value = x;
        document.forms["frm"]["UID"].value = u_id;
        //alert(u_id);
        return true;
    }
    return true;
}

function validateForm() {
    return (validInput==1);
}

</script>
</html>
