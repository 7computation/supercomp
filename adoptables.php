<!DOCTYPE html>
<html>
<head>
  <title>Adoptable Database</title>
  <link rel="stylesheet" href="styles/gallerystyle.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<?php

include 'connection.php';

// pagination variables

if(!isset($_GET['page']) ) {  
  $page = 1;  
} else {  
  $page = $_GET['page'];  
}  

$RESULTS_PER_PAGE = 32;
$firstpage = ($page - 1) * $RESULTS_PER_PAGE;

// connection

$entries = array();

$connection = make_connection();
$connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// getting values for dropdowns
$stmt = $connection->prepare('SELECT DISTINCT YEAR(date) FROM adoptables ORDER BY YEAR(date)');
$stmt->execute();
$yearlist = $stmt->fetchAll(PDO::FETCH_COLUMN);

$stmt = $connection->prepare('SELECT DISTINCT series FROM adoptables ORDER BY series');
$stmt->execute();
$serieslist = $stmt->fetchAll(PDO::FETCH_COLUMN);

$stmt = $connection->prepare('SELECT DISTINCT origin FROM adoptables ORDER BY origin');
$stmt->execute();
$originlist = $stmt->fetchAll(PDO::FETCH_COLUMN);

// run this if $_GET is empty (will only happen when gallery is first visited)
if(empty($_GET)){

  // get total count of entries that match
  $sql = 'SELECT COUNT(*) AS totalmatched FROM adoptables';

  $stmt = $connection->prepare($sql);

  $stmt->execute();

  $totalresults = $stmt->fetch(PDO::FETCH_ASSOC);
  $totalresults = $totalresults['totalmatched'];

  // get the actual entries (paginated)
  $sql = 'SELECT * FROM adoptables  
  ORDER BY adoptableid DESC LIMIT :firstpage, :resultsperpage';

  $stmt = $connection->prepare($sql);
  $stmt->bindParam('firstpage', $firstpage, PDO::PARAM_INT);
  $stmt->bindParam('resultsperpage', $RESULTS_PER_PAGE, PDO::PARAM_INT);

  $stmt->execute();

  $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// run this if submit button is pressed
if(!empty($_GET)){

  // count total entries that matches
  $sql = 'SELECT COUNT(*) AS totalmatched FROM adoptables 
  WHERE YEAR(date) = IFNULL(NULLIF(:year, ""), YEAR(date)) 
    AND series = IFNULL(NULLIF(:series, ""), series) 
    AND origin = IFNULL(NULLIF(:origin, ""), origin) 
    AND adoptableid = IFNULL(NULLIF(:specificid, ""), adoptableid)';

  // values collected from form. if the key exists, it get's the key's value, if not, it is empty
  $year = isset($_GET['year']) ? $_GET['year'] : "";
  $series = isset($_GET['series']) ? $_GET['series'] : "";
  $origin = isset($_GET['origin']) ? $_GET['origin'] : "";
  $specificid = isset($_GET['specificid']) ? $_GET['specificid'] : "";

  $stmt = $connection->prepare($sql);
  $stmt->bindParam('year', $year, PDO::PARAM_STR);
  $stmt->bindParam('series', $series, PDO::PARAM_STR);
  $stmt->bindParam('origin', $origin, PDO::PARAM_STR);
  $stmt->bindParam('specificid', $specificid, PDO::PARAM_INT);

  $stmt->execute();
  $totalresults = $stmt->fetch(PDO::FETCH_ASSOC);
  $totalresults = $totalresults['totalmatched'];

  // if a field is empty, then it ignores the parameter/doesn't try to match a value
  $sql = 'SELECT * FROM adoptables 
  WHERE YEAR(date) = IFNULL(NULLIF(:year, ""), YEAR(date)) 
    AND series = IFNULL(NULLIF(:series, ""), series) 
    AND origin = IFNULL(NULLIF(:origin, ""), origin) 
    AND adoptableid = IFNULL(NULLIF(:specificid, ""), adoptableid) 
      
  ORDER BY adoptableid DESC LIMIT :firstpage, :resultsperpage';

  // add pagination parameters

  $stmt = $connection->prepare($sql);
  $stmt->bindParam('year', $year, PDO::PARAM_STR);
  $stmt->bindParam('series', $series, PDO::PARAM_STR);
  $stmt->bindParam('origin', $origin, PDO::PARAM_STR);
  $stmt->bindParam('specificid', $specificid, PDO::PARAM_INT);
  $stmt->bindParam('firstpage', $firstpage, PDO::PARAM_INT);
  $stmt->bindParam('resultsperpage', $RESULTS_PER_PAGE, PDO::PARAM_INT);

  $stmt->execute();
  $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

}

$connection = null;

// pagination

$totalpages = ceil($totalresults / $RESULTS_PER_PAGE);

// function that adds query parameters to url

function parameters_to_url() {

  $paramurl = "";

  if(isset($_GET['year'])){
    $paramurl = $paramurl . "&year=" . urlencode($_GET['year']);
  } elseif(!isset($_GET['page'])){
    $paramurl = $paramurl . "&year=";
  }

  if(isset($_GET['series'])){
    $paramurl = $paramurl . "&series=" . urlencode($_GET['series']);
  } elseif(!isset($_GET['page'])){
    $paramurl = $paramurl . "&series=";
  }

  if(isset($_GET['origin'])){
    $paramurl = $paramurl . "&origin=" . urlencode($_GET['origin']);
  } elseif(!isset($_GET['page'])){
    $paramurl = $paramurl . "&origin=";
  }

  if(isset($_GET['specificid'])){
    $paramurl = $paramurl . "&specificid=" . urlencode($_GET['specificid']);
  } elseif(!isset($_GET['page'])){
    $paramurl = $paramurl . "&specificid=";
  }

  return $paramurl;

}

?>

<!-- visuals -->
<div id="main">

<div id="topcontainer">
  <div id="leftcol">

    <div style="width: 50%;">
      <a href="./" class="link"><< back</a>
      <h2>adoptable database</h2>

      <form action="adoptables" method="GET">
        <label for="year">year</label>
        <select id="year" name="year">
          <option value=""></option>

          <!-- create options from all values used in database -->
          <?php foreach($yearlist as $year): ?>
            <option value="<?=$year?>"><?=$year?></option>
          <?php endforeach ?>
        </select><br>

        <label for="series">series</label>
        <select id="series" name="series">
          <option value=""></option>

          <!-- create options from all values used in database -->
          <?php foreach($serieslist as $series): ?>
            <option value="<?=$series?>"><?=$series?></option>
          <?php endforeach ?>
        </select><br>

        <label for="origin">origin</label>
        <select id="origin" name="origin">
          <option value=""></option>

          <!-- create options from all values used in database -->
          <?php foreach($originlist as $origin): ?>
            <option value="<?=$origin?>"><?=$origin?></option>
          <?php endforeach ?>
        </select><br>

        <label for="specificid">ID</label>
        <input type="text" id="specificid" name="specificid"><br>

        <input type="submit" value="search" >
      </form>

      <!-- results counter -->
      <?php

      if (!$entries) {
        echo "<p>No results found!</p>";
      }elseif($totalresults == 1){
        echo "<p>" . strval($totalresults) . " result found</p>";
      }else {
        echo "<p>" . strval($totalresults) . " results found</p>";
      }

      ?>
    </div>

      <p id="talkypara">this is a directory of character designs i have made for other people, through adoptable auctions, sales, raffles, customs, and so on.
        all of these characters already have owners, please do not contact me to offer on them. if you are interested in acquiring a design by me, 
        i recommend keeping an eye on my <a style="color:rgb(79, 79, 79);text-decoration:underline;" href="https://twitter.com/7COMPUTATION">twitter</a>, as that is where i do most sales.
      </p>

  </div>

  <div>
    <img id="clickyimage" src="dynaclick/placeholder1.png">

    <!-- script for clicky image -->
    <script>
      var clickyimage = document.getElementById("clickyimage");
      var i = 0;
      clickyimage.onclick = function(){
        i++;
        if (i <= 1 || i == 3){
          clickyimage.src = "dynaclick/placeholder2.png";
        } else if (i == 2 || i == 4){
          clickyimage.src = "dynaclick/placeholder1.png";
        } else if (i > 4) {
          clickyimage.src = "dynaclick/placeholder3.png";
        }
        
      };
    </script>

  </div>

</div>

<!-- entries put on page -->

<div id="entries">
  <?php foreach($entries as $entry): ?>
  <?php if (file_exists('adoptimg/' . $entry["filepath"])): ?>
      <a href="adoptable/<?=$entry["adoptableid"]?>">
        <img src="<?='adoptimg/' . $entry["filepath"]?>" class="galleryimage">
      </a>
  <?php endif ?>
  <?php endforeach ?>
</div>

<!-- pagination pages controller -->

<div id="pagination">

  <?php if($totalpages > 1): ?>
    
    <?php 
    if(!isset($_GET['page']) ) {  
      $page = 1;  
    } else {  
      $page = $_GET['page'];  
    }  

    if($page > 1){
      echo "<a href='adoptables?page=" . ($page-1) . htmlentities(parameters_to_url()) . "'>prev</a>";}
    ?>

    <?php for($i = 1; $i <= $totalpages; $i++): ?>
      <?php 
      if($i == $page){
       echo "<a id='active' href='adoptables?page=" . $i . htmlentities(parameters_to_url()) . "'>" . $i . "</a>";
      }else {
        echo "<a href='adoptables?page=" . $i . htmlentities(parameters_to_url()) . "'>" . $i . "</a>";
      }
      ?>
    <?php endfor ?>

    <?php 
    if(!isset($_GET['page']) ) {  
      $page = 1;  
    } else {  
      $page = $_GET['page'];  
    }  

    if($page < $totalpages){
      echo "<a href='adoptables?page=" . ($page+1) . htmlentities(parameters_to_url()) . "'>next</a>";}
    ?>

  <?php endif ?>

</div>

</div>

</body>
</html>