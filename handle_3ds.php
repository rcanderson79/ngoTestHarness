<?php

  $out_acsurl = "";
  $out_acspareq = "";
  $out_acsmd = "";
  $out_acsterm = "";

  $in_acspares = "";
  $in_acsmd = "";

  if (!empty($_GET)) {

    if (isset($_GET['acsUrl'])) { $out_acsurl = $_GET['acsUrl']; }
    if (isset($_GET['PaReq'])) { $out_acspareq = $_GET['PaReq']; }
    if (isset($_GET['MD'])) { $out_acsmd = $_GET['MD']; }
    if (isset($_GET['TermUrl'])) { $out_acsterm = $_GET['TermUrl']; }
    
  }

  if (!empty($_POST)) {
    
    if (isset($_POST['PaRes'])) { $in_acspares = $_POST['PaRes']; }
    if (isset($_POST['MD'])) { $in_acsmd = $_POST['MD']; }
    
  }
  
?>

<html>
  
  <head>
    
    
  </head>

    <?php if (!empty($_GET)) : ?>
  
    acsUrl: <?php echo($out_acsurl); ?> <br>
    
    <form action="<?php echo($out_acsurl); ?>" method="POST" enctype="application/x-www-form-urlencoded">

      PaReq: <input type="text" id="PaReq" name="PaReq" value="<?php echo($out_acspareq); ?>"><br>
      TermUrl: <input type="text" id="TermUrl" name="TermUrl" value="<?php echo('https://pullingteeth.co.uk/network/handle_3ds.php'); ?>"><br>
      MD: <input type="text" id="MD" name="MD" value="<?php echo($out_acsmd);  ?>"><br>
      
      <input type="submit">
      
    </form>
  
    <?php elseif (!empty($_POST)) :?>
  
      PaRes: <input type="text" id="PaRes" name="PaRes" value="<?php echo ($in_acspares); ?>"><br>
      MD: <input type="text" id="MDRes" name="MDRes" value="<?php echo ($in_acsmd); ?>"><br>
  
    <?php else : ?>

       <center>[ Awaiting 3DS ]</center>

    <?php endif; ?>
  
  </body>
  
</html>

