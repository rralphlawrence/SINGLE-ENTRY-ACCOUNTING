<?php 


include_once '../includes/dbprocess.php';

if(isset($_SESSION['isLoggedin'])){
  
}else{
  header("Location: ../index.php");
}



include 'includes/header.php'; 

include 'includes/menu.php'; ?>


<body>

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


        <?php       
                    $month = $_SESSION['month_is'];
                    $year = $_SESSION['year_is'];
                    $Bname =  $_SESSION['business_name'];

                    $query = "SELECT  `details` FROM `tblincomestatement` WHERE `date_month` = '$month' AND `date_year` = '$year' AND `business_name` = '$Bname'";    
                    $stmt = $conn->prepare($query);
                    $stmt-> execute();
                    $result = $stmt->get_result();  
                    $details = "NO DATA AVAILABLE";
                    while ($row = $result->fetch_assoc()) { 
                      $details   = $row['details'];
                    }
                    
        ?>  


        <div class="title-top">

                <div class="title-wrapper">
                <h2><?php echo $_SESSION['business_name']?></h2>
                </div>
        <div class="title-wrapper">
                    <h2>Income Statement</h2>
                    <p><?php echo $details?></p>
                </div>
        </div>

        <div class="button-wrapper">
        <button data-modal-target="#modal3" class="buttonss views">
            <span class="button__texts">VIEW IS</span>
            <span class="button__icons">
            <ion-icon name="wallet-outline"></ion-icon>
             </span>
        </button>
        </div>



        
        <?php       
                    $month = $_SESSION['month_is'];
                    $year = $_SESSION['year_is'];
                    $Bname =  $_SESSION['business_name'];

                    $query = "SELECT `description`, `amount` FROM `tblincomestatement` WHERE `date_month` = '$month' AND `date_year` = '$year' AND `business_name` = '$Bname' AND `category` = 'INCOME'";    
                    $stmt = $conn->prepare($query);
                    $stmt-> execute();
                    $result = $stmt->get_result();  
                    
        ?>  

        <table class="content-table income-table">
            <thead>
                <tr>
                <th>INCOME</th>
                <th>AMOUNT</th>
                </tr>
            </thead>
            <tbody>

              <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                <td><?= $row['description'] ?></td>
                <td><strong>₱ </strong><?= number_format($row['amount']) ?></td>
                </tr>
            <?php } ?>

            <?php       
                    $query = "SELECT  SUM(amount) AS total_amount FROM `tblincomestatement` WHERE `date_month` = '$month' AND `date_year` = '$year' AND `business_name` = '$Bname' AND `category` = 'INCOME'";    
                    $stmt = $conn->prepare($query);
                    $stmt-> execute();
                    $result = $stmt->get_result();  
                    
            ?>  
               
              
               <tr style="background: #fef4a9;">
                    <th style="color:#009879; text-align:left;" class="total">TOTAL INCOME</th>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                    <td ><strong>₱ </strong><?= number_format($row['total_amount']) ?></td>
                    <?php $totalincome = $row['total_amount']; } ?>
                </tr>
             
           
                
            </tbody>
            </table>

            <?php       
                    $query = "SELECT `description`, `amount` FROM `tblincomestatement` WHERE `date_month` = '$month' AND `date_year` = '$year' AND `business_name` = '$Bname' AND `category` = 'EXPENSES'";    
                    $stmt = $conn->prepare($query);
                    $stmt-> execute();
                    $result = $stmt->get_result();  
                    
            ?>
            <table class="content-table income-table">
            <thead>
                <tr>
                <th>EXPENSES</th>
                <th>AMOUNT</th>
                </tr>
            </thead>
            <tbody >

              <?php while ($row = $result->fetch_assoc()) { ?>
              <tr>
              <td><?= $row['description'] ?></td>
              <td><strong>₱ </strong><?= number_format($row['amount']) ?></td>
              </tr>
              <?php } ?>
              

              <?php       
                    $query = "SELECT  SUM(amount) AS total_amount FROM `tblincomestatement` WHERE `date_month` = '$month' AND `date_year` = '$year' AND `business_name` = '$Bname' AND `category` = 'EXPENSES'";    
                    $stmt = $conn->prepare($query);
                    $stmt-> execute();
                    $result = $stmt->get_result();  
                    
              ?>  
               
               

              <tr style="background: #fef4a9;">
                    <th style="color:#009879; text-align:left;" class="total">TOTAL EXPENSES</th>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                    <td ><strong>₱ </strong><?= number_format($row['total_amount']) ?></td>
                    <?php   $totalexpenses = $row['total_amount'];} ?>
           
                </tr>

                  <?php 

                  if($totalincome > $totalexpenses){
                    $sum = $totalincome - $totalexpenses;
                    $sum1 =  $totalincome - $totalexpenses;
                  }else{
                    $sum1 =  $totalincome - $totalexpenses;
                    $sum =  $totalexpenses - $totalincome;
                  }

                  
 
                    if($sum1 < 0){
                      ?>
                      <tr style="background-color: #F25E5E;">
                      <th style="color:#fff; text-align:left;" class="total">
                      <?php echo "NET LOSS"; ?>
                      </th>
                      <td style="color:#fff" ><strong>₱ </strong> <?php echo "(".number_format($sum).")"?></td>
                      </tr>
                      <?php
                    }else{
                      ?>
                      <tr style="background:#a7ff83;">
                      <th style="color:#009879; text-align:left;" class="total">
                      <?php echo "NET PROFIT"; ?>
                      </th>
                      <td ><strong>₱ </strong> <?= number_format($sum)?></td>
                      </tr>
                  <?php }?>

                


            </tbody>
      </table>
  
            <div class="button-footer">
        <form action="../includes/dbprocess.php" Method="POST"> 
        <button type="submit" name="print_IS" class="buttonss">
            <span class="button__texts">PRINT</span>
            <span class="button__icons">
            <ion-icon name="receipt-outline"></ion-icon>
        </span>
        </button>
        </form>
</div>



<div class="modal3" id="modal3">
    <div class="modal-header">
      <div class="title"></div>
      <button data-close-button class="close-button">&times;</button>
    </div>
    <div class="modal-body">
    <div class="wrapper">
                    <input type="radio" name="select" id="option-1"  onclick="show1();">
                    <input type="radio" name="select" id="option-2" onclick="show2();">.
                    <input type="radio" name="select" id="option-3" onclick="show3();">
                    <label for="option-1" class="option option-1">
                        <div class="dot"></div>
                        <span class="labeled">Monthly</span>
                    </label>
                    <label for="option-2" class="option option-2">
                        <div class="dot"></div>
                        <span  class="labeled">Quarterly</span>
                    </label>
                    <label for="option-3" class="option option-3">
                        <div class="dot"></div>
                        <span  class="labeled">Yearly</span>
                    </label>
            </div>
            <form action="../includes/dbprocess.php" Method="POST" autocomplete="off"> 
            <div id="Monthly" class="hide">
                     <div class="monthly-wrapper">
                          <span class="custom-dropdown big">
                        <select name="monthIShow" required>    
                        <option value="">Month</option>
                        <option value="1">January</option>
                        <option value="2">February</option>
                        <option value="3">March</option>
                        <option value="4">April</option>
                        <option value="5">May</option>
                        <option value="6">June</option>
                        <option value="7">July</option>
                        <option value="8">August</option>
                        <option value="9">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>     
                          </select>
               </span>
                 
              
                 <input type="text"  id="text" name="yearIShow"  onkeypress="return onlyNumberKey(event)" placeholder="Put year in this format : '2021'" minlength="4" maxlength="4" required >
                 <div class="input-container cta">
                                        <button type="submit" name="Search_Monthly_IS" class="signup-btn continue">Continue</button>
                                </div>
               
            </div>     
         </div>
         </form>   
         <form action="../includes/dbprocess.php" Method="POST" autocomplete="off"> 
                    <div id="Quarterly" class="hide">

                        <span class="custom-dropdown big">
                        <select name="quarterIShow" required>    
                        <option value="Q1">Quarter 1 Jan-Mar</option>
                        <option value="Q2">Quarter 2 Apr-Jun</option>
                        <option value="Q3">Quarter 3 Jul-Sep</option>
                        <option value="Q4">Quarter 4 Oct-Dec</option>
                        </select>
                        </span>
                         
               
                    
                 <input type="text"  id="text" name="yearIShowQ"  onkeypress="return onlyNumberKey(event)" placeholder="Put year in this format : '2021'" minlength="4" maxlength="4" required >
                 <div class="input-container cta">
                                        <button type="submit" name="Search_Quarterly_IS" class="signup-btn continue">Continue</button>
                                </div>
               
                   
               
                                
                        </div>
                  </form>
                  <form action="../includes/dbprocess.php" Method="POST" autocomplete="off"> 
                    <div id="Yearly" class="hide">

                    
                  
                    
                    <input type="text"  id="text" name="yearIShowY"  onkeypress="return onlyNumberKey(event)" placeholder="Put year in this format : '2021'" minlength="4" maxlength="4" required >
                    <div class="input-container cta">
                                        <button type="submit" name="Search_Yearly_IS" class="signup-btn continue">Continue</button>
                                </div>
               
              </div>          
                  </form>

    
  </div>

  </div>
  <div id="overlay3"></div>


 
 
</body>
<script>
                         function onlyNumberKey(evt) {
          
                                 // Only ASCII character in that range allowed
                                 var ASCIICode = (evt.which) ? evt.which : evt.keyCode
                                 if (ASCIICode > 31 && (ASCIICode < 48 || ASCIICode > 57))
                                        return false;
                                return true;

                        }
</script>

<?php include 'includes/footer.php' ?>

<script>
    function show1(){
  document.getElementById('Monthly').style.display ='block';
  document.getElementById('Quarterly').style.display ='none';
  document.getElementById('Yearly').style.display ='none';
}
function show2(){
  document.getElementById('Quarterly').style.display = 'block';
  document.getElementById('Monthly').style.display ='none';
  document.getElementById('Yearly').style.display ='none';
}
function show3(){
  document.getElementById('Quarterly').style.display = 'none';
  document.getElementById('Monthly').style.display ='none';
  document.getElementById('Yearly').style.display ='block';
}
</script>