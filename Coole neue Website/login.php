<?php
session_start();
include 'dbh.php';
$username = $_POST['username'];
$pwd = $_POST['pwd'];
$stmt = $conn->prepare("SELECT * FROM `user` WHERE `username` = (?)");
$stmt->bind_param("s", $username);
//execute
$stmt->execute();
// bind result variables
$stmt->bind_result($fetched_id, $fetched_username, $fetched_pwd);
//$result = $stmt->get_result();
//$row = $result->fetch_assoc();

while($stmt->fetch()) {
    if (password_verify($pwd, $fetched_pwd)) {
        //login erfolgreich
        $_SESSION['id'] = $fetched_id;
        $_SESSION['debug'] = $_POST['debug'];
        header("Location: index.php");
        $stmt->close();
        $conn->close();
    }
}
  die('<h4>Benutzername oder Passwort ungültig!</h4> <br>
      Bitte erneut <a href="index.php"> einloggen</a>');
  //die("Error number".$stmt->errno." : ".$stmt->error);
  $stmt->close();
  
  $conn->close();
?>