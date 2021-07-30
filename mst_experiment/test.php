<!DOCTYPE html>
<html>
<head>
<title>
Experiment</title>
<style>
table, tr, th, td {
    border:1px solid black;
    border-collapse: collapse;
    width:absolute;
    height:absolute;
}
h1 {
    text-align: center;
}

h2{
    text-align: center;
}

#ex1_container { align:center; text-align: center;}

</style>
</head>
<body onload="loadEventHandler()">

<?php

// form parametres
function test_input($data) {
   $data = trim($data);
   $data = stripslashes($data);
   $data = htmlspecialchars($data);
   return $data;
}

// define variables and set to empty values
$UID =  "";
$dir = "att-test";
$steps = 0; 
$practice_dir = "webfile/practice";
$easy_dir="webfile/easy";
//$med_dir="webfile/medium";
//$hard_dir="webfile/hard";

$practice_names = scandir($practice_dir);
//$med_names = scandir($med_dir);
$easy_names = scandir($easy_dir);
//$hard_names = scandir($hard_dir);
$number_of_practice = count($practice_names) - 2;
$num_easy = count($easy_names) - 2;
$num_med = 0; // count($med_names) - 2;
$num_hard = 0; // count($hard_names) - 2;
$num_test = $num_easy; //i + $num_med + $num_hard;

//echo "number_of_practice  $number_of_practice <br>";
//echo "num_easy  $num_easy <br>";
//echo "num_med  $num_med <br>";
//echo "num_hard  $num_hard <br>";

$ip=$_SERVER['REMOTE_ADDR'];
$date = date('d/F/Y h:i:s'); // date of the visit that will be formated this way: 21/May/2011 2512:20:03
$browser = $_SERVER['HTTP_USER_AGENT'];
$browser = str_replace(' ', '_', $browser);
$validrequest = 0;

$randomisedMazeNo = 0; 
$mazeno = 0;
$cellsize = 55;

if (!is_writable($dir)) {
    echo 'The directory is not writable ' . $dir . '<br>';
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {

        $UID = "dodgyuser";
        $s = $dir . "/" . $UID . ".txt";
        $f = fopen($s, "a") or die("GET request received. Unable to open file!" . $s);
	fwrite($f, $ip . "\t". $date . "\t" . $browser . "Invalid request to test.php\n");
        fclose($f);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
   $validrequest = 1;
   $mazeno = 0;

   if (!empty($_POST["UID"])) {
      $UID = test_input($_POST["UID"]);
      if ( !strcmp($UID, "dodgyuser") ) {
        $validrequest = 0;
      }

     // echo $UID;
   } else {
      $validrequest = 0;
      $UID = "dodgyuser";
   }
 
   if (!empty($_POST["quizAnswer"])) {
      $quiz1 = test_input($_POST["quizAnswer"]);
   } 

   if (!empty($_POST["firsttrial"])) {
      $firsttrial = test_input($_POST["firsttrial"]);
   }


   $s = $dir . "/" . $UID . ".txt";
   $f = fopen($s, "a") or die("101 Unable to open file!" . $s);

   if(!empty($firsttrial)) {

      // the first trial; generate a random sequence of 12 for this user
      $m = array();
      for ($x = 0; $x < $num_easy; $x++) {
         $m[] = $x;
      }

      // permute randomly
      for ($x = 0; $x < count($m); $x++) {
        $pickone = rand(0, count($m)-1);
        if ($pickone <> $x) { 
    		$temp = $m[$x];
        	$m[$x] = $m[$pickone];
        	$m[$pickone] = $temp;
        }
      } 

      // save to file

      $f = fopen($dir . "/" . $UID . "sequence.txt", "a") or die("Unable to open file!" . $UID . "sequence");
      for ($x = 0; $x < count($m); $x++) {
         fwrite($f, $m[$x] . "\n");
      }
      fclose($f);
      $randomisedMazeNo = 0;

   }
   else if (!empty($quiz1)) {
        $txt = $ip . " " . $date . " " . $browser .  " " . $UID .  " solvingquiz: " . $quiz1 .  "\n";
        fwrite($f, $txt);
        fclose($f);
        $quizcorrect = test_input($_POST["quizcorrect"]);
        //echo "quiz is correct" . $quizcorrect;
        if ( !strcmp($quizcorrect, "no") ) { 
          $mazeno = 0;
        } else {
          $mazeno = $number_of_practice-1;
          $randomisedMazeNo = $mazeno;
          advanceMazeNo();
        }
   } 
   else {
        if (!empty($_POST["mazeno"])) {
          $mazeno = test_input($_POST["mazeno"]);
          $randomisedMazeNo = $mazeno;
        } else {
          $mazeno = 0;
          $randomisedMazeNo = 0;
          $mfile = $practice_dir . "/" . $practice_names[$randomisedMazeNo + 2 ];//$mazefile[$randomisedMazeNo];
          $txt = "1 of " . $number_of_practice;
        } 
        
        $mazeid = test_input($_POST["mazeID"]);
        $path = test_input($_POST["path"]);
        $time = test_input($_POST["time"]);
        $maze = test_input($_POST["name"]);
        $steps = test_input($_POST["steps"]);
   
        fwrite($f, $ip . " ". $date . " " . $browser . " " . $UID . " " . $maze . " " . $steps . " " . $path . " " . $time . "\n");
        fclose($f);

        // echo "randomisedMazeNo  $randomisedMazeNo <br>";
        advanceMazeNo();
   }
}

// agent location
$agent_x = 0;
$agent_y = 0;

if ($mazeno >=  $number_of_practice  + $num_easy + $num_med  && $mazeno <  $number_of_practice + $num_test ) {
        $mfile = $hard_dir . "/" . $hard_names[$randomisedMazeNo + 2];//$mazefile[$randomisedMazeNo];

} else
if ($mazeno >=  $number_of_practice + $num_easy && $mazeno <  $number_of_practice + $num_easy + $num_med ) {
        $mfile = $med_dir . "/" . $med_names[$randomisedMazeNo + 2];//$mazefile[$randomisedMazeNo];

}
else if ($mazeno >=  $number_of_practice && $mazeno <  $number_of_practice + $num_easy ) {
        $mfile = $easy_dir . "/" . $easy_names[$randomisedMazeNo + 2];//$mazefile[$randomisedMazeNo];

} else {
       $mfile = $practice_dir . "/" . $practice_names[$randomisedMazeNo + 2 ];//$mazefile[$randomisedMazeNo];
}

if ($validrequest == 1) {

  if ($mazeno == 0) {
    echo "<p  align='center'><br>";
    echo "Lets look at this map. There are some black squares, a brick wall, and your character.<br><br>";
    echo "One of these black squares contains the exit. All of the other black squares are empty.";
    echo "<br><br>Please find the exit in as few steps as possible.</p>";
  } else if ($mazeno < $number_of_practice) { 
    $txt = $mazeno+1 . " of " . $number_of_practice;
    $txt = "Practice " . $txt;
    echo "<p  align='center'>Please find the exit in fewest steps.<br></p>";
    echo "<table align='center' style='widthoat:center; border:0px solid white;'><tr style=' border:none'>";
  } else {
    
    $txt = ($mazeno- $number_of_practice +1) . " of " . $num_test;
    echo "<p align='center'>Please find the exit in fewest steps.</p>"; 
    echo "<h2>" . $txt . "</h2>";
  }
} else {
  echo "<h2>Err: Invalid Request</h2>\n";
}


function validatex() {
    return (validInput==1);
}

function advanceMazeNo() {
    global $mazeno, $randomisedMazeNo, $num_easy, $num_med, $num_hard, $UID, $dir,  $number_of_practice;
    $mazeno = $mazeno + 1;
    $randomisedMazeNo = $mazeno;

    // echo "randomisedMazeNo1  $randomisedMazeNo <br>";
    // echo "mazeno $mazeno,  number_of_practice  $number_of_practice <br>";
 
    // is this a practice or a real trial?
    if ($mazeno > $number_of_practice-1) {
         $s = $dir . "/" . $UID . "sequence.txt";
         $f = fopen($s, "r") or die("102: Unable to open file! " . $s);
         
         $temp = $mazeno- $number_of_practice; 

         if ($temp - $num_easy  >= 0) $temp = $temp - $num_easy;
         if ($temp - $num_med >= 0) $temp = $temp - $num_med;
         if ($temp - $num_hard >= 0) $temp = $temp - $num_hard;

         for ($x = 0; $x <= $temp; $x++) {
           $randomisedMazeNo = intval(fgets($f));
           //echo "$randomisedMazeNo <br>";
         }
         fclose($f);
   }

 //  echo "advanced to $randomisedMazeNo <br>";
}

function readWorld($fname) {
        global $worldWidth, $worldHeight, $worldmap, $agent_x, $agent_y;
        $world = fopen( $fname, "r") or die("Unable to open file!" . $fname);
        $worldWidth = fgets($world);
        $worldHeight = fgets($world);
        $agent_x = 0; $agent_y=0;

        // create a world array
        $worldmap = array();

        for ($y = 0; $y < $worldHeight; $y++) {
                $mazeLine = fgets($world);
                $line = str_split($mazeLine);
                $worldmap[$y] = array(); 
                
                for ($x = 0; $x < $worldWidth; $x++) {
                        $worldmap[$y][$x] =  $line[$x];
                        
                        if ($worldmap[$y][$x] == 5) {
                           $agent_x = $x; $agent_y=$y;
                        }
                }
        }
        fclose($world);
}

// echo "<br>reading world $mfile<br>";

readWorld($mfile);

?>

<script>
var ax = <?php global $agent_x;  echo "$agent_x" ?>;
var ay = <?php global $agent_y;  echo "$agent_y" ?>;
var height = <?php global $worldHeight; echo "$worldHeight"?>;
var width = <?php global $worldWidth;  echo "$worldWidth"?>;
var warr = <?php global $worldmap; echo json_encode($worldmap); ?>;

var mn = <?php global $mazeno; echo $mazeno; ?>;
var mnr = <?php global $randomisedMazeNo; echo $randomisedMazeNo; ?>;
var mf = "<?php global $mfile;  echo  $mfile; ?>";
var u_id = "<?php global  $UID; echo  $UID; ?>";
var savedpath = "p(" + ax + "," + ay +  ")";//"p(0,0);";
var savedtime = "";
var valid = <?php global $validrequest;  echo $validrequest; ?>;
var num_test = <?php global $num_test;  echo $num_test; ?>;
var num_practice = <?php global $number_of_practice;  echo $number_of_practice; ?>;
var showEnd = "<?php global $showEndNext; echo $showEndNext; ?>";
var cellsize = "<?php global $cellsize; echo $cellsize; ?>";
var firsttrial = "<?php global $firsttrial; echo $firsttrial; ?>";
jssteps = <?php global $steps; echo $steps;?>;
var progress_step = 1;
var quizcorrect = "<?php global $quizcorrect; echo $quizcorrect; ?>";
var maxsteps = 35;
var oldtime = new Date();

// this does work
// alert(u_id);

var seen = new Array(height);
var visible = new Array(height);

// allocate seen array
for (y = 0; y < height; y++) {
   seen[y] = new Array(width);
   visible[y] = new Array(width);
   for (x = 0; x < width; x++) {
        seen[y][x] = 0;
        visible[y][x] = 0;
        if (parseInt(warr[y][x]) == 6) {
           visible[y][x] = 1;
	}

        if (parseInt(warr[y][x]) == 5) {
           warr[y][x] = "0";
           visible[y][x] = 1;
        }	
   }
}

// 1 -- no rendered boundary, 2 -- treasure, 3 -- wall, 5 - agent starting location, 0 - empty, 6 - open

calculate_seen();


    function timestamp() { 
        var now= new Date(), 
            h= now.getHours(), 
            m= now.getMinutes(), 
            s= now.getSeconds();
        ms = now.getMilliseconds(); 
        return ms + 1000*s + 1000*60*m + 1000*60*60*h; 
    }
 
function calculate_seen() {
  for (y = 0; y < height; y++) {
    for (x = 0; x < width; x++) {
        if (( Math.abs(ax-x) + Math.abs(ay-y)) <= 1){  
          seen[y][x] = 1;
        }
    } 
  }

  for (i = 10; i > 0; i--) {
      isovistLevel(i);
  }

  for (y = 0; y < height; y++) {
    for (x = 0; x < width; x++) {
        if (visible[y][x] == 1 )  {
           seen[y][x] = 1;
        }
    }
  } 
}

function isovistLevel(level) {
    for (px = ax-level; px <= ax+level; px++) {
      addvisible(px, ay-level, level); // top row
      addvisible(px, ay+level, level); // bottom row
    }

    for (py = ay-level; py < ay+level; py++) {
      addvisible(ax+level, py, level);  // right row
      addvisible(ax-level, py, level);  // left row
    }
}

function addvisible(px, py, level) {
    if (px >= 0 && py >=  0 &&  px < width &&  py < height) {
        visible[py][px] = 0;
        var b = true;
        if (level > 1 || (Math.abs(ax-px) + Math.abs(ay-py) > 1) ) {
                if (b) b = checkline(ax+0.1, ay+0.1, px+0.1, py+0.1, level*45.0);
                if (b) b = checkline(ax+0.9, ay+0.1, px+0.9, py+0.1, level*45.0);
                if (b) b = checkline(ax+0.9, ay+0.9, px+0.9, py+0.9, level*45.0);
                if (b) b = checkline(ax+0.1, ay+0.9, px+0.1, py+0.9, level*45.0);
                if (b) visible[py][px] = 1;
        }
        if (b) visible[py][px] = 1; 
    }
}

function checkline(x1, y1, px, py, step) {
    var dx = (x1-px)/step;
    var dy = (y1-py)/step;

    var fx = px+dx;
    var fy = py+dy;
    var x = Math.floor(fx);
    var y = Math.floor(fy);

    do {
      if (px < 0 || py < 0 || px >= width || py >= height ) {
        return true;
      }

      var w = parseInt(warr[y][x]);
      if (w == 3) return false;

      fx +=dx; 
      fy +=dy;      
      x = Math.floor(fx);  
      y = Math.floor(fy);
    } while (x !=Math.floor(x1) || y !=Math.floor(y1));
    return true;
}


function generate_table(wagent) {
    // generate progress
     var bigtable = "<p><table align='center' style='widthoat:center; border:0px solid white;' cellpadding='10' >"+
        "<tr style=' border:none'>";

    healthbar = " <table align='center' style='width:absolute;widthoat:center'>";
    bar = "";
    
    for (x = 0; x < 30; x++) {
        cl = "#00a055";
      if (progress_step > 25) {
        cl = "#f90000";
      } else
      if (progress_step > 22) {
        cl = "#ff0000";
      } else if (progress_step > 19) {
        cl = "#ff5500";
      } else if (progress_step > 16) {
        cl = "#ff9a33";
      } else if (progress_step > 13) {
        cl = "#ffff00";
      } else if (progress_step > 10) {
        cl = "#d5ff33";
      } else if (progress_step > 7) {
        cl = "#a2fa32";
      } else if (progress_step > 4) {
        cl = "#00f030";
      }

      if (x < 30-progress_step+1) {
         bar = "<tr><td bgcolor='" + cl + "' width=25px height=7px></td></tr>" + bar;
      } else {
         bar = "<tr><td bgcolor='#ffffff' width=25px height=7td></tr>" + bar;
      }
     
    }

    healthbar += bar + "</table>";
    var progress = "<p><font size='6' color='#ff0000'>Steps: " +  parseInt(progress_step-1) + "</font></p>";

    var s = bigtable + "<td style=' border:none'>" + healthbar + "</td>" + "<td style=' border:none'>" + progress + " <table align='center' style='width:absolute;widthoat:center'>";

    calculate_seen();


    for (y = 0; y < height; y++) {
      s = s + "<tr>";
      for (x = 0; x < width; x++) {
 
        var w = parseInt(warr[y][x]);
        if ( x == ax && y== ay && wagent!=2) {
               s = s + "<td width=" + cellsize + "px height=" + cellsize +
                        "px><img src='webfile/agent.png' style='width:" + cellsize +
                        "px;height:" + cellsize + "px;display: block;'>";
        } else if ( x == ax && y== ay && wagent==2) {
               s = s + "<td width=" + cellsize + "px height=" + cellsize +
                        "px>"
                       // "px;height:" + cellsize + "px;display: block;'>";
        }
        else if (w == 3) {
                s = s + "<td width=" + cellsize + "px height=" + cellsize + 
                        "px><img src='webfile/brickwall.png' style='width:" + cellsize + 
                        "px;height:" + cellsize + "px;display: block;'>";

        } else if (  ((seen[y][x] == 0 && w ==2) ||  w==5) && wagent !=2)  {
                s = s + "<td bgcolor='#000000' width=" + cellsize + "px height=" + cellsize + "px>";
        } else {

              bkc = "#ffffff";
              sclick = "tableClickedVoid";

              if ( wagent !=2 ) {
                  if (seen[y][x] == 1) {

                        if (x == ax-1 && y == ay) {
                                sclick = "tableClickedXminus";
                        } else if (x == ax+1 && y == ay) {
                                sclick = "tableClickedXplus";
                        } else if (y == ay-1 && x == ax) {
                                sclick = "tableClickedYminus";
                        } else if (y == ay+1 && x == ax) {
                                sclick = "tableClickedYplus";
                        }
 
                        bkc = "#ffffff";
                        if(w == 2) bkc = "#ff0000";
                  } else {
                         bkc = "#000000";
                  }

             }
             
             if (w==2 && seen[y][x] == 1 && wagent !=2) {
                  s = s + "<td bgcolor='#ff0000' width=" + cellsize + "px height=" + cellsize + "px onclick='"; 
                  s = s + sclick + "();'>";
               } else if (w==6) {
                  s = s + "<td bgcolor='#ffffff' width=" + cellsize + "px height=" + cellsize + "px onclick='";
                  s = s + sclick + "();'>";
               } else {
                  s = s + "<td bgcolor='";
                  s = s + bkc;
                  s = s + "' width=" + cellsize + "px height=" + cellsize + "px onclick='";
                  s = s + sclick;
                  s = s + "();'>";
              }
        } 
        s = s + "</td>";
      }
      s = s + "</tr>";
    }

    s = s + "</table>";

    s = s + "</td></table>";
    // alert("generate table"); // when the alerts do not show up it is something like a missing bracket that prevents compiling
    //if (wagent!=2 && progress_step < 29) s = s + "<p  align='center'> <button type=\"button\" disabled>Submit</button></p>";
    return s ;
}

function loadEventHandler() {
  //alert("load");
 
  if (valid == 1) {
	s = generate_table();
	document.getElementById("ex1_container").innerHTML = s;
  }
        
 /* var now= new Date(), 
  h= now.getHours(), 
  m= now.getMinutes(), 
  s= now.getSeconds();
  ms = now.getMilliseconds();

  var times = "t(" + h + "," + m + "," + s + "," + ms + ");";
  savedtime += times;*/
  oldtime = timestamp();  
}

function tableClickedVoid() {

}

function tableClickedXminus() {
 if (progress_step < maxsteps) {
   ax = ax-1;
   tableClicked();
 }
}

function tableClickedXplus() {
 if (progress_step < maxsteps) {
   ax = ax+1;
   tableClicked();
 }
}

function tableClickedYminus() {
 if (progress_step < maxsteps) {
   ay = ay-1;
   tableClicked();
 }
}

function tableClickedYplus() {
 if (progress_step < maxsteps) {
   ay = ay+1;
   tableClicked();
 }
}


function tableClicked() {
        progress_step = progress_step + 1;
	jssteps = jssteps+1;
        savedpath += "p(" + ax + "," + ay + ");";

        var newtime = timestamp();
        var difference = newtime - oldtime;
        oldtime = newtime;
        savedtime = savedtime + difference + ";"; 

        /*var now= new Date(), 
        h= now.getHours(), 
        m= now.getMinutes(), 
        s= now.getSeconds();
        ms = now.getMilliseconds();

        times = "t(" + h + "," + m + "," + s + "," + ms + ");";
        savedtime += times;  
*/
	var w = parseInt(warr[ay][ax]);

        document.getElementById("ex1_container").innerHTML = "<p  align='center'>" + generate_table(w) + "</p>";
        // if reached goal state.
	
       snext = "thanks_solve.php";

        if (w == 2 ) {
                
		if (mn < num_test + num_practice - 1 ) {
                    snext = 'test.php';
                }

                if (mn == num_practice-1) {
                   snext = 'planning_quiz.php';
                }

		document.getElementById("ex1_container").innerHTML += "<p align='center'><form name='frm' action='" + snext + 
                           "' method='post' onsubmit='submitForm()'>" + 
                           "<input type='text' name='name' hidden><input type='text' name='steps' hidden>" + 
                           "<input type=text' name='mazeno' hidden><input type='text' name='UID' hidden>" +      
                           "<input type=text' name='showEndNext' hidden><input type=text' name='firsttrial' hidden>" +
                           "<input type='text' name='path' hidden><input type='text' name='time' hidden><input type='text' name='mazeID' hidden>" +
                           "</form></p>";

                 
                submitForm();
                document.forms["frm"].submit();  
	} 
        
}


function submitForm() {
        document.forms["frm"]["steps"].value = jssteps;       
        document.forms["frm"]["UID"].value = u_id;
        document.forms["frm"]["mazeno"].value = mn;
        document.forms["frm"]["mazeID"].value = mnr;
        document.forms["frm"]["path"].value = savedpath;
        document.forms["frm"]["time"].value = savedtime;
        document.forms["frm"]["name"].value = mf; 
        document.forms["frm"]["showEndNext"].value = showEnd;
        //document.forms["frm"]["firsttrial"].value = "false";
}
</script>

<div id="ex1_container">
</div>

</body>
</html> 
