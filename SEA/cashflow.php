<?php 


include_once '../includes/dbprocess.php';

if(isset($_SESSION['isLoggedin'])){
  
}else{
  header("Location: ../index.php");
}


include 'includes/header.php'; 

include 'includes/menu.php'; ?>

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


<body>

          <?php       
                    $month = $_SESSION['month_is'];
                    $year = $_SESSION['year_is'];
                    $Bname =  $_SESSION['business_name'];

                    $query = "SELECT  `details` FROM `tblcashflow` WHERE `date_month` = '$month' AND `date_year` = '$year' AND `business_name` = '$Bname'";    
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
        <div class="title-wrapper center">
                    <h2>STATEMENT OF CASH FLOWS</h2>
                    <p class="center" style="text-align: center;"><?php echo $details?></p>
                </div>
        </div>

        <div class="button-wrapper">
        <button data-modal-target="#modal3" class="buttonss views">
            <span class="button__texts">VIEW CF</span>
            <span class="button__icons">
            <ion-icon name="wallet-outline"></ion-icon>
             </span>
        </button>
        </div>



        <?php       
                    $month = $_SESSION['month_is'];
                    $year = $_SESSION['year_is'];
                    $Bname =  $_SESSION['business_name'];

                    $query = "SELECT `description`, `amount`, `sign`, `first_balance` FROM `tblcashflow` WHERE `date_month` = '$month' AND `date_year` = '$year' AND `business_name` = '$Bname' AND `category` = 'OPERATING'";    
                    $stmt = $conn->prepare($query);
                    $stmt-> execute();
                    $result = $stmt->get_result();  
                    
        ?>  

      

        <table class="content-table income-table">
            <thead>
                <tr>
                <th>OPERATING</th>
                <th>AMOUNT</th>
                </tr>
            </thead>
            <tbody>
            <?php 
            $first_balance  = 0;
            while ($row = $result->fetch_assoc()) { 
                        $first_balance   = $row['first_balance'];
              
             
              ?>
                <tr>
                <td><?= $row['description'] ?></td>
                <td><strong>₱ </strong>
                <?php 

                if($row['sign'] == "negative"){
                  echo '('.number_format($row['amount']).')'; 
                }else{
                  
                  echo number_format($row['amount']); 
                }
                ?></td>
                </tr>
            <?php } ?>

            <?php       
                    $query = "SELECT  SUM(amount) AS total_amount FROM `tblcashflow` WHERE `date_month` = '$month' AND `date_year` = '$year' AND `business_name` = '$Bname' AND `category` = 'OPERATING' AND `sign` = 'negative'";    
                    $stmt = $conn->prepare($query);
                    $stmt-> execute();
                    $result = $stmt->get_result();  

                    while ($row = $result->fetch_assoc()) {
                        $totalnegative = $row['total_amount'];
                    }

                    $query = "SELECT  SUM(amount) AS total_amount FROM `tblcashflow` WHERE `date_month` = '$month' AND `date_year` = '$year' AND `business_name` = '$Bname' AND `category` = 'OPERATING' AND `sign` = 'positive'";    
                    $stmt = $conn->prepare($query);
                    $stmt-> execute();
                    $result = $stmt->get_result();  

                    while ($row = $result->fetch_assoc()) {
                        $totalpositive = $row['total_amount'];
                    }
            ?>  

               <tr style="background: #fef4a9;">
                    <th style="color:  #009879; text-align:left;" class="total">Net cash provided (used) from operating activities</th>
                    <td ><strong>₱</strong>
                    <?php 
                    
                    if($totalpositive > $totalnegative){
                      $totalOP = $totalpositive - $totalnegative;
                      $totalOPO = $totalpositive - $totalnegative;
                      echo number_format($totalOP);
                    }else{
                      $totalOP = $totalnegative - $totalpositive;
                      $totalOPO = $totalpositive - $totalnegative;
                      echo '('.number_format($totalOP).')';
                    }
                    
                    ?></td>
           
                </tr>
            </tbody>
            </table>
            <?php       
                    $month = $_SESSION['month_is'];
                    $year = $_SESSION['year_is'];
                    $Bname =  $_SESSION['business_name'];

                    $query = "SELECT `description`, `amount`, `sign` FROM `tblcashflow` WHERE `date_month` = '$month' AND `date_year` = '$year' AND `business_name` = '$Bname' AND `category` = 'INVESTING'";    
                    $stmt = $conn->prepare($query);
                    $stmt-> execute();
                    $result = $stmt->get_result();  
                    
        ?>  

        <table class="content-table income-table">
            <thead>
                <tr>
                <th>INVESTING</th>
                <th>AMOUNT</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                <td><?= $row['description'] ?></td>
                <td><strong>₱ </strong>
                <?php 

                if($row['sign'] == "negative"){
                  echo '('.number_format($row['amount']).')'; 
                }else{
                  
                  echo number_format($row['amount']); 
                }
                ?></td>
                </tr>
            <?php } ?>

            <?php       
                    $query = "SELECT  SUM(amount) AS total_amount FROM `tblcashflow` WHERE `date_month` = '$month' AND `date_year` = '$year' AND `business_name` = '$Bname' AND `category` = 'INVESTING' AND `sign` = 'negative'";    
                    $stmt = $conn->prepare($query);
                    $stmt-> execute();
                    $result = $stmt->get_result();  

                  
                    while ($row = $result->fetch_assoc()) {
                        $totalnegative = $row['total_amount'];
                    }

                    $query = "SELECT  SUM(amount) AS total_amount FROM `tblcashflow` WHERE `date_month` = '$month' AND `date_year` = '$year' AND `business_name` = '$Bname' AND `category` = 'INVESTING' AND `sign` = 'positive'";    
                    $stmt = $conn->prepare($query);
                    $stmt-> execute();
                    $result = $stmt->get_result();  

                    while ($row = $result->fetch_assoc()) {
                        $totalpositive = $row['total_amount'];
                    }
            ?>  

               <tr style="background: #fef4a9;">
                    <th style="color:  #009879; text-align:left;" class="total">Net cash provided (used) from investing activities</th>
                    <td ><strong>₱</strong>
                    <?php 
                    
                    if($totalpositive > $totalnegative){
                      $totalIN = $totalpositive - $totalnegative;
                      $totalINO = $totalpositive - $totalnegative;
                      echo number_format($totalIN);
                    }else{
                      $totalIN = $totalnegative - $totalpositive;
                      $totalINO = $totalpositive - $totalnegative;
                      echo '('.number_format($totalIN).')';
                    }
                    
                    ?></td>
           
                </tr>
            </tbody>
            </table>

            <?php       
                    $month = $_SESSION['month_is'];
                    $year = $_SESSION['year_is'];
                    $Bname =  $_SESSION['business_name'];

                    $query = "SELECT `description`, `amount`, `sign` FROM `tblcashflow` WHERE `date_month` = '$month' AND `date_year` = '$year' AND `business_name` = '$Bname' AND `category` = 'FINANCING'";    
                    $stmt = $conn->prepare($query);
                    $stmt-> execute();
                    $result = $stmt->get_result();  
                    
        ?>  

        <table class="content-table income-table">
            <thead>
                <tr>
                <th>FINANCING</th>
                <th>AMOUNT</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                <td><?= $row['description'] ?></td>
                <td><strong>₱ </strong>
                <?php 

                if($row['sign'] == "negative"){
                  echo '('.number_format($row['amount']).')'; 
                }else{
                  
                  echo number_format($row['amount']); 
                }
                ?></td>
                </tr>
            <?php } ?>

            <?php       
                    $query = "SELECT  SUM(amount) AS total_amount FROM `tblcashflow` WHERE `date_month` = '$month' AND `date_year` = '$year' AND `business_name` = '$Bname' AND `category` = 'FINANCING' AND `sign` = 'negative'";    
                    $stmt = $conn->prepare($query);
                    $stmt-> execute();
                    $result = $stmt->get_result();  

                  
                    while ($row = $result->fetch_assoc()) {
                        $totalnegative = $row['total_amount'];
                    }

                    $query = "SELECT  SUM(amount) AS total_amount FROM `tblcashflow` WHERE `date_month` = '$month' AND `date_year` = '$year' AND `business_name` = '$Bname' AND `category` = 'FINANCING' AND `sign` = 'positive'";    
                    $stmt = $conn->prepare($query);
                    $stmt-> execute();
                    $result = $stmt->get_result();  

                    while ($row = $result->fetch_assoc()) {
                        $totalpositive = $row['total_amount'];
                    }
            ?>  

               <tr style="background: #fef4a9;">
                    <th style="color:  #009879; text-align:left;" class="total">Net cash provided (used) from financing activities</th>
                    <td ><strong>₱</strong>
                    <?php 
                    
                    if($totalpositive > $totalnegative){
                      $totalFI = $totalpositive - $totalnegative;
                      $totalFIO = $totalpositive - $totalnegative;
                      echo number_format($totalFI);
                    }else{
                      $totalFI = $totalnegative - $totalpositive;
                      $totalFIO = $totalpositive - $totalnegative;
                      echo '('.number_format($totalFI).')';
                    }
                    
                    ?></td>
           
                </tr>
            </tbody>
            </table>

            <table class="content-table ">
            <thead>
                <tr>
                <th>NET CHANGE IN CASH</th>
                <th><strong>₱</strong> <?php 
                $netC = $totalFIO + $totalINO + $totalOPO;
                if($netC < 0){
                  echo '('.number_format(substr($netC,1)).')';
                }else{
                  echo number_format($netC);
                }
                ?></th>
              
            
                </tr>
            </thead>
            <tbody>

            
                <tr>
               
                <td data-label="Description">CASH BEGINNING</td>
                <td data-label="DATE"><strong>₱</strong> <?= number_format($first_balance) ?></td>
                </tr>
               
                <?php 
                $netC = $totalFIO + $totalINO + $totalOPO;
                $netC = $netC + $first_balance;
                if($netC < 0){ ?>
                <tr style="background-color:#F25E5E;">
                <th style="color:#fff; text-align:left; " class="total">CASH ENDING</th>
                <td style="color:#fff;" data-label="DATE"><strong>₱</strong> <?php
                  echo '('.number_format(substr($netC,1)).')'; ?>
                <?php
                }else{ ?>
                <tr style="background-color: #a7ff83;">
                <th style="color:  #009879; text-align:left; " class="total">CASH ENDING</th>
                <td data-label="DATE"><strong>₱</strong> <?php
                  echo number_format($netC);
                }
                ?></td>

                </tr>
             
           
                
            </tbody>
            </table>
  
            <div class="button-footer">
        <form action="../includes/dbprocess.php" Method="POST"> 
        <button type="submit" name='print_CF' class="buttonss">
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
                        <select name="monthCShow" required>    
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
                 
              
                 <input type="text"  id="text" name="yearCShow"  onkeypress="return onlyNumberKey(event)" placeholder="Put year in this format : '2021'" minlength="4" maxlength="4" required >
                 <div class="input-container cta">
                                        <button type="submit" name="Search_Monthly_CF" class="signup-btn continue">Continue</button>
                                </div>
              
                 
            </div>     
         </div>
         </form>   
         <form action="../includes/dbprocess.php" Method="POST" autocomplete="off"> 
                    <div id="Quarterly" class="hide">

                        <span class="custom-dropdown big">
                        <select name="quarterCShow" required>    
                        <option value="Q1">Quarter 1 Jan-Mar</option>
                        <option value="Q2">Quarter 2 Apr-Jun</option>
                        <option value="Q3">Quarter 3 Jul-Sep</option>
                        <option value="Q4">Quarter 4 Oct-Dec</option>
                        </select>
                        </span>
                         
                
                    
                 <input type="text"  id="text" name="yearCShowQ"  onkeypress="return onlyNumberKey(event)" placeholder="Put year in this format : '2021'" minlength="4" maxlength="4" required >
                 <div class="input-container cta">
                                        <button type="submit" name="Search_Quarterly_CF" class="signup-btn continue">Continue</button>
                                </div>
                   
            
                                
                        </div>
                  </form>
                  <form action="../includes/dbprocess.php" Method="POST" autocomplete="off"> 
                    <div id="Yearly" class="hide">

                    
                   
                    
                    <input type="text"  id="text" name="yearCShowY"  onkeypress="return onlyNumberKey(event)" placeholder="Put year in this format : '2021'" minlength="4" maxlength="4" required >
                     
                    <div class="input-container cta">
                                        <button type="submit"  name="Search_Yearly_CF" class="signup-btn continue">Continue</button>
                                </div>
   
                    </div>

              </div>          
                  </form>

    
  </div>

  </div>
  <div id="overlay3"></div>





 
</body>


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