<?php  ob_start(); 
include_once '../includes/dbprocess.php';
?>


<!DOCTYPE html>
<html lang="en">
  <head>
  <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/css/tables.css">
    <link rel="stylesheet" href="./assets/css/manager.css">
    <!-- Boxicons CDN Link -->
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    

     <!-- CHARTS -->
     <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
     <script type="text/javascript" src="https://www.google.com/jsapi"></script>


    
     <title>Single Entry Accounting</title>
    <link rel="shortcut icon" type="image/png " href="../img/logo.png">


    <link rel="stylesheet" href="../styles/sweetalert.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="../js/sweetalert.min.js"></script>


   </head>
   <body>
   <header class="header">
            <div class="header__container">
       
            
            <h3 style="color: #00a98f;"><?php echo $_SESSION['business_owner']?></h3>
           
                <a href="../admin_home.php" class="header__logo"> <span></span></a>
            
           
    
                <div class="header__toggle">
                    <i style="color:#fc5c9c;" class='bx bx-menu' id="header-toggle"></i>
                </div>

            </div>
    </body>
  
</html>