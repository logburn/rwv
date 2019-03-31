<?php
  session_start();
  require "conn.php";
  
  //Getting rid of error messages
  $guess = "whatever, just can't be an answer";
  
  $urlLevel = $_GET['level'];
  $urlLevel = intval($urlLevel);
  
  $user = $_SESSION["user"];
  $stmt = $conn->prepare("SELECT level, stamp FROM users WHERE username=:usr");
  $stmt->bindParam(":usr", $user);
  $stmt->execute();
  $array = $stmt->fetch(PDO::FETCH_ASSOC);
  $level = $array['level'];
  $level = intval($level);
  $stamp = $array['stamp'];
  $timeDiff = time()-strtotime($stamp);
  $minsDiff = $timeDiff/60;
  
  //SQL level crap
  $stmt = $conn->prepare("SELECT password, image, title, paragraph, comment, unoxidized FROM root WHERE level=:lvl");
  $stmt->bindParam(':lvl', $urlLevel);
  $stmt->execute();
  //getting the level info returned by sql
  $levelArray = $stmt->fetch(PDO::FETCH_ASSOC);
  
  //Actual useful info ($user, $level already defined)
  $pass = $levelArray['password'];
  $head = $levelArray['title'];
  $paragraph = $levelArray['paragraph'];
  $image = $levelArray['image'];
  $comment = $levelArray['comment'];
  $unoxidized = $levelArray['unoxidized'];
  
  //amount of guesses the user has left
  $stmt = $conn->prepare("SELECT * FROM users WHERE username=:unm");
  $stmt->bindParam(":unm", $user);
  $stmt->execute();
  $guessesArray = $stmt->fetch(PDO::FETCH_ASSOC);
  $guesses = $guessesArray['guesses'];
  $menos = false; //I guess this belongs here, with $guesses
  
  //Variables not reliant on input
  $next = intval($level)+1;
  $urlNext = $urlLevel+1;
  $suffix = ".jpg";
  //making the $suffix .jpeg instead of .jpg if the .jpeg exists
  if(file_exists($_SERVER['DOCUMENT_ROOT'].'/i/'.$image.'.jpeg')){
    $suffix = '.jpeg';
  }
  
  //Making the $numeral
  switch($urlLevel){
    case 1:  $numeral = "I";       break;
    case 2:  $numeral = "II";      break;
    case 3:  $numeral = "III";     break;
    case 4:  $numeral = "IIII";    break;
    case 5:  $numeral = "V";       break;
    case 6:  $numeral = "VI";      break;
    case 7:  $numeral = "VII";     break;
    case 8:  $numeral = "VIII";    break;
    case 9:  $numeral = "IX";      break;
    case 10: $numeral = "X";       break;
    case 11: $numeral = "XI";      break;
    case 12: $numeral = "XII";     break;
    case 13: $numeral = "XIII";    break;
    case 14: $numeral = "XIV";     break;
    case 15: $numeral = "XV";      break;
    case 16: $numeral = "XVI";     break;
    case 17: $numeral = "XVII";    break;
    case 18: $numeral = "XVIII";   break;
    case 19: $numeral = "XIX";     break;
    case 20: $numeral = "XX";      break;
    case 21: $numeral = "XXI";     break;
    case 22: $numeral = "XXII";    break;
    case 23: $numeral = "XXIII";   break;
    case 24: $numeral = "XXIV";    break;
    case 25: $numeral = "XXV";     break;
    case 26: $numeral = "XXVI";    break;
    case 27: $numeral = "XXVII";   break;
    case 28: $numeral = "XXVIII";  break;
    case 29: $numeral = "XXIX";    break;
    case 30: $numeral = "XXX";     break;
    default: $numeral = "[error]"; break;
  }
  
  //resetting the $guesses if it's been two hours
  if($minsDiff>=120 && $guesses<0){
    $guesses = 50;
    $stmt = $conn->prepare("UPDATE users SET guesses=$guesses WHERE username=:unm");
    $stmt->bindParam(":unm", $user);
    $stmt->execute();
  }
  if(isset($_POST['guess']) && $guesses>0){
   //updating the user's timestamp
   $stmt =$conn->prepare("UPDATE users SET stamp=now() WHERE username = :usr");
   $stmt->bindParam(":usr", $user);
   $stmt->execute();
    
   //getting $guess as lowercase
    $guess = $_POST['guess'];
    $guess = strtolower($guess);

    /*Egg checking*/    
    for($n=1; $n<8; $n++){
      $eggToCheck = "egg".$n;
      $stmt = $conn->prepare("SELECT egg".$n." FROM eggs WHERE level=:url");
      $stmt->bindParam(":url", $urlLevel);
      $stmt->execute();
      $eggArray = $stmt->fetch(PDO::FETCH_ASSOC);
      $egg = $eggArray[$eggToCheck];
      if($guess===$egg){
        //fetching eggtext
        $stmt = $conn->prepare("SELECT eggtext".$n." FROM eggs WHERE level=:url");
        $stmt->bindParam(":url", $urlLevel);
        $stmt->execute();
        $eggtextArray = $stmt->fetch(PDO::FETCH_ASSOC);
        $eggtext = $eggtextArray['eggtext'.$n];
        $menos = true;
        $n = 8; //Ending the loop to only give the one egg
      }
    }
  }
  //Letting the users skip through their solved levels
  elseif(intval($urlLevel)<intval($level)){
    $eggtext = "You've solved this.<br>Want the <a href='vamos.php?level=" . intval(intval($urlLevel)+1) . "'>next level</a> or your <a href='vamos.php?level=".intval($level)."'>current level?</a>";
  }
  //guesses limit and password checking
  if($guesses<1){
    $eggtext = "You're out of guesses. Come back in ".(120-round($minsDiff, 0))." minutes.";
  }elseif($pass===$guess){
    //guesses should be updated reguardless of level acheived
    $stmt = $conn->prepare("UPDATE users SET guesses=50 WHERE username=:unm");
    $stmt->bindParam(":unm", $user);
    $stmt->execute();
    //Making sure the user is on their current level before advancing them
    if(intval($urlLevel)===intval($level)){
      $stmt = $conn->prepare("UPDATE users SET level=$next WHERE username=:unm");
      $stmt->bindParam(":unm", $user);
      $stmt->execute();
    }
    header('Location: vamos.php?level='.$urlNext);
  }elseif($menos === false && $guess!=null) {
        $guesses -= 1;
        if($guesses===0){
          $stmt =$conn->prepare("UPDATE users SET stamp=now() WHERE username = :usr");
          $stmt->bindParam(":usr", $user);
          $stmt->execute();
          $guesses-=1;
          $eggtext = "You're out of guesses. Try again in two hours!";
        }
        //update the user's table
        $stmt = $conn->prepare("UPDATE users SET guesses=$guesses WHERE username=:unm");
        $stmt->bindParam(":unm", $user);
        $stmt->execute();
        //give eggtext
        if($guesses>0){
          $eggtext = "Guesses left: ".$guesses;
        }
      }
      
    //End of Riddles
    if(intval($level) === 26 && intval($urlLevel) === 26){
    $eggtext="So what will it be?";
    if(strpos($guess, "blue")!==false){
      header('Location: bluepill.html');
    }elseif(strpos($guess, "red")!==false){
      header('Location: redpill.html');
    }
  }
    
      //making sure user is signed in, then checking for cheating
    if($user === null){
      header('Location: signin.php');
    }elseif (intval($urlLevel)>intval($level)){
      header('Location: cheater.html');
    }
  
  //CSS
  $css="rwv.css";
?>
<?php if($unoxidized!=null){echo "<!--".$unoxidized."-->";} ?>

<html>
<head>
	<meta charset='utf-8'>
	<title>Riddles Without Vitae</title>
  <link rel='icon' href='/i/favicon.ico' type='image/x-icon'>
  <link rel='stylesheet' href=style.css type='text/css' />
</head>
 <body>
	<div id='left'>
	  <img src='<?php echo "i/".$image.$suffix; ?>'>
	  <div id=info>
	    <h3 id="ll">Level: <?php echo $numeral; ?></h3>
	    <h3 id="lr"><?php echo $user; ?></h3>
	  </div>
	</div>
	<div id='right'>
	  <h2><?php echo $head; ?></h2>
	  <p><?php echo $paragraph;?></p>
	  <!--Answer form-->
	  <div id="answer">
	    <h3 id="eggy"><?php echo $eggtext; ?></h3>
	    <form method='POST' action=''>
	      <input id="guess" size="40" type='text' name='guess' autocomplete=off autofocus>
	    </form>
	  </div>
	</div>
 </body>
</html>
<?php if($comment!=null){echo "<!--".$comment."-->";} ?>