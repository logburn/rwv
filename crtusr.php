<?php
  session_start();
  //conntecting to database
  require "conn.php";
  
  //setting some vars
  $user = $_POST['username'];
  $pass = $_POST['password'];
  $pass = password_hash($pass, PASSWORD_DEFAULT);
  
//making sure the username isn't already in use
  $stmt = $conn->prepare("SELECT * FROM users WHERE username=:usr");
  $stmt->bindParam(":usr", $user);
  $stmt->execute();
  $array = $stmt->fetch(PDO::FETCH_ASSOC);
  if($array != ""){
    header('Location: signup.php?userexists=true');
  } else {
//inserting the new user
    //preparing statement against injection
    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (:unm, :psw)");
    $stmt->bindParam(":unm", $user);
    $stmt->bindParam(":psw", $pass);
    $stmt->execute();
  
    $_SESSION["user"] = $user;
    //getting user level and putting it in url for $_GET
    $stmt = $conn->prepare("SELECT level FROM users WHERE username=:usr");
    $stmt->bindParam(":usr", $user);
    $stmt->execute();
    $array = $stmt->fetch(PDO::FETCH_ASSOC);
    $lvl = $array['level'];
    header("Location: https://lukeogburn.com/rwv/vamos.php?level=".$lvl);
  }
?>