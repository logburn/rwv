<?
  session_start();
  //connecting to the db
  require "conn.php";
  
  $user = $_SESSION["user"];
  
  $stmt = $conn->prepare("SELECT * FROM users WHERE username = :usr");
  $stmt->bindParam(":usr", $user);
  $stmt->execute();
  $userInfo = $stmt->fetch(PDO::FETCH_OBJ);
  
  function del(){
    $stmt = $conn->prepare("DELETE FROM users WHERE username=:usr");
    $stmt->bindParam(":usr", $user);
    $stmt->execute();
    if($stmt){
      header('Location: ');
    }else{
      header('Location: ?act=error');
    }
  }
  function res(){
    $stmt = $conn->prepare("UPDATE users SET level = 1 WHERE username = :usr");
    $stmt->bindParam(":usr", $user);
    $stmt->execute();
    if($stmt){
      header('Location: ?act=done');
    }else{
      header('Location: ?act=error');
    }
  }
  
  if(isset($_GET["act"])){
    $act = $_GET["act"];
    switch($act){
        case "del":
            del();
            break;
        case "res":
            res();
            break;
        case "error":
            echo "<h1>Something went wrong</h1>";
            break;
        default:
            echo "<h1>Something went wrong</h1>";
            break;
    }
  }
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Riddles Without Vitae</title>
    <link rel="stylesheet" type="text/css" href="user.css" />	
    <meta name="viewport" content="width=device-width,initial-scale=1">
  </head>
  <body>
    <h1><?=$user?></h1>
    <p>This is your profile page. There will never be clues here.</p>
    <p>You are on <a href=/rwv/vamos.php?level=<?=$userInfo->level?>>level <?=$userInfo->level?></a>.</p>
    <br>
    <br>
    <h2>Danger Zone</h2>
    <h3><a id=del href="?act=del">Delete account</a></h3>
    <h3><a id=res href="?act=res">Reset progress</a></h3>
  </body>
</html>