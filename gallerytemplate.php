<!DOCTYPE html>
<html>

<?php 
// use id to get values from database, so that people can't mess with the values in the template...

include 'connection.php';

$connection = make_connection();
$connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = 'SELECT * FROM adoptables WHERE adoptableid = :specificid LIMIT 1';

$stmt = $connection->prepare($sql);

$specificid = $_GET['specificid'];
$stmt->bindParam('specificid', $specificid, PDO::PARAM_STR);

$stmt->execute();
$entry = $stmt->fetch(PDO::FETCH_ASSOC);

$year = date("Y", strtotime($entry["date"]));
$series = $entry["series"];
$origin = $entry["origin"];
$filepath = 'adoptimg/' . $entry["filepath"];

$connection = null;

if(empty($entry)){
    header("Location: ../notfound");
    exit;
}

?>


<head>

    <title>Adoptable #<?=$specificid?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/gallerystyle.css">

    <style>
        h3 {
            padding-top: 20px;
            padding-bottom: 5px;
        }
        p {
            padding-bottom: 5px;
        }
        img {
            padding-top: 5px;
            width: 100%;
            image-rendering: crisp-edges;
        }
    </style>

</head>
<body>

<div id="main">
    <a href="../adoptables" class="link"><< back to adoptable database</a>
    <br>
    <h3>ID: <?=$specificid?></h3>
    <p>Year: <?=$year?></p>
    <p>Series: <?=$series?></p>
    <p>Origin: <?=$origin?></p>

    <img src="../<?=$filepath?>">
</div>

</body>
</html>