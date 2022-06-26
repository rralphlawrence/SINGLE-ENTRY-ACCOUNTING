
<?php 



include_once '../includes/dbprocess.php';

if(isset($_SESSION['isLoggedin'])){
  
}else{
  header("Location: ../index.php");
}


include 'includes/header.php'; 

include 'includes/menu.php'; 


?>

<body onload="setDataOnSelection()">



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


    <div class="title-top">

            <div class="title-wrapper">
                <h2><?php echo $_SESSION['business_name']?></h2>
            </div>
            <div class="title-wrapper">
                <h2>Cash book entry</h2>
            </div>
    </div>

<div class="cover">
<div class="box">
<div class="top">
        <button type="buttons" data-modal-target="#modal" class="buttonss" id="newData">
            <span class="button__texts"  >NEW</span>
            <span class="button__icons">
            <ion-icon name="add-circle-outline"></ion-icon>
        </span>
        </button>
        
        <span data-tooltip="GENERATE CF" >
        <button type="buttons"  data-modal-target="#modal2" class="buttonss" id="btnCashFlow">
            <span class="button__texts">CASHFLOW</span>
            <span class="button__icons">
            <ion-icon name="reader-outline"></ion-icon>
        </span>
        </button>
    </span>
        <span data-tooltip="GENERATE IS" >
        <button type="buttons"  data-modal-target="#modal2" class="buttonss" id="btnIncomeStatement">
            <span class="button__texts">INCOME STATEMENT</span>
            <span class="button__icons">
            <ion-icon name="wallet-outline"></ion-icon>
        </span>
        </button>
        </span>
</div>

<div class="print">
    <span data-tooltip="PRINT RECORDS">
        <button type="button" data-modal-target="#modal2" class="buttonss" id="btnPrint">
            <span class="button__texts">PRINT</span>
            <span class="button__icons">
            <ion-icon name="receipt-outline"></ion-icon>
        </span>
        </button>
    </span>
       <span data-tooltip="DOWNLOAD RECORDS">
        <button type="buttons" data-modal-target="#modal2" class="buttonss" id="btnDownload">
            <span class="button__texts">Download</span>
            <span class="button__icons">
            <ion-icon name="download-outline"></ion-icon>
        </span>
        </button>
        </span>
</div>


</div>


<div class="dropdown">

<form action="../includes/dbprocess.php" Method="POST">
          <span class="custom-dropdown big">
               <select id="Month" name="month">  
                    <option value="" disabled selected>MONTH</option>  
                    <option value="01">January</option>
                    <option value="02">February</option>
                    <option value="03">March</option>
                    <option value="04">April</option>
                    <option value="05">May</option>
                    <option value="06">June</option>
                    <option value="07">July</option>
                    <option value="08">August</option>
                    <option value="09">September</option>
                    <option value="10">October</option>
                    <option value="11">November</option>
                    <option value="12">December</option>
               </select>
               </span>

               <span class="custom-dropdown big">
                   
               <?php
                    $query = "SELECT DISTINCT YEAR(date) as year FROM `tblcashbookentry` WHERE `business_name` = '$Bname'";    
                    $stmt = $conn->prepare($query);
                    $stmt->execute();
                    $result = $stmt->get_result();
                ?>   
               <select id="Year" name="year">    
                    <option value="" disabled selected>YEAR</option>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <option value="<?= $row['year']; ?>"><?= $row['year']; ?></option>
                    <?php } ?>  
               </select>
               </span>
               <span data-tooltip="SHOW RECORDS" >
                    <button   style="background-color:#be6a15;" type="buttons" id="show" type="submit" name="show_table" class="buttonss">
                     <span class="button__texts"  >SHOW</span>
                     <span class="button__icons">
                     <ion-icon name="add-circle-outline"></ion-icon>
                     </span>
                    </button>
               </span>
          </form>
 </div>
 </div>



 <div class="modal" id="modal">
                <div class="modal-header">
                     <div class="title"></div>
                        <button data-close-button class="close-button">&times;</button>
                        </div>
                        <div class="modal-body">
                        <form action="../includes/dbprocess.php" Method="POST" autocomplete="off">
                              
                                <div class="input-container name combobox">
                                  
                               

                                <span class="custom-dropdown big large">
                                <select name="description" id="description" required>    
                                        <option value="">Description</option>
                                        <option value="Beginning balance">Beginning balance</option> 
                                        <option value="Investment">Investment</option>  
                                        <option value="Sales">Sales</option>
                                        <option value="Service Income">Service Income</option>
                                        <option value="Customer Deposits">Customer Deposits</option>
                                        <option value="Bank Financing Long Term">Bank Financing Long Term</option>
                                        <option value="Bank Financing Short Term">Bank Financing Short Term</option>
                                        <option value="Other source of cash">Other source of cash</option>
                                        <option value="Other Income">Other Income</option>
                                        <option value="Salaries and Wages">Salaries and Wages</option>
                                        <option value="Rent Expenses">Rent Expenses</option>
                                        <option value="Amortization Expenses">Amortization Expenses</option>
                                        <option value="Marketing Expenses">Marketing Expenses</option>
                                        <option value="Utilities Expenses">Utilities Expenses</option>
                                        <option value="Insurance Expenses">Insurance Expenses</option>
                                        <option value="Inventory Purchases">Inventory Purchases</option>
                                        <option value="Loan Payments - Short term">Loan Payments - Short term</option>
                                        <option value="Loan Payments - Long term">Loan Payments - Long term</option>
                                        <option value="Supplies Expenses">Supplies Expenses</option>
                                        <option value="Miscellaneous Expenses">Miscellaneous Expenses</option>
                                        <option value="Interest Income">Interest Income</option>
                                        <option value="Interest Expenses">Interest Expenses</option>
                                        <option value="Administrative Expenses">Administrative Expenses</option>
                                        <option value="Selling and distribution Expenses">Selling and Distribution Expenses</option>
                                        <option value="Other uses of cash">Other uses of cash</option>
                                        <option value="Other Expenses">Other Expenses</option>
                                        <option value="Equipment">Equipment</option>
                                        <option value="Vehicle">Vehicle</option>
                                        <option value="Furniture">Furniture</option>
                                </select>
                                </span>


                                <div class="date">
                                <input type="date" name="date" id="date" required>
                                <input type="hidden" name="Olddate" id="Olddate">
                                <input type="hidden" name="id" id="id">
                                <input type="hidden" name="od" id="od">
                                </div>

                                </div>
                                <div class="radio-container">
                                        <div class="container">
                                            <input type="radio" name="type_of_entry" value="Inflows" id="in" required>
                                            <label for="in">Inflows</label>
                                        </div>

                                      <div class="container">
                                            <input type="radio" name="type_of_entry" value="Outflows" id="out" required>
                                            <label for="out">Outflow</label>
                                    </div>
                                </div>


                                <div class="input-container email">
                                 
                                    <input type="text" id="amount" name="amount" onkeypress="return onlyNumberKey(event)" placeholder="Input your amount" required>
                                </div>
                                
                                <div class="input-container cta">
                                        <button type="submit" name="add_entry" class="signup-btn continue">Continue</button>
                                </div>
                               
                            </form>
                        </div>
                   </div>

                    <script>
                         function onlyNumberKey(evt) {
          
                                 // Only ASCII character in that range allowed
                                 var ASCIICode = (evt.which) ? evt.which : evt.keyCode
                                 if (ASCIICode > 31 && (ASCIICode < 48 || ASCIICode > 57))
                                        return false;
                                return true;

                            
                        }

                        function setDataOnSelection(){
                            $('#Month').val("<?php echo $_SESSION['month']?>");
                            $('#Year').val("<?php echo $_SESSION['year']?>");
                           
                        }

                    </script>


            <div id="overlay"></div>
            <?php       
                         
                    $month = $_SESSION['month'];
                    $year = $_SESSION['year'];
                    $Bname = mysqli_real_escape_string($conn, $Bname);

                    $query = "SELECT cbe_id, date, order_by, description, inflows, outflows, balance FROM tblcashbookentry WHERE ((MONTH(date) = '$month' AND YEAR(date)= '$year') AND (business_name = '$Bname')) Order By date, order_by ASC";    
                    $stmt = $conn->prepare($query);
                    $stmt-> execute();
                    $result = $stmt->get_result();  
                    
             ?>    
            <table class="content-table table">
            <thead>
                <tr>
                <th hidden>ID</th>
                <th>Date</th>
                <th hidden >OD</th>
                <th>Description</th>
                <th>Inflows</th>
                <th>Outflows</th>
                <th>Balances</th>
                <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                <td data-label="ID" hidden><?= $row['cbe_id'] ?></td>
                <td data-label="Date"><?= $row['date'] ?></td>
                <td data-label="OD" hidden><?= $row['order_by'] ?></td>
                <td data-label="Description"><?= $row['description'] ?></td>
                <td data-label="Inflows"> <strong>₱ </strong><?= number_format($row['inflows']) ?></td>
                <td data-label="Outflows"><strong>₱ </strong><?= number_format($row['outflows']) ?></td>
                <td data-label="Balance"><strong>₱ </strong><?= number_format($row['balance']) ?></td>
                <td data-label="Actions" class="seperate">

                    <span data-tooltip="Edit Record">
                    <a href="javascripit:void(0)" class="button editData" data-modal-target="#modal">
                    <span class="button__icon">
                    <ion-icon name="add-circle-outline"></ion-icon>
                    </span>
                    </a>
                    </span>

                    <span data-tooltip="Delete Record">
                    <a href="javascripit:void(0)" class="button deleteData">
                    <span class="button__icon">
                    <ion-icon name="trash-outline"></ion-icon>
                    </span>
                    </a>
                    </span>
               
                </td>
                </tr>
            <?php }?> 
            </tbody>
            </table>


            <script>
                    
                        $(document).ready(function () {

                            $('.button, .editData').on('click', function(e){

                                    e.preventDefault();

                                     $tr = $(this).closest('tr');
                                     var data = $tr.children("td").map(function(){
                                        return $(this).text();
                                    }).get();

                                    var id = data[0];
                                    var date = data[1];
                                    var od = data[2];
                                    var description = data[3];
                                    var inflows = data[4].substring(2).replace(/,/g, "");
                                    var outflows = data[5].substring(1).replace(/,/g, "");
                                    var balance = data[6].substring(1).replace(/,/g, "");

                                        $('#id').val(id);
                                        $('#od').val(od);
                                        $('#date').val(date);
                                        $("#date").prop("readonly",true);
                                        $('#Olddate').val(date);
                                        $('#description').val(description);

                                        if(inflows == 0){
                                            $('#amount').val(outflows);
                                            $('#out').prop("checked", true);
                                            $('#in').prop("checked", false);
                                        }else{
                                            $('#amount').val(inflows);
                                            $('#out').prop("checked", false);
                                            $('#in').prop("checked", true);
                                        }
    
                            });



                            $('#newData').on('click', function(e){

                                    e.preventDefault();

                                    var id = "";
                                    var date = "";
                                    var od = "";
                                    var description = "";
                                    var inflows = "";
                                    var outflows = "";
                                    var balance = "";

                                        $('#id').val(id);
                                        $('#od').val(od);
                                        $('#date').val(date);
                                        $("#date").prop("readonly",false);
                                        $('#Olddate').val(date);
                                        $('#description').val(description);
                                        $('#amount').val(outflows);
                                        $('#out').prop("checked", false);
                                        $('#in').prop("checked", false);
                                       
    
                            });


                            $('#btnIncomeStatement').on('click', function(e){

                                    e.preventDefault();

                                    var whatToDo = "Income Statement";

                                        $('.anoito').val(whatToDo);
    
                            });


                            $('#btnCashFlow').on('click', function(e){

                                    e.preventDefault();

                                    var whatToDo = "Cash Flow";

                                        $('.anoito').val(whatToDo);
    
                            });

                            $('#btnPrint').on('click', function(e){

                                    e.preventDefault();

                                    var whatToDo = "Print";

                                        $('.anoito').val(whatToDo);
    
                            });

                            $('#btnDownload').on('click', function(e){

                                    e.preventDefault();

                                    var whatToDo = "Download";

                                        $('.anoito').val(whatToDo);
    
                            });





                            $('.deleteData').on('click', function(e){

                                        e.preventDefault();

                                        $tr = $(this).closest('tr');
                                        var data = $tr.children("td").map(function(){
                                        return $(this).text();
                                        }).get();

                                        var id = data[0];
                                        
                                        var date = data[1];
                                        var od = data[2];
                                        


                                        swal({
                                                title: "Are you sure to delete this file?",
                                                icon: "warning",
                                                buttons: true,
                                                dangerMode: true,
                                            })
                                        .then((willDelete) => {
                                            
                                            if (willDelete) {

                                                $.ajax({
                                                    type: "POST",
                                                    url: "../includes/dbprocess.php", 
                                                    data: {
                                                        "delete_btn":1,
                                                        "delete_id": id,
                                                        "delete_date": date,
                                                        "delete_od": od,
                                                    },
                                                success: function(result){
                                                    swal({
                                                        title: "Successfully File Deleted!",
                                                        icon: "success",
                                                    }).then((result) => {
                                                        location.reload();
                                                    });
                                                }
                                            });

                                            }   

                                        });
                                    });

                            });


</script>


                        

          <div class="modal" id="modal2">
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
                        <select name="monthG" required>    
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
                 <div class="input-container text">
                     <label for="text">Year</label>
                     <input type="text"  id="text" name="yearM"  onkeypress="return onlyNumberKey(event)" placeholder="Put year in this format : '2021'" minlength="4" maxlength="4" required >
                     <input type = "hidden" name="whatshouldIDOM" class="anoito">
                 </div>
                 <div class="input-container cta">
                    <button class="signup-btn continue" type="submit" name="generate-monthly">GENERATE</button>
                </div>
            </form>   
            </div>     
         </div>
         <form action="../includes/dbprocess.php" Method="POST" autocomplete="off"> 
                    <div id="Quarterly" class="hide">

                        <span class="custom-dropdown big">
                        <select name="quarterG" required>    
                        <option value="Q1">Quarter 1 Jan-Mar</option>
                        <option value="Q2">Quarter 2 Apr-Jun</option>
                        <option value="Q3">Quarter 3 Jul-Sep</option>
                        <option value="Q4">Quarter 4 Oct-Dec</option>
                        </select>


                        </span>
                        <div class="input-container text">
                            <label for="text">Year</label>
                            <input type="text"  id="text" name="yearQ"  onkeypress="return onlyNumberKey(event)" placeholder="Put year in this format : '2021'" minlength="4" maxlength="4"  required>
                            <input type = "hidden" name="whatshouldIDOQ" class="anoito">
                        </div>
                        <div class="input-container cta">
                                        <button class="signup-btn continue" type="submit" name="generate-quarterly">GENERATE</button>
                                </div>
                                
                        </div>
        </form>            
        <form action="../includes/dbprocess.php" Method="POST" autocomplete="off">   
                    <div id="Yearly" class="hide">

                    <div class="input-container text">
                     <label for="text">Year</label>
                     <input type="text"  id="text" name="yearY"  onkeypress="return onlyNumberKey(event)" placeholder="Put year in this format : '2021'" minlength="4" maxlength="4"  required>
                     <input type = "hidden" name="whatshouldIDOY" class="anoito">
                 </div>
                 <div class="input-container cta">
                            <button class="signup-btn continue" type="submit" name="generate-yearly">GENERATE</button>
                    </div>
        </form>    
                    </div>
                            </div>
                        </div>
                                    <div id="overlayIS"></div>


    <script>

            const openModalButtons = document.querySelectorAll('[data-modal-target]')
            const closeModalButtons = document.querySelectorAll('[data-close-button]')
            const overlay = document.getElementById('overlay')

            openModalButtons.forEach(button => {
            button.addEventListener('click', () => {
                const modal = document.querySelector(button.dataset.modalTarget)
                openModal(modal)
            })
            })

            overlay.addEventListener('click', () => {
            const modals = document.querySelectorAll('.modal.active')
            modals.forEach(modal => {
                closeModal(modal)
            })
            })

            closeModalButtons.forEach(button => {
            button.addEventListener('click', () => {
                const modal = button.closest('.modal')
                closeModal(modal)
            })
            })

            function openModal(modal) {
            if (modal == null) return
            modal.classList.add('active')
            overlay.classList.add('active')
            }

            function closeModal(modal) {
            if (modal == null) return
            modal.classList.remove('active')
            overlay.classList.remove('active')
            }
    </script>

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




         <?php include 'includes/footer.php' ?>