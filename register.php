<?php
  include_once './includes/dbprocess.php';

  if(isset($_SESSION['isLoggedin'])){
    header("Location: ./SEA/dashboard.php");
  }else{
      
  }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
    <link rel="stylesheet" href="./styles/sweetalert.css">
    <link rel="stylesheet" href="./styles/register.css">
    <title>Single Entry Accounting</title>
    <link rel="shortcut icon" type="image/png " href="./img/logo.png">


        
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="./js/sweetalert.min.js"></script>

    </head>

    <body>
            <div class="split-screen">
                <img src="./assets/img/gatercy.jpg" alt="">
                <div class="left">
                    <section class="copy">
                        <h2>Keep on track on your business</h2>
                        <p>Easy to navigate single entry accounting system</p>
                    </section>

                </div>
                <div class="right">
                            <form autocomplete="off" class="forms" id="forms" action="./includes/dbprocess.php" method="POST">
                                <section class="copy sign">
                                    <h2 class="sign">Sign Up</h2>
                                    
                                </section>
                                <div class="login-container">
                                        <p style="color:rgb(87, 87, 87)">Already have an account? <a href="index.php">Log in</a></p>
                                    </div>
                                <div class="input-container name" >
                                    <label for="fname">Full Name</label> 
                                    <input autocomplete="off" type="text"  id="fname" name="fname" onkeypress="return OnAlert(event)" onkeydown=" NowAlert();" required>
                                    <small id="alert" class="alert"></small>
                                    <i class='bx bx-check-shield' ></i>
                                    <i id="error" class='bx bx-error-alt'></i>
                                    
                                </div>
                                <div class="input-container name">
                                    <label for="bname">Business Name</label>
                                    <input autocomplete="off" type="text"  id="bname" name="bname" required">
                                    <small id="alert"></small>
                                    <i class='bx bx-check-shield' ></i>
                                    <i class='bx bx-error-alt'></i>
                                </div>
                                <div class="input-container name">
                                    <label for="email">Email</label>
                                    <input autocomplete="off" type="email"  id="email" name="email" required placeholder="sample@gmail.com">
                                    <i class='bx bx-error-alt'></i>
                                    <i class='bx bx-check-shield' ></i>
                                </div>
                                <div class="input-container password">
                                    <label for="password">Password</label>
                                    <input autocomplete="off" type="password"  id="password" name="password" placeholder="Must be at least 6 characters" minlength="6" required>
                                    <i style="cursor: pointer;" class='bx bx-hide' id="eye" aria-hidden="true" onclick="toggle();"></i>
                                </div>
                               
                                <div class="input-container cta">
                                        <button  type="submit" class="signup-btn" name="signup_btn">Sign up </button>
                                </div>
                              
                                    <br>
                                    <div class="check">
                                    <input type="checkbox" name="" id="" required>
                                    <p>By continuing, you accept to agree out <br>
                                    </div>
                                  
                                    <p class="terms"> <a  href="">Privacy policy</a> <small>&amp; </small> <a href="">Terms of service</a></p>
                              
                            </form>

                </div>

            </div>

<script>

        function OnAlert(evt){

            var error = document.getElementById('error');
            var name = document.getElementById('fname');
            var str  = document.getElementById("fname").value;
            var Alert = document.getElementById("alert");
         // Only ASCII character in that range allowed
                            var ASCIICode = (evt.which) ? evt.which : evt.keyCode
                                 if ((ASCIICode < 65 || ASCIICode > 90) && (ASCIICode < 97 || ASCIICode > 122)){

                                     
                                    if((ASCIICode == 32 || ASCIICode == 45)){
                                        return true;
                                     }else{
                                            Alert.style.display="block";
                                        Alert.innerHTML="Only Alphabets allowed";
                                        Alert=false;
                                            error.style.display="block";
                                            error.style.color="#e74c3c";
                                        return false;
                                     }
                                     
                                 }else{
                                   
        
                                    return true;
                                 }
                                                              
        }
        

        
        
        </script>


<script>

        function NowAlert(){

        var error = document.getElementById('error');
        var name = document.getElementById('fname');
        var str  = document.getElementById("fname").value;
        var Alert = document.getElementById("alert");
            
        if(!((/^[a-zA-Z ]+$/.test(str)) || str.length == 0)) {
         name.style.color="#e74c3c";
         Alert.style.display="block";
         Alert.innerHTML="Only Alphabets allowed";
         Alert=false;
            error.style.display="block";
            error.style.color="#e74c3c";

        }else{

            Alert.style.display= "block";
             Alert.innerHTML="";
             name.style.color="black";
             error.style.display="none";
              Alert=true;
        }
      

        

        }
        
        
        </script>



        
        


                <div class="image">

                </div>





            <?php 
            if (isset($_SESSION['response']) && $_SESSION['response'] !='') { ?>

            <script>
            swal({
                title: "<?php echo $_SESSION['response']?>",
                icon: "<?php echo $_SESSION['res_type']?>",
                button: "Done",
            });
            </script>
        
            <?php
                 unset($_SESSION['res_type']); 
                unset($_SESSION['response']); }
            ?>



</body>
<script src="https://kit.fontawesome.com/a81368914c.js"></script>
    <script src="./js/app.js"></script>
    <script src="./js/script.js" defer></script>
<<<<<<< HEAD
    <script src="./js/valid.js"></script>

    
=======
>>>>>>> 0f85fe3cddec4696cb6f9318a5a6efe57546b1ac
</html>


		

		