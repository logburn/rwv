<?php
  session_start();
  //connecting to the db
  require "conn.php";
  
//Getting vars from $_POST
  $formPass = $_POST['password'];
  $user = $_POST["username"];

//checking the username and password
  //Getting the real, hashed password
  $stmt = $conn->prepare("SELECT password FROM users WHERE username=:unm");
  $stmt->bindParam(":unm", $user);
  $stmt->execute();
  $array = $stmt->fetch(PDO::FETCH_ASSOC);
  $hashedPass = $array['password'];
  
  //checking password, starting session, redirecting
  if(password_verify($formPass, $hashedPass)){
    $_SESSION["user"] = $user;
    //getting user level and putting it in url for $_GET
    $stmt = $conn->prepare("SELECT level FROM users WHERE username=:usr");
    $stmt->bindParam(":usr", $user);
    $stmt->execute();
    $array = $stmt->fetch(PDO::FETCH_ASSOC);
    $lvl = $array['level'];
    header("Location: vamos.php?level=".$lvl);
  }else{
    header("Location: signin.php?usrpass=incorrect");
  }
?>