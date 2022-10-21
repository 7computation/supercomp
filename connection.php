<?php

function make_connection() {
  $servername = "localhost";
  $username = "supercomp";
  $password = "";

  try {
    return new PDO("mysql:host=$servername;dbname=$username", $username, $password);
    
  } catch(PDOException $e) {
    echo "Could not connect to database: " . $e->getMessage();
  }
}
?>