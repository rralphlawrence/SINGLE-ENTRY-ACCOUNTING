<?php
session_start();
include_once 'dbconnect.php';
require ('fpdf182/fpdf.php');
require_once "vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;



// Registration Process

if(isset($_POST['signup_btn'])){

    $Email = mysqli_real_escape_string($conn, $_POST['email']);
    $Fullname = $_POST['fname'];
    $BusinessName = $_POST['bname'];
    $PassW = mysqli_real_escape_string($conn, password_hash ($_POST['password'],PASSWORD_DEFAULT));

    $sqlforNoAccount = "SELECT * FROM tblaccounts WHERE email ='$Email' OR business_name = '$BusinessName'";
    $sqlrun = mysqli_query($conn, $sqlforNoAccount);

    if(mysqli_num_rows($sqlrun)>0){
        header("Location: ../register.php");
        $_SESSION ['response'] = "Credentials Already Exists!";
        $_SESSION ['res_type']= "error";
    }else{
    
    $sqlforAccounts = "INSERT INTO tblaccounts(user_id,email, password, business_name, business_owner) VALUES ('',?,?,?,?);";
    
    $stmt = mysqli_stmt_init($conn);

    if(!mysqli_stmt_prepare($stmt, $sqlforAccounts)){
        echo "SQL Error";
    }else{
        mysqli_stmt_bind_param($stmt,"ssss",$Email,$PassW,$BusinessName,$Fullname);
        mysqli_stmt_execute($stmt);
    }
       



        header("Location: ../index.php");
        $_SESSION ['response'] = "Successfully Account Created!";
        $_SESSION ['res_type']= "success";
    

    }
}

//Login Process

if(isset($_POST['login_btn'])){
    $Email = $_POST['email'];
    $Password = $_POST['password'];

    $sqlforNoAccount = "SELECT * FROM tblaccounts WHERE email ='$Email'";
    $stmt = $conn->prepare($sqlforNoAccount);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
            $PasswordinDB = $row['password'];
            $BusinessOwner = $row['business_owner'];
            $BusinessName = $row['business_name'];
    }
    
        if(password_verify($Password,$PasswordinDB)){

            header("Location: ../SEA/dashboard.php");
            $_SESSION ['business_name'] = $BusinessName;
            $_SESSION ['business_owner'] = $BusinessOwner;
            $_SESSION ['isLoggedin'] = true;
            $_SESSION ['response'] = "Successfully Login!";
            $_SESSION ['first'] = true;
            $_SESSION ['year'] = date("Y");
            $_SESSION ['month']= date("m");
            $_SESSION ['year_is'] = date("Y");
            $_SESSION ['month_is']= date("m");
            
            //$_SESSION ['ISdetails']= "for the Month ended October 2021";
            $_SESSION ['res_type']= "success";
            

        }else{
            header("Location: ../index.php");
            $_SESSION ['response'] = "The Password you’ve entered is incorrect.";
            $_SESSION ['res_type']= "error";
        }

}


if(isset($_POST['show_table'])){

    $month = $_POST['month'];
    $year = $_POST['year'];


    header("Location: ../SEA/cashbook.php");
    $_SESSION ['year'] = $year;
    $_SESSION ['month']= $month;



}


if(isset($_POST['run_dashboard_report'])){

    $year = $_POST['year'];


    header("Location: ../SEA/dashboard.php");
    $_SESSION ['year_is'] = $year;

}



//Add and Edit Entry on Cashbook 

if(isset($_POST['add_entry'])){
    
    $BusinessName = mysqli_real_escape_string($conn,   $_SESSION ['business_name']);
    $Date = mysqli_real_escape_string($conn, $_POST['date']);
    $Month = substr($Date,5,2);
    $Year = substr($Date,0,4);
    $ID = mysqli_real_escape_string($conn, $_POST['id']);
    $OD = mysqli_real_escape_string($conn, $_POST['od']);
    $OldDate = mysqli_real_escape_string($conn, $_POST['Olddate']);
    $Description = mysqli_real_escape_string($conn, $_POST['description']);
    $Amount = mysqli_real_escape_string($conn, $_POST['amount']);

    $Type = mysqli_real_escape_string($conn, $_POST['type_of_entry']);

    
    if($Type == "Outflows"){
        $Inflows = 0;
        $Outflows = $Amount;
    }else{
        $Inflows = $Amount;
        $Outflows = 0;
    }



    //Adding entry
    if($ID == ""){


        $sqlforNoAccount = "SELECT cbe_id FROM tblcashbookentry WHERE (MONTH(date) = '$Month' AND YEAR(date) = '$Year' AND business_name = '$BusinessName') AND date > '$Date' Order By date, order_by ASC";
        $sqlrun = mysqli_query($conn, $sqlforNoAccount);
    
        if(mysqli_num_rows($sqlrun)>0){
        //This section ay may date na nalampasan
            
            $stmt = $conn->prepare($sqlforNoAccount);
            $stmt->execute();
            $result = $stmt->get_result();
        
            $IDS = array();

            $counter = 0;
            while ($row = $result->fetch_assoc()) {
            $IDS[$counter] = $row['cbe_id'];
            $counter++;
            }

            $numberNeedUpdate = count($IDS);


            $sqlforNoAccount = "SELECT balance, order_by  FROM tblcashbookentry WHERE order_by = (SELECT MAX(order_by) FROM tblcashbookentry WHERE (date = '$Date' AND business_name = '$BusinessName') AND  (MONTH(date) = '$Month' AND YEAR(date) = '$Year')) AND date = '$Date'";
            $stmt = $conn->prepare($sqlforNoAccount);
            $stmt->execute();
            $result = $stmt->get_result();
        
            while ($row = $result->fetch_assoc()) {
                    $prevBal = $row['balance'];
                    $order = $row['order_by'];
            }

            if($prevBal == 0){
                $sqlforNoAccount = "SELECT balance FROM tblcashbookentry WHERE (order_by = (SELECT MAX(order_by) from tblcashbookentry WHERE date = (SELECT MAX(date) from tblcashbookentry WHERE date < '$Date')) AND date = (SELECT MAX(date) from tblcashbookentry WHERE date < '$Date')) AND (MONTH(date) = '$Month' AND YEAR(date) = '$Year' AND business_name = '$BusinessName')";
                $stmt = $conn->prepare($sqlforNoAccount);
                $stmt->execute();
                $result = $stmt->get_result();

                while ($row = $result->fetch_assoc()) {
                    $prevBal = $row['balance'];
                }    
            }

            
            if($Inflows == "0"){
                $Balance = $prevBal - $Outflows;
                $order = $order + 1;
            }else{
                $Balance = $prevBal + $Inflows;
                $order = $order + 1;
            }
        
        
        
            $sqlforAccounts = "INSERT INTO tblcashbookentry(cbe_id,business_name, date, order_by, description, inflows, outflows, balance) VALUES ('',?,?,?,?,?,?,?);";
        
            $stmt = mysqli_stmt_init($conn);

            if(!mysqli_stmt_prepare($stmt, $sqlforAccounts)){
                echo "SQL Error";
            }else{
                mysqli_stmt_bind_param($stmt,"sssssss",$BusinessName,$Date,$order,$Description,$Inflows,$Outflows,$Balance);
                mysqli_stmt_execute($stmt);
            }


            for ($i=0; $i < $numberNeedUpdate ; $i++) { 
                # code...
        
                $sqlforNoAccount = "SELECT * FROM tblcashbookentry WHERE cbe_id = '$IDS[$i]'";
                $stmt = $conn->prepare($sqlforNoAccount);
                $stmt->execute();
                $result = $stmt->get_result();
            
                while ($row = $result->fetch_assoc()) {
                        $update_inflows = $row['inflows'];
                        $update_outflows = $row['outflows'];
                        $update_date = $row['date'];
                        $update_order = $row['order_by'];
                        $update_description = $row['description'];
        
                }
        
        
                if($update_inflows == "0"){
                    $Balance = $Balance - $update_outflows;
                }else{
                    $Balance = $Balance + $update_inflows;
                }
        
        
                $sqlforupdateaccount="UPDATE tblcashbookentry SET cbe_id= '$IDS[$i]',business_name='$BusinessName',date='$update_date',order_by='$update_order',description='$update_description',inflows='$update_inflows',outflows='$update_outflows',balance='$Balance' WHERE cbe_id = ?";
        
                $stmt = mysqli_stmt_init($conn);
                
                        
                        if(!mysqli_stmt_prepare($stmt, $sqlforupdateaccount)){
                            echo "SQL Error";
                        }else{
                            mysqli_stmt_bind_param($stmt,"s",$IDS[$i]);
                            mysqli_stmt_execute($stmt);
                        }
            }
        
        
            header("Location: ../SEA/cashbook.php");
            $_SESSION ['response'] = "Successfully Account Added";
            $_SESSION ['res_type']= "success";
            $_SESSION ['year'] = $Year;
            $_SESSION ['month']= $Month;


        }else{
        //This section ay walang date na nalampasan

        $sqlforNoAccount = "SELECT balance FROM tblcashbookentry WHERE (MONTH(date) = '$Month' AND YEAR(date) = '$Year' AND business_name = '$BusinessName') Order By date DESC, order_by DESC LIMIT 1";
        $sqlrun = mysqli_query($conn, $sqlforNoAccount);
    
        //May record
        if(mysqli_num_rows($sqlrun)>0){
            
        $stmt = $conn->prepare($sqlforNoAccount);
        $stmt->execute();
        $result = $stmt->get_result();
    
        while ($row = $result->fetch_assoc()) {
                $balance = $row['balance']; 
        }

        if($Type == "Outflows"){
            $balance = $balance - $Amount;
        }else{
            $balance = $balance + $Amount;
        }


        //Check kung may katulad ng date 

        $sqlforNoAccount = "SELECT MAX(order_by) AS order_by FROM tblcashbookentry WHERE (date = '$Date' AND business_name = '$BusinessName')";
        $sqlrun = mysqli_query($conn, $sqlforNoAccount);
    
        if(mysqli_num_rows($sqlrun)>0){
            
        $stmt = $conn->prepare($sqlforNoAccount);
        $stmt->execute();
        $result = $stmt->get_result();
    
        while ($row = $result->fetch_assoc()) {
                $order = $row['order_by'];
        }

        $order = $order + 1;


        $sqlforAccounts = "INSERT INTO tblcashbookentry(cbe_id,business_name, date, order_by, description, inflows, outflows, balance) VALUES ('',?,?,?,?,?,?,?);";
        
        $stmt = mysqli_stmt_init($conn);

        if(!mysqli_stmt_prepare($stmt, $sqlforAccounts)){
            echo "SQL Error";
        }else{
            mysqli_stmt_bind_param($stmt,"sssssss",$BusinessName,$Date,$order,$Description,$Inflows,$Outflows,$balance);
            mysqli_stmt_execute($stmt);
        }
           
            header("Location: ../SEA/cashbook.php");
            $_SESSION ['year'] = $Year;
            $_SESSION ['month']= $Month;
            $_SESSION ['response'] = "Successfully Account Added";
            $_SESSION ['res_type']= "success";

        }else{

            $order = 1;


            $sqlforAccounts = "INSERT INTO tblcashbookentry(cbe_id,business_name, date, order_by, description, inflows, outflows, balance) VALUES ('',?,?,?,?,?,?,?);";
            
            $stmt = mysqli_stmt_init($conn);
    
            if(!mysqli_stmt_prepare($stmt, $sqlforAccounts)){
                echo "SQL Error";
            }else{
                mysqli_stmt_bind_param($stmt,"sssssss",$BusinessName,$Date,$order,$Description,$Inflows,$Outflows,$balance);
                mysqli_stmt_execute($stmt);
            }
               
                header("Location: ../SEA/cashbook.php");
                $_SESSION ['year'] = $Year;
                $_SESSION ['month']= $Month;
                $_SESSION ['response'] = "Successfully Account Added";
                $_SESSION ['res_type']= "success";


        }   



    
    
        
        //New Entry
        }else{
        
            if($Type == "Outflows"){
                $balance = 0 - $Outflows;
            }else{
                $balance = 0 + $Inflows;
            }
        
            
            $order = 1;
            
            $sqlforAccounts = "INSERT INTO tblcashbookentry(cbe_id,business_name, date, order_by, description, inflows, outflows, balance) VALUES ('',?,?,?,?,?,?,?);";
            
            $stmt = mysqli_stmt_init($conn);
    
            if(!mysqli_stmt_prepare($stmt, $sqlforAccounts)){
                echo "SQL Error";
            }else{
                mysqli_stmt_bind_param($stmt,"sssssss",$BusinessName,$Date,$order,$Description,$Inflows,$Outflows,$balance);
                mysqli_stmt_execute($stmt);
            }
               
                header("Location: ../SEA/cashbook.php");
                $_SESSION ['year'] = $Year;
                $_SESSION ['month']= $Month;
                $_SESSION ['response'] = "Successfully Account Added";
                $_SESSION ['res_type']= "success";
    
            }


        }    
            


    //This Section will be the Edit/Update of Data 
    }else{  



    //Updating Values with no change of date
    if($Date == $OldDate){

    $sqlforNoAccount = "SELECT cbe_id, order_by FROM tblcashbookentry WHERE MONTH(date) = '$Month' AND YEAR(date) = '$Year' AND business_name = '$BusinessName' AND date >= '$Date' Order By date, order_by ASC";
    $stmt = $conn->prepare($sqlforNoAccount);
    $stmt->execute();
    $result = $stmt->get_result();

    $IDS = array();

    $counter = 0;
    $startToCount = 0;
    $prevID = 0;

    while ($row = $result->fetch_assoc()) {



        if($row['cbe_id'] == $ID  && $row['order_by'] == $OD){
            $startToCount = 1;
        }

        if($startToCount == 1){
        
            if($row['cbe_id'] != $ID){
                $IDS[$counter] = $row['cbe_id'];
                $counter++;
            }
        }else{
            $prevID = $row['cbe_id'];
        }

    }

    $numberNeedUpdate = count($IDS);

    
    if($prevID == 0){

    $sqlforNoAccount = "SELECT cbe_id FROM tblcashbookentry WHERE (order_by = (SELECT MAX(order_by) from tblcashbookentry WHERE date = (SELECT MAX(date) from tblcashbookentry WHERE date < '$Date' AND business_name = '$BusinessName')) AND date = (SELECT MAX(date) from tblcashbookentry WHERE date < '$Date' AND business_name = '$BusinessName')) AND (MONTH(date) = '$Month' AND YEAR(date) = '$Year' AND business_name = '$BusinessName')";
    $stmt = $conn->prepare($sqlforNoAccount);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
            $prevID = $row['cbe_id'];
    }    
    }

    $sqlforNoAccount = "SELECT balance FROM tblcashbookentry WHERE cbe_id = '$prevID'";
    $stmt = $conn->prepare($sqlforNoAccount);
    $stmt->execute();
    $result = $stmt->get_result();

    $prevBal = 0;

    while ($row = $result->fetch_assoc()) {
            $prevBal = $row['balance'];
    }


    
    if($Inflows == "0"){
        $balance = $prevBal - $Outflows;
    }else{
        $balance = $prevBal + $Inflows;
    }

    $sqlforupdateaccount="UPDATE tblcashbookentry SET cbe_id= '$ID',business_name='$BusinessName',date='$Date',order_by='$OD',description='$Description',inflows='$Inflows',outflows='$Outflows',balance='$balance' WHERE cbe_id = ?";

    $stmt = mysqli_stmt_init($conn);
    
            
            if(!mysqli_stmt_prepare($stmt, $sqlforupdateaccount)){
                echo "SQL Error";
            }else{
                mysqli_stmt_bind_param($stmt,"s",$ID);
                mysqli_stmt_execute($stmt);
            }


    for ($i=0; $i < $numberNeedUpdate ; $i++) { 
        # code...

        $sqlforNoAccount = "SELECT * FROM tblcashbookentry WHERE cbe_id = '$IDS[$i]'";
        $stmt = $conn->prepare($sqlforNoAccount);
        $stmt->execute();
        $result = $stmt->get_result();
    
        while ($row = $result->fetch_assoc()) {
                $update_inflows = $row['inflows'];
                $update_outflows = $row['outflows'];
                $update_date = $row['date'];
                $update_order = $row['order_by'];
                $update_description = $row['description'];

        }


        if($update_inflows == "0"){
            $balance = $balance - $update_outflows;
        }else{
            $balance = $balance + $update_inflows;
        }


        $sqlforupdateaccount="UPDATE tblcashbookentry SET cbe_id= '$IDS[$i]',business_name='$BusinessName',date='$update_date',order_by='$update_order',description='$update_description',inflows='$update_inflows',outflows='$update_outflows',balance='$balance' WHERE cbe_id = ?";

        $stmt = mysqli_stmt_init($conn);
        
                
                if(!mysqli_stmt_prepare($stmt, $sqlforupdateaccount)){
                    echo "SQL Error";
                }else{
                    mysqli_stmt_bind_param($stmt,"s",$IDS[$i]);
                    mysqli_stmt_execute($stmt);
                }
    }


    header("Location: ../SEA/cashbook.php");
    $_SESSION ['response'] = "Successfully Account Updated";
    $_SESSION ['res_type']= "success";
    $_SESSION ['year'] = $Year;
    $_SESSION ['month']= $Month;

        

    //Change date also
    }else{




    }
    }   
}



//Delete Record

if(isset($_POST['delete_btn'])){


    $BusinessName = mysqli_real_escape_string($conn,   $_SESSION ['business_name']);
    $ID=$_POST['delete_id'];
    $Date=$_POST['delete_date'];
    $OD=$_POST['delete_od'];
    $Month = substr($Date,5,2);
    $Year = substr($Date,0,4);


    $sqlforNoAccount = "SELECT cbe_id, order_by FROM tblcashbookentry WHERE MONTH(date) = '$Month' AND YEAR(date) = '$Year' AND business_name = '$BusinessName' AND date >= '$Date' Order By date, order_by ASC";
    $stmt = $conn->prepare($sqlforNoAccount);
    $stmt->execute();
    $result = $stmt->get_result();

    $IDS = array();

    $counter = 0;
    $startToCount = 0;
    $prevID = 0;

    while ($row = $result->fetch_assoc()) {



        if($row['cbe_id'] == $ID  && $row['order_by'] == $OD){
            $startToCount = 1;
        }

        if($startToCount == 1){
        
            if($row['cbe_id'] != $ID){
                $IDS[$counter] = $row['cbe_id'];
                $counter++;
            }
        }else{
            $prevID = $row['cbe_id'];
        }

    }

    $numberNeedUpdate = count($IDS);

    
    if($prevID == 0){

    $sqlforNoAccount = "SELECT cbe_id, order_by FROM tblcashbookentry WHERE (order_by = (SELECT MAX(order_by) from tblcashbookentry WHERE date = (SELECT MAX(date) from tblcashbookentry WHERE date < '$Date' AND business_name = '$BusinessName')) AND date = (SELECT MAX(date) from tblcashbookentry WHERE date < '$Date' AND business_name = '$BusinessName')) AND (MONTH(date) = '$Month' AND YEAR(date) = '$Year' AND business_name = '$BusinessName')";
    $stmt = $conn->prepare($sqlforNoAccount);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
            $prevID = $row['cbe_id'];
    }    
    }

    $sqlforNoAccount = "SELECT balance FROM tblcashbookentry WHERE cbe_id = '$prevID'";
    $stmt = $conn->prepare($sqlforNoAccount);
    $stmt->execute();
    $result = $stmt->get_result();

    $prevBal = 0;

    while ($row = $result->fetch_assoc()) {
            $prevBal = $row['balance'];
    }

    

    $sqlfordeletfiletask="DELETE FROM tblcashbookentry WHERE cbe_id=?";

    $stmt = mysqli_stmt_init($conn);


    if(!mysqli_stmt_prepare($stmt, $sqlfordeletfiletask)){
        echo "SQL Error";
    }else{
        mysqli_stmt_bind_param($stmt,"s",$ID);
        mysqli_stmt_execute($stmt);
    }



    $balance =  $prevBal;

    for ($i=0; $i < $numberNeedUpdate ; $i++) { 
        # code...

        $sqlforNoAccount = "SELECT * FROM tblcashbookentry WHERE cbe_id = '$IDS[$i]'";
        $stmt = $conn->prepare($sqlforNoAccount);
        $stmt->execute();
        $result = $stmt->get_result();
    
        while ($row = $result->fetch_assoc()) {
                $update_inflows = $row['inflows'];
                $update_outflows = $row['outflows'];
                $update_date = $row['date'];
                $update_order = $row['order_by'];
                $update_description = $row['description'];

        }


        if($update_inflows == "0"){
            $balance = $balance - $update_outflows;
        }else{
            $balance = $balance + $update_inflows;
        }


        $sqlforupdateaccount="UPDATE tblcashbookentry SET cbe_id= '$IDS[$i]',business_name='$BusinessName',date='$update_date',order_by='$update_order',description='$update_description',inflows='$update_inflows',outflows='$update_outflows',balance='$balance' WHERE cbe_id = ?";

        $stmt = mysqli_stmt_init($conn);
        
                
                if(!mysqli_stmt_prepare($stmt, $sqlforupdateaccount)){
                    echo "SQL Error";
                }else{
                    mysqli_stmt_bind_param($stmt,"s",$IDS[$i]);
                    mysqli_stmt_execute($stmt);
                }
    }

    $_SESSION ['year'] = $Year;
    $_SESSION ['month']= $Month;



}


if(isset($_POST['generate-monthly'])){


    $WhatWillGenerate = $_POST['whatshouldIDOM'];
    $Month = $_POST['monthG'];
    $Year = $_POST['yearM'];
    $BusinessName = mysqli_real_escape_string($conn,   $_SESSION ['business_name']);
    $BusinessOwner = mysqli_real_escape_string($conn,   $_SESSION ['business_owner']);

        $MonthDetails = array('',
        'January',
        'February',
        'March',
        'April',
        'May',
        'June',
        'July ',
        'August',
        'September',
        'October',
        'November',
        'December');

   
    

    if($WhatWillGenerate == "Income Statement"){

        

        $sqlforupdateaccount="DELETE FROM `tblincomestatement` WHERE `date_month` = ? AND `date_year` = ? AND `business_name` = ?";

        $stmt = mysqli_stmt_init($conn);
    
            
            if(!mysqli_stmt_prepare($stmt, $sqlforupdateaccount)){
                echo "SQL Error";
            }else{
                mysqli_stmt_bind_param($stmt,"sss",$Month,$Year,$BusinessName);
                mysqli_stmt_execute($stmt);
            }

        


        $sqlforNoAccount = "SELECT cbe_id FROM tblcashbookentry WHERE ((MONTH(date) = '$Month' AND YEAR(date)= '$Year') AND (business_name = '$BusinessName')) Order By date, order_by ASC";
        $stmt = $conn->prepare($sqlforNoAccount);
        $stmt->execute();
        $result = $stmt->get_result();


        $IDS = array();

        $counter = 0;
        while ($row = $result->fetch_assoc()) {
        $IDS[$counter] = $row['cbe_id'];
        $counter++;
        }

        $numberNeedUpdate = count($IDS);


        for ($i=0; $i < $numberNeedUpdate ; $i++) { 
            # code...
    
            $sqlforNoAccount = "SELECT date, description, inflows, outflows FROM tblcashbookentry WHERE cbe_id = '$IDS[$i]'";
            $stmt = $conn->prepare($sqlforNoAccount);
            $stmt->execute();
            $result = $stmt->get_result();
        
            while ($row = $result->fetch_assoc()) {
                
                    $update_description = $row['description'];
                    $update_inflows = $row['inflows'];
                    $update_outflows = $row['outflows'];
                    $last_date = $row['date'];
            }

            if($update_description == "Beginning balance" || $update_description == "Investment" || $update_description == "Bank Financing Long Term" || $update_description == "Shareholder Investment" || $update_description == "Other source of cash" || $update_description == "Amortization Expenses" || $update_description == "Loan Payments - Long term" || $update_description == "Other uses of cash" || $update_description == "Equipment" || $update_description == "Vehicle" || $update_description == "Furniture" || $update_description == "Other non-current assets" || $update_description == "Ending Balance"){

            }else{
                
                if($update_description == "Sales" || $update_description == "Service Income" || $update_description == "Interest Income" || $update_description == "Bank Financing Short Term" || $update_description == "Customer Deposits" || $update_description == "Other Income"){
                    $amount = $update_inflows;
                    $category= 'INCOME';
                }
                else{
                    $amount = $update_outflows;
                    $category= 'EXPENSES';
                }
            
            $type= 'Monthly';


            $sqlforNoAccount = "SELECT is_id, amount FROM tblincomestatement WHERE date_month = '$Month' AND date_year = '$Year' AND business_name = '$BusinessName' AND description='$update_description'";
            $sqlrun = mysqli_query($conn, $sqlforNoAccount);
    
            if(mysqli_num_rows($sqlrun)>0){
            
                $stmt = $conn->prepare($sqlforNoAccount);
                $stmt->execute();
                $result = $stmt->get_result();
    
                while ($row = $result->fetch_assoc()) {
                    $id = $row['is_id']; 
                    $prev_amount = $row['amount'];
                }

                $amount = $prev_amount + $amount;

                $sqlforAccounts = "UPDATE tblincomestatement SET amount='$amount' WHERE is_id = ?";
                
                $stmt = mysqli_stmt_init($conn);
        
                if(!mysqli_stmt_prepare($stmt, $sqlforAccounts)){
                    echo "SQL Error";
                }else{
                    mysqli_stmt_bind_param($stmt,"s",$id);
                    mysqli_stmt_execute($stmt);
                }

            }else{


                $sqlforAccounts = "INSERT INTO tblincomestatement(is_id,business_name, date_month, date_year, type, category, description, amount,details) VALUES ('',?,?,?,?,?,?,?,'');";
            
                $stmt = mysqli_stmt_init($conn);
        
                if(!mysqli_stmt_prepare($stmt, $sqlforAccounts)){
                    echo "SQL Error";
                }else{
                    mysqli_stmt_bind_param($stmt,"sssssss",$BusinessName,$Month,$Year,$type,$category,$update_description,$amount);
                    mysqli_stmt_execute($stmt);
                }
            }
            }

    
        }

        $date = DateTime::createFromFormat('Y-m-d', $last_date);

        

        $ISdetails = 'for the Month ended ' . $date->format('F d, Y');

        $sqlforAccounts = "UPDATE tblincomestatement SET details='$ISdetails' WHERE `date_month` = ? AND `date_year` = ? AND `business_name` = ?";
                
                $stmt = mysqli_stmt_init($conn);
        
                if(!mysqli_stmt_prepare($stmt, $sqlforAccounts)){
                    echo "SQL Error";
                }else{
                    mysqli_stmt_bind_param($stmt,"sss",$Month,$Year,$BusinessName);
                    mysqli_stmt_execute($stmt);
                }






        header("Location: ../SEA/income.php");
        $_SESSION ['response'] = "Successfully Income Statement Generated";
        $_SESSION ['res_type']= "success";
        $_SESSION ['year_is'] = $Year;
        $_SESSION ['month_is']= $Month;
        $_SESSION ['ISdetails']= $ISdetails;


    }
    else if($WhatWillGenerate == "Cash Flow"){


        $sqlforupdateaccount="DELETE FROM `tblcashflow` WHERE `date_month` = ? AND `date_year` = ? AND `business_name` = ?";

        $stmt = mysqli_stmt_init($conn);
    
            
            if(!mysqli_stmt_prepare($stmt, $sqlforupdateaccount)){
                echo "SQL Error";
            }else{
                mysqli_stmt_bind_param($stmt,"sss",$Month,$Year,$BusinessName);
                mysqli_stmt_execute($stmt);
            }


            $sqlforNoAccount = "SELECT cbe_id FROM tblcashbookentry WHERE ((MONTH(date) = '$Month' AND YEAR(date)= '$Year') AND (business_name = '$BusinessName')) Order By date, order_by ASC";
            $stmt = $conn->prepare($sqlforNoAccount);
            $stmt->execute();
            $result = $stmt->get_result();
    
    
            $IDS = array();
    
            $counter = 0;
            while ($row = $result->fetch_assoc()) {
            $IDS[$counter] = $row['cbe_id'];
            $counter++;
            }
    
            $numberNeedUpdate = count($IDS);
    
    
            $f = 0;
            for ($i=0; $i < $numberNeedUpdate ; $i++) { 
                # code...
        
                $sqlforNoAccount = "SELECT date, description, inflows, outflows, balance FROM tblcashbookentry WHERE cbe_id = '$IDS[$i]'";
                $stmt = $conn->prepare($sqlforNoAccount);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    
                        $update_description = $row['description'];
                        $update_inflows = $row['inflows'];
                        $update_outflows = $row['outflows'];
                        $last_date = $row['date'];

                        if($f == 0){
                            if($update_description == "Beginning balance"){
                                $first_balance = $row['balance'];
                                
                            }else{
                                $first_balance = 0;
                            }

                            $f++;
                        }
                }

                if($update_description == "Beginning balance"){

                }else{

                    
        
                if($update_inflows == "0"){
                    $amount = $update_outflows;
                    $sign= 'negative';
                }else{
                    $amount = $update_inflows;
                    $sign= 'positive';
                }
                
                $type= 'Monthly';


                if($update_description == "Equipment" || $update_description == "Vehicle" || $update_description == "Furniture"){
                    $category = 'INVESTING';

                }else if($update_description == "Investment" || $update_description == "Other source of cash" || $update_description == "Other uses of cash" || $update_description == "Bank Financing Long Term" || $update_description == "Loan Payments - Long term"){
                    $category = 'FINANCING';
                }else{
                    $category = 'OPERATING';
                }


    
    
                $sqlforNoAccount = "SELECT cf_id, amount FROM tblcashflow WHERE date_month = '$Month' AND date_year = '$Year' AND business_name = '$BusinessName' AND description='$update_description'";
                $sqlrun = mysqli_query($conn, $sqlforNoAccount);
        
                if(mysqli_num_rows($sqlrun)>0){
                
                    $stmt = $conn->prepare($sqlforNoAccount);
                    $stmt->execute();
                    $result = $stmt->get_result();
        
                    while ($row = $result->fetch_assoc()) {
                        $id = $row['cf_id']; 
                        $prev_amount = $row['amount'];
                    }
    
                    $amount = $prev_amount + $amount;
    
                    $sqlforAccounts = "UPDATE tblcashflow SET amount='$amount' WHERE cf_id = ?";
                    
                    $stmt = mysqli_stmt_init($conn);
            
                    if(!mysqli_stmt_prepare($stmt, $sqlforAccounts)){
                        echo "SQL Error";
                    }else{
                        mysqli_stmt_bind_param($stmt,"s",$id);
                        mysqli_stmt_execute($stmt);
                    }
    
                }else{
    
    
                    $sqlforAccounts = "INSERT INTO tblcashflow(cf_id,business_name, date_month, date_year, type, category, description, first_balance, amount, sign,details) VALUES ('',?,?,?,?,?,?,'',?,?,'');";
                
                    $stmt = mysqli_stmt_init($conn);
            
                    if(!mysqli_stmt_prepare($stmt, $sqlforAccounts)){
                        echo "SQL Error";
                    }else{
                        mysqli_stmt_bind_param($stmt,"ssssssss",$BusinessName,$Month,$Year,$type,$category,$update_description,$amount,$sign);
                        mysqli_stmt_execute($stmt);
                    }
                }

                }
            }
    
            $date = DateTime::createFromFormat('Y-m-d', $last_date);
    
            
    
            $ISdetails = 'for the Month ended ' . $date->format('F d, Y');
    
            $sqlforAccounts = "UPDATE tblcashflow SET details='$ISdetails', first_balance ='$first_balance' WHERE `date_month` = ? AND `date_year` = ? AND `business_name` = ?";
                    
                    $stmt = mysqli_stmt_init($conn);
            
                    if(!mysqli_stmt_prepare($stmt, $sqlforAccounts)){
                        echo "SQL Error";
                    }else{
                        mysqli_stmt_bind_param($stmt,"sss",$Month,$Year,$BusinessName);
                        mysqli_stmt_execute($stmt);
                    }
    
    
    
    
    
    
            header("Location: ../SEA/cashflow.php");
            $_SESSION ['response'] = "Successfully Cash Flow Statement Generated";
            $_SESSION ['res_type']= "success";
            $_SESSION ['year_is'] = $Year;
            $_SESSION ['month_is']= $Month;
            $_SESSION ['ISdetails']= $ISdetails;
    
    














    }
    else if($WhatWillGenerate == "Print"){

        $fileName = $BusinessName .'-Cashbook Entry Records-' . $MonthDetails[$Month].'-'.$Year.'.pdf';


        
class PDF extends FPDF
{


    function __construct()
		{
			parent::__construct();
		}

		function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false)
		{
			parent::MultiCell($w, $h, $this->normalize($txt), $border, $align, $fill);
		}

		function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
		{
			parent::Cell($w, $h, $this->normalize($txt), $border, $ln, $align, $fill, $link);
		}

		function Write($h, $txt, $link='')
		{
			parent::Write($h, $this->normalize($txt), $link);
		}

		function Text($x, $y, $txt)
		{
			parent::Text($x, $y, $this->normalize($txt));
		}

		protected function normalize($word)
		{
			// Thanks to: http://stackoverflow.com/questions/3514076/special-characters-in-fpdf-with-php
			
			$word = str_replace("@","%40",$word);
			$word = str_replace("`","%60",$word);
			$word = str_replace("¢","%A2",$word);
			$word = str_replace("£","%A3",$word);
			$word = str_replace("¥","%A5",$word);
			$word = str_replace("|","%A6",$word);
			$word = str_replace("«","%AB",$word);
			$word = str_replace("¬","%AC",$word);
			$word = str_replace("¯","%AD",$word);
			$word = str_replace("º","%B0",$word);
			$word = str_replace("±","%B1",$word);
			$word = str_replace("ª","%B2",$word);
			$word = str_replace("µ","%B5",$word);
			$word = str_replace("»","%BB",$word);
			$word = str_replace("¼","%BC",$word);
			$word = str_replace("½","%BD",$word);
			$word = str_replace("¿","%BF",$word);
			$word = str_replace("À","%C0",$word);
			$word = str_replace("Á","%C1",$word);
			$word = str_replace("Â","%C2",$word);
			$word = str_replace("Ã","%C3",$word);
			$word = str_replace("Ä","%C4",$word);
			$word = str_replace("Å","%C5",$word);
			$word = str_replace("Æ","%C6",$word);
			$word = str_replace("Ç","%C7",$word);
			$word = str_replace("È","%C8",$word);
			$word = str_replace("É","%C9",$word);
			$word = str_replace("Ê","%CA",$word);
			$word = str_replace("Ë","%CB",$word);
			$word = str_replace("Ì","%CC",$word);
			$word = str_replace("Í","%CD",$word);
			$word = str_replace("Î","%CE",$word);
			$word = str_replace("Ï","%CF",$word);
			$word = str_replace("Ð","%D0",$word);
			$word = str_replace("Ñ","%D1",$word);
			$word = str_replace("Ò","%D2",$word);
			$word = str_replace("Ó","%D3",$word);
			$word = str_replace("Ô","%D4",$word);
			$word = str_replace("Õ","%D5",$word);
			$word = str_replace("Ö","%D6",$word);
			$word = str_replace("Ø","%D8",$word);
			$word = str_replace("Ù","%D9",$word);
			$word = str_replace("Ú","%DA",$word);
			$word = str_replace("Û","%DB",$word);
			$word = str_replace("Ü","%DC",$word);
			$word = str_replace("Ý","%DD",$word);
			$word = str_replace("Þ","%DE",$word);
			$word = str_replace("ß","%DF",$word);
			$word = str_replace("à","%E0",$word);
			$word = str_replace("á","%E1",$word);
			$word = str_replace("â","%E2",$word);
			$word = str_replace("ã","%E3",$word);
			$word = str_replace("ä","%E4",$word);
			$word = str_replace("å","%E5",$word);
			$word = str_replace("æ","%E6",$word);
			$word = str_replace("ç","%E7",$word);
			$word = str_replace("è","%E8",$word);
			$word = str_replace("é","%E9",$word);
			$word = str_replace("ê","%EA",$word);
			$word = str_replace("ë","%EB",$word);
			$word = str_replace("ì","%EC",$word);
			$word = str_replace("í","%ED",$word);
			$word = str_replace("î","%EE",$word);
			$word = str_replace("ï","%EF",$word);
			$word = str_replace("ð","%F0",$word);
			$word = str_replace("ñ","%F1",$word);
			$word = str_replace("ò","%F2",$word);
			$word = str_replace("ó","%F3",$word);
			$word = str_replace("ô","%F4",$word);
			$word = str_replace("õ","%F5",$word);
			$word = str_replace("ö","%F6",$word);
			$word = str_replace("÷","%F7",$word);
			$word = str_replace("ø","%F8",$word);
			$word = str_replace("ù","%F9",$word);
			$word = str_replace("ú","%FA",$word);
			$word = str_replace("û","%FB",$word);
			$word = str_replace("ü","%FC",$word);
			$word = str_replace("ý","%FD",$word);
			$word = str_replace("þ","%FE",$word);
			$word = str_replace("ÿ","%FF",$word);

			return urldecode($word);
		}
// Page header
function Header()
{
    // Logo
   // $this->Image('../img/logo.png',100,6,170);
    // Arial bold 15
    $this->SetFont('Arial','B',14);
    // Move to the right
    
    $this->Ln(10);
    // Title
    $this->Cell(100);
    $this->Cell(10,10,'CASHBOOK ENTRY RECORDS',0,0,'C');
    $this->Ln(7);
    // Line break
    $this->Ln(10);
}

// Page footer
function Footer()
{
    // Position at 1.5 cm from bottom
    $this->SetY(-15);
    // Arial italic 8
    $this->SetFont('Arial','I',8);
    // Page number
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
}

// Page footer
function headerTable()
{
  
    // Arial italic 8
    $this->SetFont('Arial','B',10);
    // Page number
    $this->Cell(5);
    $this->Cell(22,10,'Date',1,0,'C');
    $this->Cell(78,10,'Description',1,0,'C');
    $this->Cell(30,10,'Inflows',1,0,'C');
    $this->Cell(30,10,'Outflows',1,0,'C');
    $this->Cell(30,10,'Balance',1,0,'C');
    $this->Ln();
}
function queryTable($Month, $Year, $BusinessName,$conn){
        $this->SetFont('Arial','B',10);
       
      
        $sqlforNoAccount = "SELECT date, description, inflows, outflows, balance FROM tblcashbookentry WHERE ((MONTH(date) = '$Month' AND YEAR(date)= '$Year') AND (business_name = '$BusinessName')) Order By date, order_by ASC";
        $stmt = $conn->prepare($sqlforNoAccount);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
                $this->Cell(5);
                $this->Cell(22,10,$row['date'],1,0,'L');
                $this->Cell(78,10,$row['description'],1,0,'L');
                $this->Cell(30,10,number_format($row['inflows']),1,0,'C');
                $this->Cell(30,10,number_format($row['outflows']),1,0,'C');
                $this->Cell(30,10,number_format($row['balance']),1,0,'C');
                $this->Ln();
        }
}

function Signatory($BusinessOwner, $BusinessName)
{
    $this->Ln(15);
    $this->SetFont('Arial','B',12);
    $this->Cell(15);
    $this->Cell(0,10,$BusinessOwner,0,0,'R');
    $this->Ln(6);
    $this->Cell(15);
    $this->SetFont('Arial','B',12);
    $this->Cell(0,10,$BusinessName .', Owner',0,0,'R');
    // Line break
    $this->Ln(20);
}


function Month($MonthDetails, $Year)
{
    $this->Ln(8);
    $this->SetFont('Arial','B',12);
    $this->Cell(15);
    $this->Cell(0,10,'Month of '.$MonthDetails . ' ' . $Year,0,0,'C');
    // Line break
    $this->Ln(10);
}

}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage('P','Legal',0);
$pdf->Month($MonthDetails[$Month], $Year);
$pdf->headerTable();
$pdf->queryTable($Month, $Year, $BusinessName,$conn);
$pdf->Signatory($BusinessOwner, $BusinessName);
$pdf->Output($fileName, 'D');


    }
    else if($WhatWillGenerate == "Download"){

        $spreadsheet = new Spreadsheet();
        $Excel_writer = new Xlsx($spreadsheet);


        $spreadsheet->getProperties()->setCreator($BusinessOwner)
        ->setLastModifiedBy($BusinessOwner)
        ->setTitle($BusinessName.'-Cashbook Entry Report-'.$MonthDetails[$Month].'-'.$Year )
        ->setSubject('Cashbook Entry Report')
        ->setDescription('Cashbook Entry Report')
        ->setKeywords('Cashbook Entry Report')
        ->setCategory('Cashbook Entry Report');
          
        $spreadsheet->setActiveSheetIndex(0);
        $activeSheet = $spreadsheet->getActiveSheet();
          
        $activeSheet->setCellValue('A1', 'Date');
        $activeSheet->setCellValue('B1', 'Description');
        $activeSheet->setCellValue('C1', 'Inflows');
        $activeSheet->setCellValue('D1', 'Ouflows');
        $activeSheet->setCellValue('E1', 'Balance');

        $activeSheet->setTitle($MonthDetails[$Month]);

        foreach ($activeSheet->getColumnIterator() as $column) {
            $activeSheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
        }

        $styleArray = [
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'center' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];

        $activeSheet->getStyle('A1:E1')->applyFromArray($styleArray);

          
        $sqlforNoAccount = "SELECT date, description, inflows, outflows, balance FROM tblcashbookentry WHERE ((MONTH(date) = '$Month' AND YEAR(date)= '$Year') AND (business_name = '$BusinessName')) Order By date, order_by ASC";
        $stmt = $conn->prepare($sqlforNoAccount);
        $stmt->execute();
        $result = $stmt->get_result();

        $i = 2;
        while ($row = $result->fetch_assoc()) {
                $activeSheet->setCellValue('A'.$i , $row['date']);
                $activeSheet->setCellValue('B'.$i , $row['description']);
                $activeSheet->setCellValue('C'.$i , $row['inflows']);
                $activeSheet->setCellValue('D'.$i , $row['outflows']);
                $activeSheet->setCellValue('E'.$i , $row['balance']);
                $i++;
            }
        
          
        $filename = $BusinessName .'-Cashbook Entry Records-' . $MonthDetails[$Month].'-'.$Year.'.xlsx';

          
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename='. $filename);
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');

    }else{
        header("Location: ../SEA/cashbook.php");
        $_SESSION ['year'] = date("Y");
        $_SESSION ['month']= date("m");
    }

}



if(isset($_POST['generate-quarterly'])){


    $WhatWillGenerate = $_POST['whatshouldIDOQ'];
    $Quarter = $_POST['quarterG'];
    $Year = $_POST['yearQ'];
    $BusinessName = mysqli_real_escape_string($conn,   $_SESSION ['business_name']);
    $BusinessOwner = mysqli_real_escape_string($conn,   $_SESSION ['business_owner']);

    
    $Months = array();

    $MonthDetails = array('',
    'January',
    'February',
    'March',
    'April',
    'May',
    'June',
    'July ',
    'August',
    'September',
    'October',
    'November',
    'December',);

    
    if($Quarter == "Q1"){
        $Months[0] = 1;
        $Months[1] = 2;
        $Months[2] = 3;
    }
    else if($Quarter == "Q2"){
        $Months[0] = 4;
        $Months[1] = 5;
        $Months[2] = 6;
    }
    else if($Quarter == "Q3"){
        $Months[0] = 7;
        $Months[1] = 8;
        $Months[2] = 9;
    }
    else if($Quarter == "Q4"){
        $Months[0] = 10;
        $Months[1] = 11;
        $Months[2] = 12;
    }









    if($WhatWillGenerate == "Income Statement"){

        $sqlforupdateaccount="DELETE FROM `tblincomestatement` WHERE `date_month` = ? AND `date_year` = ? AND `business_name` = ?";

        $stmt = mysqli_stmt_init($conn);
    
            
            if(!mysqli_stmt_prepare($stmt, $sqlforupdateaccount)){
                echo "SQL Error";
            }else{
                mysqli_stmt_bind_param($stmt,"sss",$Quarter,$Year,$BusinessName);
                mysqli_stmt_execute($stmt);
            }


        $sqlforNoAccount = "SELECT cbe_id FROM tblcashbookentry WHERE ((MONTH(date) >= '$Months[0]' AND MONTH(date) <= '$Months[2]') AND (YEAR(date)= '$Year' AND business_name = '$BusinessName')) Order By date, order_by ASC";
        $stmt = $conn->prepare($sqlforNoAccount);
        $stmt->execute();
        $result = $stmt->get_result();


        $IDS = array();

        $counter = 0;
        while ($row = $result->fetch_assoc()) {
        $IDS[$counter] = $row['cbe_id'];
        $counter++;
        }

        $numberNeedUpdate = count($IDS);

        $f = 0;
        for ($i=0; $i < $numberNeedUpdate ; $i++) { 
            # code...
    
            $sqlforNoAccount = "SELECT date, description, inflows, outflows FROM tblcashbookentry WHERE cbe_id = '$IDS[$i]'";
            $stmt = $conn->prepare($sqlforNoAccount);
            $stmt->execute();
            $result = $stmt->get_result();
        
            while ($row = $result->fetch_assoc()) {

                    $update_description = $row['description'];
                    $update_inflows = $row['inflows'];
                    $update_outflows = $row['outflows'];
                    
                    if($f == 0){
                        //$first_date = $row['date'];
                        $f++;
                    }else{
                        $last_date = $row['date'];
                    }

            }

            if($update_description == "Beginning balance" || $update_description == "Investment" || $update_description == "Bank Financing Long Term" || $update_description == "Shareholder Investment" || $update_description == "Other source of cash" || $update_description == "Amortization Expenses" || $update_description == "Loan Payments - Long term" || $update_description == "Other uses of cash" || $update_description == "Equipment" || $update_description == "Vehicle" || $update_description == "Furniture" || $update_description == "Other non-current assets" || $update_description == "Ending Balance"){

            }else{

                if($update_description == "Sales" || $update_description == "Service Income" || $update_description == "Interest Income" || $update_description == "Bank Financing Short Term" || $update_description == "Customer Deposits" || $update_description == "Other Income"){
                    $amount = $update_inflows;
                    $category= 'INCOME';
                }
                else{
                    $amount = $update_outflows;
                    $category= 'EXPENSES';
                }
                
                $type= 'Quarterly';
    
    
                $sqlforNoAccount = "SELECT is_id, amount FROM tblincomestatement WHERE date_month = '$Quarter' AND date_year = '$Year' AND business_name = '$BusinessName' AND description='$update_description'";
                $sqlrun = mysqli_query($conn, $sqlforNoAccount);
        
                if(mysqli_num_rows($sqlrun)>0){
                
                    $stmt = $conn->prepare($sqlforNoAccount);
                    $stmt->execute();
                    $result = $stmt->get_result();
        
                    while ($row = $result->fetch_assoc()) {
                        $id = $row['is_id']; 
                        $prev_amount = $row['amount'];
                    }
    
                    $amount = $prev_amount + $amount;
    
                    $sqlforAccounts = "UPDATE tblincomestatement SET amount='$amount' WHERE is_id = ?";
                    
                    $stmt = mysqli_stmt_init($conn);
            
                    if(!mysqli_stmt_prepare($stmt, $sqlforAccounts)){
                        echo "SQL Error";
                    }else{
                        mysqli_stmt_bind_param($stmt,"s",$id);
                        mysqli_stmt_execute($stmt);
                    }
    
                }else{
    
    
                    $sqlforAccounts = "INSERT INTO tblincomestatement(is_id,business_name, date_month, date_year, type, category, description, amount) VALUES ('',?,?,?,?,?,?,?);";
                
                    $stmt = mysqli_stmt_init($conn);
            
                    if(!mysqli_stmt_prepare($stmt, $sqlforAccounts)){
                        echo "SQL Error";
                    }else{
                        mysqli_stmt_bind_param($stmt,"sssssss",$BusinessName,$Quarter,$Year,$type,$category,$update_description,$amount);
                        mysqli_stmt_execute($stmt);
                    }
                }
                
            }

    
           
        }


        //$date1 = DateTime::createFromFormat('Y-m-d', $first_date);
        $date2 = DateTime::createFromFormat('Y-m-d', $last_date);

        

        $ISdetails = 'for the Quarter ended ' . $date2->format('F d, Y');

        $sqlforAccounts = "UPDATE tblincomestatement SET details='$ISdetails' WHERE `date_month` = ? AND `date_year` = ? AND `business_name` = ?";
                
        $stmt = mysqli_stmt_init($conn);

        if(!mysqli_stmt_prepare($stmt, $sqlforAccounts)){
            echo "SQL Error";
        }else{
            mysqli_stmt_bind_param($stmt,"sss",$Quarter,$Year,$BusinessName);
            mysqli_stmt_execute($stmt);
        }



        header("Location: ../SEA/income.php");
        $_SESSION ['response'] = "Successfully Income Statement Generated";
        $_SESSION ['res_type']= "success";
        $_SESSION ['year_is'] = $Year;
        $_SESSION ['month_is']= $Quarter;
        $_SESSION ['ISdetails']= $ISdetails;



       

    }
    else if($WhatWillGenerate == "Cash Flow"){

        $sqlforupdateaccount="DELETE FROM `tblcashflow` WHERE `date_month` = ? AND `date_year` = ? AND `business_name` = ?";

        $stmt = mysqli_stmt_init($conn);
    
            
            if(!mysqli_stmt_prepare($stmt, $sqlforupdateaccount)){
                echo "SQL Error";
            }else{
                mysqli_stmt_bind_param($stmt,"sss",$Quarter,$Year,$BusinessName);
                mysqli_stmt_execute($stmt);
            }


        $sqlforNoAccount = "SELECT cbe_id FROM tblcashbookentry WHERE ((MONTH(date) >= '$Months[0]' AND MONTH(date) <= '$Months[2]') AND (YEAR(date)= '$Year' AND business_name = '$BusinessName')) Order By date, order_by ASC";
        $stmt = $conn->prepare($sqlforNoAccount);
        $stmt->execute();
        $result = $stmt->get_result();


        $IDS = array();

        $counter = 0;
        while ($row = $result->fetch_assoc()) {
        $IDS[$counter] = $row['cbe_id'];
        $counter++;
        }

        $numberNeedUpdate = count($IDS);

        $f = 0;
        for ($i=0; $i < $numberNeedUpdate ; $i++) { 
            # code...
    
            $sqlforNoAccount = "SELECT date, description, inflows, outflows, balance FROM tblcashbookentry WHERE cbe_id = '$IDS[$i]'";
            $stmt = $conn->prepare($sqlforNoAccount);
            $stmt->execute();
            $result = $stmt->get_result();
        
            while ($row = $result->fetch_assoc()) {

                    $update_description = $row['description'];
                    $update_inflows = $row['inflows'];
                    $update_outflows = $row['outflows'];

                    
                    if($f == 0){
                        if($update_description == "Beginning balance"){
                            $first_balance = $row['balance'];
                            
                        }else{
                            $first_balance = 0;
                        }

                        $f++;
                    }else{
                        $last_date = $row['date'];
                    }

            }


            if($update_description == "Beginning balance"){

                

            }else{

                if($update_inflows == "0"){
                    $amount = $update_outflows;
                    $sign= 'negative';
                }else{
                    $amount = $update_inflows;
                    $sign= 'positive';
                }
                
                $type = 'Quarterly';
    
    
                if($update_description == "Equipment" || $update_description == "Vehicle" || $update_description == "Furniture"){
                    $category = 'INVESTING';
    
                }else if($update_description == "Investment" || $update_description == "Other source of cash" || $update_description == "Other uses of cash" || $update_description == "Bank Financing Long Term" || $update_description == "Loan Payments - Long term"){
                    $category = 'FINANCING';
                }else{
                    $category = 'OPERATING';
                }
    
                
    
    
                $sqlforNoAccount = "SELECT cf_id, amount FROM tblcashflow WHERE date_month = '$Quarter' AND date_year = '$Year' AND business_name = '$BusinessName' AND description='$update_description'";
                $sqlrun = mysqli_query($conn, $sqlforNoAccount);
        
                if(mysqli_num_rows($sqlrun)>0){
                
                    $stmt = $conn->prepare($sqlforNoAccount);
                    $stmt->execute();
                    $result = $stmt->get_result();
        
                    while ($row = $result->fetch_assoc()) {
                        $id = $row['cf_id']; 
                        $prev_amount = $row['amount'];
                    }
    
                    $amount = $prev_amount + $amount;
    
                    $sqlforAccounts = "UPDATE tblcashflow SET amount='$amount' WHERE cf_id = ?";
                    
                    $stmt = mysqli_stmt_init($conn);
            
                    if(!mysqli_stmt_prepare($stmt, $sqlforAccounts)){
                        echo "SQL Error";
                    }else{
                        mysqli_stmt_bind_param($stmt,"s",$id);
                        mysqli_stmt_execute($stmt);
                    }
    
                }else{
    
    
                    $sqlforAccounts = "INSERT INTO tblcashflow(cf_id, business_name, date_month, date_year, type, category, description, first_balance, amount, sign, details) VALUES ('',?,?,?,?,?,?,'',?,?,'');";
                
                    $stmt = mysqli_stmt_init($conn);
            
                    if(!mysqli_stmt_prepare($stmt, $sqlforAccounts)){
                        echo "SQL Error";
                    }else{
                        mysqli_stmt_bind_param($stmt,"ssssssss",$BusinessName,$Quarter,$Year,$type,$category,$update_description,$amount,$sign);
                        mysqli_stmt_execute($stmt);
                    }
                }
                
            }

    
            
        }


       // $date1 = DateTime::createFromFormat('Y-m-d', $first_date);
        $date2 = DateTime::createFromFormat('Y-m-d', $last_date);

        

        $ISdetails = 'for the Quarter ended ' . $date2->format('F d, Y');

        $sqlforAccounts = "UPDATE tblcashflow SET details='$ISdetails', first_balance='$first_balance' WHERE `date_month` = ? AND `date_year` = ? AND `business_name` = ?";
                
        $stmt = mysqli_stmt_init($conn);

        if(!mysqli_stmt_prepare($stmt, $sqlforAccounts)){
            echo "SQL Error";
        }else{
            mysqli_stmt_bind_param($stmt,"sss",$Quarter,$Year,$BusinessName);
            mysqli_stmt_execute($stmt);
        }



        header("Location: ../SEA/cashflow.php");
        $_SESSION ['response'] = "Successfully Cash Flows Statement Generated";
        $_SESSION ['res_type']= "success";
        $_SESSION ['year_is'] = $Year;
        $_SESSION ['month_is']= $Quarter;
        $_SESSION ['ISdetails']= $ISdetails;


        

    }
    else if($WhatWillGenerate == "Print"){
        
        $fileName = $BusinessName .'-Cashbook Entry Records-' . $MonthDetails[$Months[0]].'-to-'.$MonthDetails[$Months[2]].'-'.$Year.'.pdf';

        
        
class PDF extends FPDF
{

    function __construct()
		{
			parent::__construct();
		}

		function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false)
		{
			parent::MultiCell($w, $h, $this->normalize($txt), $border, $align, $fill);
		}

		function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
		{
			parent::Cell($w, $h, $this->normalize($txt), $border, $ln, $align, $fill, $link);
		}

		function Write($h, $txt, $link='')
		{
			parent::Write($h, $this->normalize($txt), $link);
		}

		function Text($x, $y, $txt)
		{
			parent::Text($x, $y, $this->normalize($txt));
		}

		protected function normalize($word)
		{
			// Thanks to: http://stackoverflow.com/questions/3514076/special-characters-in-fpdf-with-php
			
			$word = str_replace("@","%40",$word);
			$word = str_replace("`","%60",$word);
			$word = str_replace("¢","%A2",$word);
			$word = str_replace("£","%A3",$word);
			$word = str_replace("¥","%A5",$word);
			$word = str_replace("|","%A6",$word);
			$word = str_replace("«","%AB",$word);
			$word = str_replace("¬","%AC",$word);
			$word = str_replace("¯","%AD",$word);
			$word = str_replace("º","%B0",$word);
			$word = str_replace("±","%B1",$word);
			$word = str_replace("ª","%B2",$word);
			$word = str_replace("µ","%B5",$word);
			$word = str_replace("»","%BB",$word);
			$word = str_replace("¼","%BC",$word);
			$word = str_replace("½","%BD",$word);
			$word = str_replace("¿","%BF",$word);
			$word = str_replace("À","%C0",$word);
			$word = str_replace("Á","%C1",$word);
			$word = str_replace("Â","%C2",$word);
			$word = str_replace("Ã","%C3",$word);
			$word = str_replace("Ä","%C4",$word);
			$word = str_replace("Å","%C5",$word);
			$word = str_replace("Æ","%C6",$word);
			$word = str_replace("Ç","%C7",$word);
			$word = str_replace("È","%C8",$word);
			$word = str_replace("É","%C9",$word);
			$word = str_replace("Ê","%CA",$word);
			$word = str_replace("Ë","%CB",$word);
			$word = str_replace("Ì","%CC",$word);
			$word = str_replace("Í","%CD",$word);
			$word = str_replace("Î","%CE",$word);
			$word = str_replace("Ï","%CF",$word);
			$word = str_replace("Ð","%D0",$word);
			$word = str_replace("Ñ","%D1",$word);
			$word = str_replace("Ò","%D2",$word);
			$word = str_replace("Ó","%D3",$word);
			$word = str_replace("Ô","%D4",$word);
			$word = str_replace("Õ","%D5",$word);
			$word = str_replace("Ö","%D6",$word);
			$word = str_replace("Ø","%D8",$word);
			$word = str_replace("Ù","%D9",$word);
			$word = str_replace("Ú","%DA",$word);
			$word = str_replace("Û","%DB",$word);
			$word = str_replace("Ü","%DC",$word);
			$word = str_replace("Ý","%DD",$word);
			$word = str_replace("Þ","%DE",$word);
			$word = str_replace("ß","%DF",$word);
			$word = str_replace("à","%E0",$word);
			$word = str_replace("á","%E1",$word);
			$word = str_replace("â","%E2",$word);
			$word = str_replace("ã","%E3",$word);
			$word = str_replace("ä","%E4",$word);
			$word = str_replace("å","%E5",$word);
			$word = str_replace("æ","%E6",$word);
			$word = str_replace("ç","%E7",$word);
			$word = str_replace("è","%E8",$word);
			$word = str_replace("é","%E9",$word);
			$word = str_replace("ê","%EA",$word);
			$word = str_replace("ë","%EB",$word);
			$word = str_replace("ì","%EC",$word);
			$word = str_replace("í","%ED",$word);
			$word = str_replace("î","%EE",$word);
			$word = str_replace("ï","%EF",$word);
			$word = str_replace("ð","%F0",$word);
			$word = str_replace("ñ","%F1",$word);
			$word = str_replace("ò","%F2",$word);
			$word = str_replace("ó","%F3",$word);
			$word = str_replace("ô","%F4",$word);
			$word = str_replace("õ","%F5",$word);
			$word = str_replace("ö","%F6",$word);
			$word = str_replace("÷","%F7",$word);
			$word = str_replace("ø","%F8",$word);
			$word = str_replace("ù","%F9",$word);
			$word = str_replace("ú","%FA",$word);
			$word = str_replace("û","%FB",$word);
			$word = str_replace("ü","%FC",$word);
			$word = str_replace("ý","%FD",$word);
			$word = str_replace("þ","%FE",$word);
			$word = str_replace("ÿ","%FF",$word);

			return urldecode($word);
		}

// Page header
function Header()
{
    // Logo
   // $this->Image('../img/logo.png',100,6,170);
    // Arial bold 15
    $this->SetFont('Arial','B',14);
    // Move to the right
    
    $this->Ln(10);
    // Title
    $this->Cell(100);
    $this->Cell(10,10,'CASHBOOK ENTRY RECORDS',0,0,'C');
    $this->Ln(7);
    // Line break
    $this->Ln(10);
}

// Page footer
function Footer()
{
    // Position at 1.5 cm from bottom
    $this->SetY(-15);
    // Arial italic 8
    $this->SetFont('Arial','I',8);
    // Page number
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
}

// Page footer
function headerTable()
{
  
    // Arial italic 8
    $this->SetFont('Arial','B',10);
    // Page number
    $this->Cell(5);
    $this->Cell(22,10,'Date',1,0,'C');
    $this->Cell(78,10,'Description',1,0,'C');
    $this->Cell(30,10,'Inflows',1,0,'C');
    $this->Cell(30,10,'Outflows',1,0,'C');
    $this->Cell(30,10,'Balance',1,0,'C');
    $this->Ln();
}
function queryTable($Month, $Year, $BusinessName,$conn){
        $this->SetFont('Arial','B',10);
       
      
        $sqlforNoAccount = "SELECT date, description, inflows, outflows, balance FROM tblcashbookentry WHERE ((MONTH(date) = '$Month' AND YEAR(date)= '$Year') AND (business_name = '$BusinessName')) Order By date, order_by ASC";
        $stmt = $conn->prepare($sqlforNoAccount);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
                $this->Cell(5);
                $this->Cell(22,10,$row['date'],1,0,'L');
                $this->Cell(78,10,$row['description'],1,0,'L');
                $this->Cell(30,10,number_format($row['inflows']),1,0,'C');
                $this->Cell(30,10,number_format($row['outflows']),1,0,'C');
                $this->Cell(30,10,number_format($row['balance']),1,0,'C');
                $this->Ln();
        }
}

function Signatory($BusinessOwner, $BusinessName)
{
    $this->Ln(15);
    $this->SetFont('Arial','B',12);
    $this->Cell(15);
    $this->Cell(0,10,$BusinessOwner,0,0,'R');
    $this->Ln(6);
    $this->Cell(15);
    $this->SetFont('Arial','B',12);
    $this->Cell(0,10,$BusinessName .', Owner',0,0,'R');
    // Line break
    $this->Ln(20);
}


function Month($MonthDetails, $Year)
{
    $this->Ln(8);
    $this->SetFont('Arial','B',12);
    $this->Cell(15);
    $this->Cell(0,10,'Month of '.$MonthDetails . ' ' . $Year,0,0,'C');
    // Line break
    $this->Ln(10);
}

}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage('P','Legal',0);

for ($i=0; $i <=2 ; $i++) { 
    # code...
    $pdf->Month($MonthDetails[$Months[$i]], $Year);
    $pdf->headerTable();
    $pdf->queryTable($Months[$i], $Year, $BusinessName,$conn);
}

$pdf->Signatory($BusinessOwner, $BusinessName);
$pdf->Output($fileName, 'D');
      
        


    }
    else if($WhatWillGenerate == "Download"){


        
        $spreadsheet = new Spreadsheet();
        $Excel_writer = new Xlsx($spreadsheet);


        $spreadsheet->getProperties()->setCreator($BusinessOwner)
        ->setLastModifiedBy($BusinessOwner)
        ->setTitle($BusinessName.'-Cashbook Entry Report-'.$MonthDetails[$Months[0]].'-to-'.$MonthDetails[$Months[2]].'-'.$Year )
        ->setSubject('Cashbook Entry Report')
        ->setDescription('Cashbook Entry Report')
        ->setKeywords('Cashbook Entry Report')
        ->setCategory('Cashbook Entry Report');

        for ($r=0; $r <= 2 ; $r++) { 
            # code...

        $spreadsheet->setActiveSheetIndex($r);
        $activeSheet = $spreadsheet->getActiveSheet();
          
        $activeSheet->setCellValue('A1', 'Date');
        $activeSheet->setCellValue('B1', 'Description');
        $activeSheet->setCellValue('C1', 'Inflows');
        $activeSheet->setCellValue('D1', 'Ouflows');
        $activeSheet->setCellValue('E1', 'Balance');

        $activeSheet->setTitle($MonthDetails[$Months[$r]]);

        foreach ($activeSheet->getColumnIterator() as $column) {
            $activeSheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
        }

        $styleArray = [
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'center' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];

        $activeSheet->getStyle('A1:E1')->applyFromArray($styleArray);

          
        $sqlforNoAccount = "SELECT date, description, inflows, outflows, balance FROM tblcashbookentry WHERE ((MONTH(date) = '$Months[$r]' AND YEAR(date)= '$Year') AND (business_name = '$BusinessName')) Order By date, order_by ASC";
        $stmt = $conn->prepare($sqlforNoAccount);
        $stmt->execute();
        $result = $stmt->get_result();

        $i = 2;
        while ($row = $result->fetch_assoc()) {
                $activeSheet->setCellValue('A'.$i , $row['date']);
                $activeSheet->setCellValue('B'.$i , $row['description']);
                $activeSheet->setCellValue('C'.$i , $row['inflows']);
                $activeSheet->setCellValue('D'.$i , $row['outflows']);
                $activeSheet->setCellValue('E'.$i , $row['balance']);
                $i++;
            }
        


            $spreadsheet->createSheet();
        }
          
        $spreadsheet->setActiveSheetIndex(0);
          
        $filename = $BusinessName .'-Cashbook Entry Records-' . $MonthDetails[$Months[0]].'-to-'.$MonthDetails[$Months[2]].'-'.$Year.'.xlsx';

          
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename='. $filename);
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');


       

    }else{
        
    }




}


if(isset($_POST['generate-yearly'])){

    
   
    $WhatWillGenerate = $_POST['whatshouldIDOY'];
    $Year = $_POST['yearY'];
    $BusinessName = mysqli_real_escape_string($conn,   $_SESSION ['business_name']);
    $BusinessOwner = mysqli_real_escape_string($conn,   $_SESSION ['business_owner']);


    $Months = array('',
    '1',
    '2',
    '3',
    '4',
    '5',
    '6',
    '7 ',
    '8',
    '9',
    '10',
    '11',
    '12',);

    $MonthDetails = array('',
    'January',
    'February',
    'March',
    'April',
    'May',
    'June',
    'July ',
    'August',
    'September',
    'October',
    'November',
    'December',);




    


    if($WhatWillGenerate == "Income Statement"){

        $sqlforupdateaccount="DELETE FROM `tblincomestatement` WHERE `date_month` = ? AND `date_year` = ? AND `business_name` = ?";

        $stmt = mysqli_stmt_init($conn);
    
            
            if(!mysqli_stmt_prepare($stmt, $sqlforupdateaccount)){
                echo "SQL Error";
            }else{
                mysqli_stmt_bind_param($stmt,"sss",$Year,$Year,$BusinessName);
                mysqli_stmt_execute($stmt);
            }


        $sqlforNoAccount = "SELECT cbe_id FROM tblcashbookentry WHERE ((MONTH(date) >= '$Months[1]' AND MONTH(date) <= '$Months[12]') AND (YEAR(date)= '$Year' AND business_name = '$BusinessName')) Order By date, order_by ASC";
        $stmt = $conn->prepare($sqlforNoAccount);
        $stmt->execute();
        $result = $stmt->get_result();


        $IDS = array();

        $counter = 0;
        while ($row = $result->fetch_assoc()) {
        $IDS[$counter] = $row['cbe_id'];
        $counter++;
        }

        $numberNeedUpdate = count($IDS);

        $f = 0;
        for ($i=0; $i < $numberNeedUpdate ; $i++) { 
            # code...
    
            $sqlforNoAccount = "SELECT date, description, inflows, outflows FROM tblcashbookentry WHERE cbe_id = '$IDS[$i]'";
            $stmt = $conn->prepare($sqlforNoAccount);
            $stmt->execute();
            $result = $stmt->get_result();
        
            while ($row = $result->fetch_assoc()) {

                    $update_description = $row['description'];
                    $update_inflows = $row['inflows'];
                    $update_outflows = $row['outflows'];
                    
                    if($f == 0){
                        //$first_date = $row['date'];
                        $f++;
                    }else{
                        $last_date = $row['date'];
                    }

            }

            if($update_description == "Beginning balance" || $update_description == "Investment" || $update_description == "Bank Financing Long Term" || $update_description == "Shareholder Investment" || $update_description == "Other source of cash" || $update_description == "Amortization Expenses" || $update_description == "Loan Payments - Long term" || $update_description == "Other uses of cash" || $update_description == "Equipment" || $update_description == "Vehicle" || $update_description == "Furniture" || $update_description == "Other non-current assets" || $update_description == "Ending Balance"){

            }else{

                if($update_description == "Sales" || $update_description == "Service Income" || $update_description == "Interest Income" || $update_description == "Bank Financing Short Term" || $update_description == "Customer Deposits" || $update_description == "Other Income"){
                    $amount = $update_inflows;
                    $category= 'INCOME';
                }
                else{
                    $amount = $update_outflows;
                    $category= 'EXPENSES';
                }
                
                $type= 'Yearly';
    
    
                $sqlforNoAccount = "SELECT is_id, amount FROM tblincomestatement WHERE date_month = '$Year' AND date_year = '$Year' AND business_name = '$BusinessName' AND description='$update_description'";
                $sqlrun = mysqli_query($conn, $sqlforNoAccount);
        
                if(mysqli_num_rows($sqlrun)>0){
                
                    $stmt = $conn->prepare($sqlforNoAccount);
                    $stmt->execute();
                    $result = $stmt->get_result();
        
                    while ($row = $result->fetch_assoc()) {
                        $id = $row['is_id']; 
                        $prev_amount = $row['amount'];
                    }
    
                    $amount = $prev_amount + $amount;
    
                    $sqlforAccounts = "UPDATE tblincomestatement SET amount='$amount' WHERE is_id = ?";
                    
                    $stmt = mysqli_stmt_init($conn);
            
                    if(!mysqli_stmt_prepare($stmt, $sqlforAccounts)){
                        echo "SQL Error";
                    }else{
                        mysqli_stmt_bind_param($stmt,"s",$id);
                        mysqli_stmt_execute($stmt);
                    }
    
                }else{
    
    
                    $sqlforAccounts = "INSERT INTO tblincomestatement(is_id,business_name, date_month, date_year, type, category, description, amount) VALUES ('',?,?,?,?,?,?,?);";
                
                    $stmt = mysqli_stmt_init($conn);
            
                    if(!mysqli_stmt_prepare($stmt, $sqlforAccounts)){
                        echo "SQL Error";
                    }else{
                        mysqli_stmt_bind_param($stmt,"sssssss",$BusinessName,$Year,$Year,$type,$category,$update_description,$amount);
                        mysqli_stmt_execute($stmt);
                    }
                }
                
            }
        }


       // $date1 = DateTime::createFromFormat('Y-m-d', $first_date);
        $date2 = DateTime::createFromFormat('Y-m-d', $last_date);

        

        $ISdetails = 'for the Year ended ' . $date2->format('F d, Y');

        $sqlforAccounts = "UPDATE tblincomestatement SET details='$ISdetails' WHERE `date_month` = ? AND `date_year` = ? AND `business_name` = ?";
                
        $stmt = mysqli_stmt_init($conn);

        if(!mysqli_stmt_prepare($stmt, $sqlforAccounts)){
            echo "SQL Error";
        }else{
            mysqli_stmt_bind_param($stmt,"sss",$Year,$Year,$BusinessName);
            mysqli_stmt_execute($stmt);
        }




        header("Location: ../SEA/income.php");
        $_SESSION ['response'] = "Successfully Income Statement Generated";
        $_SESSION ['res_type']= "success";
        $_SESSION ['year_is'] = $Year;
        $_SESSION ['month_is']= $Year;
        $_SESSION ['ISdetails']= $ISdetails;



       

    

      

    }
    else if($WhatWillGenerate == "Cash Flow"){

        $sqlforupdateaccount="DELETE FROM `tblcashflow` WHERE `date_month` = ? AND `date_year` = ? AND `business_name` = ?";

        $stmt = mysqli_stmt_init($conn);
    
            
            if(!mysqli_stmt_prepare($stmt, $sqlforupdateaccount)){
                echo "SQL Error";
            }else{
                mysqli_stmt_bind_param($stmt,"sss",$Year,$Year,$BusinessName);
                mysqli_stmt_execute($stmt);
            }


        $sqlforNoAccount = "SELECT cbe_id FROM tblcashbookentry WHERE ((MONTH(date) >= '$Months[1]' AND MONTH(date) <= '$Months[12]') AND (YEAR(date)= '$Year' AND business_name = '$BusinessName')) Order By date, order_by ASC";
        $stmt = $conn->prepare($sqlforNoAccount);
        $stmt->execute();
        $result = $stmt->get_result();


        $IDS = array();

        $counter = 0;
        while ($row = $result->fetch_assoc()) {
        $IDS[$counter] = $row['cbe_id'];
        $counter++;
        }

        $numberNeedUpdate = count($IDS);

        $f = 0;
        for ($i=0; $i < $numberNeedUpdate ; $i++) { 
            # code...
    
            $sqlforNoAccount = "SELECT date, description, inflows, outflows, balance FROM tblcashbookentry WHERE cbe_id = '$IDS[$i]'";
            $stmt = $conn->prepare($sqlforNoAccount);
            $stmt->execute();
            $result = $stmt->get_result();
        
            while ($row = $result->fetch_assoc()) {

                    $update_description = $row['description'];
                    $update_inflows = $row['inflows'];
                    $update_outflows = $row['outflows'];
                    
                    if($f == 0){
                        if($update_description == "Beginning balance"){
                            $first_balance = $row['balance'];
                            
                        }else{
                            $first_balance = 0;
                        }

                        $f++;
                    }else{
                        $last_date = $row['date'];
                    }

            }

            if($update_description == "Beginning balance"){

            }else{
                if($update_inflows == "0"){
                    $amount = $update_outflows;
                    $sign= 'negative';
                }else{
                    $amount = $update_inflows;
                    $sign= 'positive';
                }
                
                $type = 'Yearly';
    
    
                if($update_description == "Equipment" || $update_description == "Vehicle" || $update_description == "Furniture"){
                    $category = 'INVESTING';
    
                }else if($update_description == "Investment" || $update_description == "Other source of cash" || $update_description == "Other uses of cash" || $update_description == "Bank Financing Long Term" || $update_description == "Loan Payments - Long term"){
                    $category = 'FINANCING';
                }else{
                    $category = 'OPERATING';
                }
                
    
    
                $sqlforNoAccount = "SELECT cf_id, amount FROM tblcashflow WHERE date_month = '$Year' AND date_year = '$Year' AND business_name = '$BusinessName' AND description='$update_description'";
                $sqlrun = mysqli_query($conn, $sqlforNoAccount);
        
                if(mysqli_num_rows($sqlrun)>0){
                
                    $stmt = $conn->prepare($sqlforNoAccount);
                    $stmt->execute();
                    $result = $stmt->get_result();
        
                    while ($row = $result->fetch_assoc()) {
                        $id = $row['cf_id']; 
                        $prev_amount = $row['amount'];
                    }
    
                    $amount = $prev_amount + $amount;
    
                    $sqlforAccounts = "UPDATE tblcashflow SET amount='$amount' WHERE cf_id = ?";
                    
                    $stmt = mysqli_stmt_init($conn);
            
                    if(!mysqli_stmt_prepare($stmt, $sqlforAccounts)){
                        echo "SQL Error";
                    }else{
                        mysqli_stmt_bind_param($stmt,"s",$id);
                        mysqli_stmt_execute($stmt);
                    }
    
                }else{
    
    
                    $sqlforAccounts = "INSERT INTO tblcashflow(cf_id,business_name, date_month, date_year, type, category, description,first_balance, amount, sign, details) VALUES ('',?,?,?,?,?,?,'',?,?,'');";
                
                    $stmt = mysqli_stmt_init($conn);
            
                    if(!mysqli_stmt_prepare($stmt, $sqlforAccounts)){
                        echo "SQL Error";
                    }else{
                        mysqli_stmt_bind_param($stmt,"ssssssss",$BusinessName,$Year,$Year,$type,$category,$update_description,$amount,$sign);
                        mysqli_stmt_execute($stmt);
                    }
                }
            }
        }


        //$date1 = DateTime::createFromFormat('Y-m-d', $first_date);
        $date2 = DateTime::createFromFormat('Y-m-d', $last_date);

        

        $ISdetails = 'for the Year ended ' . $date2->format('F d, Y');

        $sqlforAccounts = "UPDATE tblcashflow SET details='$ISdetails', first_balance='$first_balance' WHERE `date_month` = ? AND `date_year` = ? AND `business_name` = ?";
                
        $stmt = mysqli_stmt_init($conn);

        if(!mysqli_stmt_prepare($stmt, $sqlforAccounts)){
            echo "SQL Error";
        }else{
            mysqli_stmt_bind_param($stmt,"sss",$Year,$Year,$BusinessName);
            mysqli_stmt_execute($stmt);
        }




        header("Location: ../SEA/cashflow.php");
        $_SESSION ['response'] = "Successfully Cash Flow Statement Generated";
        $_SESSION ['res_type']= "success";
        $_SESSION ['year_is'] = $Year;
        $_SESSION ['month_is']= $Year;
        $_SESSION ['ISdetails']= $ISdetails;

    }
    else if($WhatWillGenerate == "Print"){

       

        $fileName = $BusinessName .'-Cashbook Entry Records Year-of-'.$Year.'.pdf';


                
class PDF extends FPDF
{

    function __construct()
    {
        parent::__construct();
    }

    function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false)
    {
        parent::MultiCell($w, $h, $this->normalize($txt), $border, $align, $fill);
    }

    function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
    {
        parent::Cell($w, $h, $this->normalize($txt), $border, $ln, $align, $fill, $link);
    }

    function Write($h, $txt, $link='')
    {
        parent::Write($h, $this->normalize($txt), $link);
    }

    function Text($x, $y, $txt)
    {
        parent::Text($x, $y, $this->normalize($txt));
    }

    protected function normalize($word)
    {
        // Thanks to: http://stackoverflow.com/questions/3514076/special-characters-in-fpdf-with-php
        
        $word = str_replace("@","%40",$word);
        $word = str_replace("`","%60",$word);
        $word = str_replace("¢","%A2",$word);
        $word = str_replace("£","%A3",$word);
        $word = str_replace("¥","%A5",$word);
        $word = str_replace("|","%A6",$word);
        $word = str_replace("«","%AB",$word);
        $word = str_replace("¬","%AC",$word);
        $word = str_replace("¯","%AD",$word);
        $word = str_replace("º","%B0",$word);
        $word = str_replace("±","%B1",$word);
        $word = str_replace("ª","%B2",$word);
        $word = str_replace("µ","%B5",$word);
        $word = str_replace("»","%BB",$word);
        $word = str_replace("¼","%BC",$word);
        $word = str_replace("½","%BD",$word);
        $word = str_replace("¿","%BF",$word);
        $word = str_replace("À","%C0",$word);
        $word = str_replace("Á","%C1",$word);
        $word = str_replace("Â","%C2",$word);
        $word = str_replace("Ã","%C3",$word);
        $word = str_replace("Ä","%C4",$word);
        $word = str_replace("Å","%C5",$word);
        $word = str_replace("Æ","%C6",$word);
        $word = str_replace("Ç","%C7",$word);
        $word = str_replace("È","%C8",$word);
        $word = str_replace("É","%C9",$word);
        $word = str_replace("Ê","%CA",$word);
        $word = str_replace("Ë","%CB",$word);
        $word = str_replace("Ì","%CC",$word);
        $word = str_replace("Í","%CD",$word);
        $word = str_replace("Î","%CE",$word);
        $word = str_replace("Ï","%CF",$word);
        $word = str_replace("Ð","%D0",$word);
        $word = str_replace("Ñ","%D1",$word);
        $word = str_replace("Ò","%D2",$word);
        $word = str_replace("Ó","%D3",$word);
        $word = str_replace("Ô","%D4",$word);
        $word = str_replace("Õ","%D5",$word);
        $word = str_replace("Ö","%D6",$word);
        $word = str_replace("Ø","%D8",$word);
        $word = str_replace("Ù","%D9",$word);
        $word = str_replace("Ú","%DA",$word);
        $word = str_replace("Û","%DB",$word);
        $word = str_replace("Ü","%DC",$word);
        $word = str_replace("Ý","%DD",$word);
        $word = str_replace("Þ","%DE",$word);
        $word = str_replace("ß","%DF",$word);
        $word = str_replace("à","%E0",$word);
        $word = str_replace("á","%E1",$word);
        $word = str_replace("â","%E2",$word);
        $word = str_replace("ã","%E3",$word);
        $word = str_replace("ä","%E4",$word);
        $word = str_replace("å","%E5",$word);
        $word = str_replace("æ","%E6",$word);
        $word = str_replace("ç","%E7",$word);
        $word = str_replace("è","%E8",$word);
        $word = str_replace("é","%E9",$word);
        $word = str_replace("ê","%EA",$word);
        $word = str_replace("ë","%EB",$word);
        $word = str_replace("ì","%EC",$word);
        $word = str_replace("í","%ED",$word);
        $word = str_replace("î","%EE",$word);
        $word = str_replace("ï","%EF",$word);
        $word = str_replace("ð","%F0",$word);
        $word = str_replace("ñ","%F1",$word);
        $word = str_replace("ò","%F2",$word);
        $word = str_replace("ó","%F3",$word);
        $word = str_replace("ô","%F4",$word);
        $word = str_replace("õ","%F5",$word);
        $word = str_replace("ö","%F6",$word);
        $word = str_replace("÷","%F7",$word);
        $word = str_replace("ø","%F8",$word);
        $word = str_replace("ù","%F9",$word);
        $word = str_replace("ú","%FA",$word);
        $word = str_replace("û","%FB",$word);
        $word = str_replace("ü","%FC",$word);
        $word = str_replace("ý","%FD",$word);
        $word = str_replace("þ","%FE",$word);
        $word = str_replace("ÿ","%FF",$word);

        return urldecode($word);
    }


// Page header
function Header()
{
    // Logo
   // $this->Image('../img/logo.png',100,6,170);
    // Arial bold 15
    $this->SetFont('Arial','B',14);
    // Move to the right
    
    $this->Ln(10);
    // Title
    $this->Cell(100);
    $this->Cell(10,10,'CASHBOOK ENTRY RECORDS',0,0,'C');
    $this->Ln(7);
    // Line break
    $this->Ln(10);
}

// Page footer
function Footer()
{
    // Position at 1.5 cm from bottom
    $this->SetY(-15);
    // Arial italic 8
    $this->SetFont('Arial','I',8);
    // Page number
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
}

// Page footer
function headerTable()
{
  
    // Arial italic 8
    $this->SetFont('Arial','B',10);
    // Page number
    $this->Cell(5);
    $this->Cell(22,10,'Date',1,0,'C');
    $this->Cell(78,10,'Description',1,0,'C');
    $this->Cell(30,10,'Inflows',1,0,'C');
    $this->Cell(30,10,'Outflows',1,0,'C');
    $this->Cell(30,10,'Balance',1,0,'C');
    $this->Ln();
}
function queryTable($Month, $Year, $BusinessName,$conn){
        $this->SetFont('Arial','B',10);
       
      
        $sqlforNoAccount = "SELECT date, description, inflows, outflows, balance FROM tblcashbookentry WHERE ((MONTH(date) = '$Month' AND YEAR(date)= '$Year') AND (business_name = '$BusinessName')) Order By date, order_by ASC";
        $stmt = $conn->prepare($sqlforNoAccount);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
                $this->Cell(5);
                $this->Cell(22,10,$row['date'],1,0,'L');
                $this->Cell(78,10,$row['description'],1,0,'L');
                $this->Cell(30,10,number_format($row['inflows']),1,0,'C');
                $this->Cell(30,10,number_format($row['outflows']),1,0,'C');
                $this->Cell(30,10,number_format($row['balance']),1,0,'C');
                $this->Ln();
        }
}

function Signatory($BusinessOwner, $BusinessName)
{
    $this->Ln(15);
    $this->SetFont('Arial','B',12);
    $this->Cell(15);
    $this->Cell(0,10,$BusinessOwner,0,0,'R');
    $this->Ln(6);
    $this->Cell(15);
    $this->SetFont('Arial','B',12);
    $this->Cell(0,10,$BusinessName .', Owner',0,0,'R');
    // Line break
    $this->Ln(20);
}


function Month($MonthDetails, $Year)
{
    $this->Ln(8);
    $this->SetFont('Arial','B',12);
    $this->Cell(15);
    $this->Cell(0,10,'Month of '.$MonthDetails . ' ' . $Year,0,0,'C');
    // Line break
    $this->Ln(10);
}

}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage('P','Legal',0);

for ($i=1; $i <=12 ; $i++) { 
    # code...
    $pdf->Month($MonthDetails[$i], $Year);
    $pdf->headerTable();
    $pdf->queryTable($Months[$i], $Year, $BusinessName,$conn);
}

$pdf->Signatory($BusinessOwner, $BusinessName);
$pdf->Output($fileName, 'D');
      
        



     


    }
    else if($WhatWillGenerate == "Download"){


        
        $spreadsheet = new Spreadsheet();
        $Excel_writer = new Xlsx($spreadsheet);


        $spreadsheet->getProperties()->setCreator($BusinessOwner)
        ->setLastModifiedBy($BusinessOwner)
        ->setTitle($BusinessName.'-Cashbook Entry Report Year-of-'.$Year )
        ->setSubject('Cashbook Entry Report')
        ->setDescription('Cashbook Entry Report')
        ->setKeywords('Cashbook Entry Report')
        ->setCategory('Cashbook Entry Report');

        for ($r=0; $r <= 11 ; $r++) { 
            # code...
        $m = $r + 1;
        $spreadsheet->setActiveSheetIndex($r);
        $activeSheet = $spreadsheet->getActiveSheet();
          
        $activeSheet->setCellValue('A1', 'Date');
        $activeSheet->setCellValue('B1', 'Description');
        $activeSheet->setCellValue('C1', 'Inflows');
        $activeSheet->setCellValue('D1', 'Ouflows');
        $activeSheet->setCellValue('E1', 'Balance');

        $activeSheet->setTitle($MonthDetails[$m]);

        foreach ($activeSheet->getColumnIterator() as $column) {
            $activeSheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
        }

        $styleArray = [
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'center' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];

        $activeSheet->getStyle('A1:E1')->applyFromArray($styleArray);

          
        $sqlforNoAccount = "SELECT date, description, inflows, outflows, balance FROM tblcashbookentry WHERE ((MONTH(date) = '$Months[$m]' AND YEAR(date)= '$Year') AND (business_name = '$BusinessName')) Order By date, order_by ASC";
        $stmt = $conn->prepare($sqlforNoAccount);
        $stmt->execute();
        $result = $stmt->get_result();

        $i = 2;
        while ($row = $result->fetch_assoc()) {
                $activeSheet->setCellValue('A'.$i , $row['date']);
                $activeSheet->setCellValue('B'.$i , $row['description']);
                $activeSheet->setCellValue('C'.$i , $row['inflows']);
                $activeSheet->setCellValue('D'.$i , $row['outflows']);
                $activeSheet->setCellValue('E'.$i , $row['balance']);
                $i++;
            }
        


            $spreadsheet->createSheet();
        }
          
        $spreadsheet->setActiveSheetIndex(0);
          
        $filename = $BusinessName .'-Cashbook Entry Records Year-of-'.$Year.'.xlsx';

          
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename='. $filename);
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');


      

    }else{
        header("Location: ../SEA/cashbook.php");
        $_SESSION ['year'] = date("Y");
        $_SESSION ['month']= date("m");
    }




}


if(isset($_POST['print_IS'])){


    $BusinessName = $_SESSION ['business_name'];
    $BusinessOwner =  $_SESSION ['business_owner'];
    $Yearly =   $_SESSION ['year_is'];
    $Monthly = $_SESSION ['month_is'];
    $Detailsly = $_SESSION ['ISdetails'];

    $MonthDetails = array('',
    'January',
    'February',
    'March',
    'April',
    'May',
    'June',
    'July ',
    'August',
    'September',
    'October',
    'November',
    'December',);


    if($Monthly == "Q1" || $Monthly == "Q2" || $Monthly == "Q3" || $Monthly == "Q4"){

        $fileName = $BusinessName .'-Income Statement '.$Monthly.'-of-'.$Yearly.'.pdf';

    }else if(strlen($Monthly) == 4){
        $fileName = $BusinessName .'-Income Statement Year-of-'.$Yearly.'.pdf';
    }else{
        $fileName = $BusinessName .'-Income Statement '. $MonthDetails[$Monthly] .$Yearly.'.pdf';
    }


                
    class PDF extends FPDF
    {

        function __construct()
		{
			parent::__construct();
		}

		function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false)
		{
			parent::MultiCell($w, $h, $this->normalize($txt), $border, $align, $fill);
		}

		function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
		{
			parent::Cell($w, $h, $this->normalize($txt), $border, $ln, $align, $fill, $link);
		}

		function Write($h, $txt, $link='')
		{
			parent::Write($h, $this->normalize($txt), $link);
		}

		function Text($x, $y, $txt)
		{
			parent::Text($x, $y, $this->normalize($txt));
		}

		protected function normalize($word)
		{
			// Thanks to: http://stackoverflow.com/questions/3514076/special-characters-in-fpdf-with-php
			
			$word = str_replace("@","%40",$word);
			$word = str_replace("`","%60",$word);
			$word = str_replace("¢","%A2",$word);
			$word = str_replace("£","%A3",$word);
			$word = str_replace("¥","%A5",$word);
			$word = str_replace("|","%A6",$word);
			$word = str_replace("«","%AB",$word);
			$word = str_replace("¬","%AC",$word);
			$word = str_replace("¯","%AD",$word);
			$word = str_replace("º","%B0",$word);
			$word = str_replace("±","%B1",$word);
			$word = str_replace("ª","%B2",$word);
			$word = str_replace("µ","%B5",$word);
			$word = str_replace("»","%BB",$word);
			$word = str_replace("¼","%BC",$word);
			$word = str_replace("½","%BD",$word);
			$word = str_replace("¿","%BF",$word);
			$word = str_replace("À","%C0",$word);
			$word = str_replace("Á","%C1",$word);
			$word = str_replace("Â","%C2",$word);
			$word = str_replace("Ã","%C3",$word);
			$word = str_replace("Ä","%C4",$word);
			$word = str_replace("Å","%C5",$word);
			$word = str_replace("Æ","%C6",$word);
			$word = str_replace("Ç","%C7",$word);
			$word = str_replace("È","%C8",$word);
			$word = str_replace("É","%C9",$word);
			$word = str_replace("Ê","%CA",$word);
			$word = str_replace("Ë","%CB",$word);
			$word = str_replace("Ì","%CC",$word);
			$word = str_replace("Í","%CD",$word);
			$word = str_replace("Î","%CE",$word);
			$word = str_replace("Ï","%CF",$word);
			$word = str_replace("Ð","%D0",$word);
			$word = str_replace("Ñ","%D1",$word);
			$word = str_replace("Ò","%D2",$word);
			$word = str_replace("Ó","%D3",$word);
			$word = str_replace("Ô","%D4",$word);
			$word = str_replace("Õ","%D5",$word);
			$word = str_replace("Ö","%D6",$word);
			$word = str_replace("Ø","%D8",$word);
			$word = str_replace("Ù","%D9",$word);
			$word = str_replace("Ú","%DA",$word);
			$word = str_replace("Û","%DB",$word);
			$word = str_replace("Ü","%DC",$word);
			$word = str_replace("Ý","%DD",$word);
			$word = str_replace("Þ","%DE",$word);
			$word = str_replace("ß","%DF",$word);
			$word = str_replace("à","%E0",$word);
			$word = str_replace("á","%E1",$word);
			$word = str_replace("â","%E2",$word);
			$word = str_replace("ã","%E3",$word);
			$word = str_replace("ä","%E4",$word);
			$word = str_replace("å","%E5",$word);
			$word = str_replace("æ","%E6",$word);
			$word = str_replace("ç","%E7",$word);
			$word = str_replace("è","%E8",$word);
			$word = str_replace("é","%E9",$word);
			$word = str_replace("ê","%EA",$word);
			$word = str_replace("ë","%EB",$word);
			$word = str_replace("ì","%EC",$word);
			$word = str_replace("í","%ED",$word);
			$word = str_replace("î","%EE",$word);
			$word = str_replace("ï","%EF",$word);
			$word = str_replace("ð","%F0",$word);
			$word = str_replace("ñ","%F1",$word);
			$word = str_replace("ò","%F2",$word);
			$word = str_replace("ó","%F3",$word);
			$word = str_replace("ô","%F4",$word);
			$word = str_replace("õ","%F5",$word);
			$word = str_replace("ö","%F6",$word);
			$word = str_replace("÷","%F7",$word);
			$word = str_replace("ø","%F8",$word);
			$word = str_replace("ù","%F9",$word);
			$word = str_replace("ú","%FA",$word);
			$word = str_replace("û","%FB",$word);
			$word = str_replace("ü","%FC",$word);
			$word = str_replace("ý","%FD",$word);
			$word = str_replace("þ","%FE",$word);
			$word = str_replace("ÿ","%FF",$word);

			return urldecode($word);
		}
        
    // Page header
    function Header()
    {

        if ( $this->PageNo() === 1 ) {
                    // Logo
       // $this->Image('../img/logo.png',100,6,170);
        // Arial bold 15
        $this->SetFont('Arial','B',14);
        // Move to the right
        
        $this->Ln(10);
        // Title
        $this->Cell(100);
        $this->Cell(10,10,'INCOME STATEMENT',0,0,'C');
        }

    }
    
    // Page footer
    function Footer()
    {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial','I',8);
        // Page number
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }
    
    // Page footer
    function headerTable()
    {
        
        
      
        // Arial italic 8
        $this->SetFont('Arial','B',10);
        // Page number
        $this->Cell(5);
        $this->Cell(95,10,'DESCRIPTION',1,0,'C');
        $this->Cell(95,10,'AMOUNT',1,0,'C');
        $this->Ln();
    }
    function queryTableIncome($month, $year, $BName,$conn){
            $this->SetFont('Arial','B',10);
           
          
            $sqlforNoAccount = "SELECT `description`, `amount` FROM `tblincomestatement` WHERE `date_month` = '$month' AND `date_year` = '$year' AND `business_name` = '$BName' AND `category` = 'INCOME'";
            $stmt = $conn->prepare($sqlforNoAccount);
            $stmt->execute();
            $result = $stmt->get_result();
    
            while ($row = $result->fetch_assoc()) {
                    $this->Cell(5);
                    $this->Cell(95,10,$row['description'],1,0,'L');
                    $this->Cell(95,10,number_format($row['amount']),1,0,'R');
                    $this->Ln();
            }

            $sqlforNoAccount = "SELECT  SUM(amount) AS total_amount FROM `tblincomestatement` WHERE `date_month` = '$month' AND `date_year` = '$year' AND `business_name` = '$BName' AND `category` = 'INCOME'";
            $stmt = $conn->prepare($sqlforNoAccount);
            $stmt->execute();
            $result = $stmt->get_result();
    
            while ($row = $result->fetch_assoc()) {
                    $this->Cell(5);
                    $this->Cell(95,10,'TOTAL INCOME',1,0,'L');
                    $this->Cell(95,10,number_format($row['total_amount']),1,0,'R');
                    $this->Ln();
            }


    }

    function queryTableExpenses($month, $year, $BName,$conn){
        $this->SetFont('Arial','B',10);
       
      
        $sqlforNoAccount = "SELECT `description`, `amount` FROM `tblincomestatement` WHERE `date_month` = '$month' AND `date_year` = '$year' AND `business_name` = '$BName' AND `category` = 'EXPENSES'";
        $stmt = $conn->prepare($sqlforNoAccount);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
                $this->Cell(5);
                $this->Cell(95,10,$row['description'],1,0,'L');
                $this->Cell(95,10,number_format($row['amount']),1,0,'R');
                $this->Ln();
        }

        $sqlforNoAccount = "SELECT  SUM(amount) AS total_amount FROM `tblincomestatement` WHERE `date_month` = '$month' AND `date_year` = '$year' AND `business_name` = '$BName' AND `category` = 'EXPENSES'";
        $stmt = $conn->prepare($sqlforNoAccount);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
                $totalexpenses = $row['total_amount'];
              
        }

        $sqlforNoAccount = "SELECT  SUM(amount) AS total_amount FROM `tblincomestatement` WHERE `date_month` = '$month' AND `date_year` = '$year' AND `business_name` = '$BName' AND `category` = 'INCOME'";
        $stmt = $conn->prepare($sqlforNoAccount);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
               $totalincome = $row['total_amount'];
        }

        $sum = $totalincome - $totalexpenses;

                    if($sum < 0){
                      $text = "NET LOSS";
                    }else{
                        $text = "NET PROFIT";
                    }
        

                    $this->Cell(5);
                    $this->Cell(95,10,"TOTAL EXPENSES",1,0,'L');
                    $this->Cell(95,10,number_format($totalexpenses),1,0,'R');
                    $this->Ln();

                    $this->Cell(5);
                    $this->Cell(95,10,$text,1,0,'L');
                    $this->Cell(95,10,number_format($sum),1,0,'R');
                    $this->Ln();


}
    
    function Signatory($BusinessOwner, $BusinessName)
    {
        $this->Ln(15);
        $this->SetFont('Arial','B',12);
        $this->Cell(15);
        $this->Cell(0,10,$BusinessOwner,0,0,'R');
        $this->Ln(6);
        $this->Cell(15);
        $this->SetFont('Arial','B',12);
        $this->Cell(0,10,$BusinessName .', Owner',0,0,'R');
        // Line break
        $this->Ln(20);
    }

    function subhead($ISdetails)
{
    $this->Ln(8);
    $this->SetFont('Arial','B',12);
    $this->Cell(15);
    $this->Cell(0,10, $ISdetails ,0,0,'C');
    // Line break
    $this->Ln(12);
}

function subhead1($ISdetails)
{
    $this->Ln(8);
    $this->SetFont('Arial','B',12);
    $this->Cell(10);
    $this->Cell(0,10, $ISdetails ,0,0,'C');
    // Line break
    $this->Ln(12);
}
    
    
    
    }
    
    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage('P','Legal',0);

    $pdf->subhead($Detailsly);

    $pdf->subhead1("INCOME");
    $pdf->headerTable();
    $pdf->queryTableIncome($Monthly, $Yearly, $BusinessName,$conn);

    $pdf->subhead1("EXPENSES");
    $pdf->headerTable();
    $pdf->queryTableExpenses($Monthly, $Yearly, $BusinessName,$conn);
    
    
    $pdf->Signatory($BusinessOwner, $BusinessName);
    $pdf->Output($fileName, 'D');
          

}





if(isset($_POST['Search_Monthly_IS'])){

    $month = $_POST['monthIShow'];
    $year = $_POST['yearIShow'];
    $BusinessName = $_SESSION ['business_name'];

    $sqlforNoAccount = "SELECT DISTINCT details FROM `tblincomestatement` WHERE `date_month` = '$month' AND `date_year` = '$year' AND `business_name` = '$BusinessName'";
            $stmt = $conn->prepare($sqlforNoAccount);
            $stmt->execute();
            $result = $stmt->get_result();
        
            while ($row = $result->fetch_assoc()) {

                $ISdetails = $row['details'];

            }


   header("Location: ../SEA/income.php");
    $_SESSION ['year_is'] = $year;
    $_SESSION ['month_is']= $month;
    $_SESSION ['ISdetails']= $ISdetails;

}



if(isset($_POST['Search_Quarterly_IS'])){

    $month = $_POST['quarterIShow'];
    $year = $_POST['yearIShowQ'];
    $BusinessName = $_SESSION ['business_name'];

    $sqlforNoAccount = "SELECT DISTINCT details FROM `tblincomestatement` WHERE `date_month` = '$month' AND `date_year` = '$year' AND `business_name` = '$BusinessName'";
            $stmt = $conn->prepare($sqlforNoAccount);
            $stmt->execute();
            $result = $stmt->get_result();
        
            while ($row = $result->fetch_assoc()) {

                $ISdetails = $row['details'];

            }


   header("Location: ../SEA/income.php");
    $_SESSION ['year_is'] = $year;
    $_SESSION ['month_is']= $month;
    $_SESSION ['ISdetails']= $ISdetails;

}


if(isset($_POST['Search_Yearly_IS'])){

    $month = $_POST['yearIShowY'];
    $year = $_POST['yearIShowY'];
    $BusinessName = $_SESSION ['business_name'];

    $sqlforNoAccount = "SELECT DISTINCT details FROM `tblincomestatement` WHERE `date_month` = '$month' AND `date_year` = '$year' AND `business_name` = '$BusinessName'";
            $stmt = $conn->prepare($sqlforNoAccount);
            $stmt->execute();
            $result = $stmt->get_result();
        
            while ($row = $result->fetch_assoc()) {

                $ISdetails = $row['details'];

            }


   header("Location: ../SEA/income.php");
    $_SESSION ['year_is'] = $year;
    $_SESSION ['month_is']= $month;
    $_SESSION ['ISdetails']= $ISdetails;

}


if(isset($_POST['Search_Monthly_CF'])){

    $month = $_POST['monthCShow'];
    $year = $_POST['yearCShow'];
    $BusinessName = $_SESSION ['business_name'];

    $sqlforNoAccount = "SELECT DISTINCT details FROM `tblincomestatement` WHERE `date_month` = '$month' AND `date_year` = '$year' AND `business_name` = '$BusinessName'";
            $stmt = $conn->prepare($sqlforNoAccount);
            $stmt->execute();
            $result = $stmt->get_result();
        
            while ($row = $result->fetch_assoc()) {

                $ISdetails = $row['details'];

            }


   header("Location: ../SEA/cashflow.php");
    $_SESSION ['year_is'] = $year;
    $_SESSION ['month_is']= $month;
    $_SESSION ['ISdetails']= $ISdetails;

}


if(isset($_POST['Search_Quarterly_CF'])){

    $month = $_POST['quarterCShow'];
    $year = $_POST['yearCShowQ'];
    $BusinessName = $_SESSION ['business_name'];

    $sqlforNoAccount = "SELECT DISTINCT details FROM `tblincomestatement` WHERE `date_month` = '$month' AND `date_year` = '$year' AND `business_name` = '$BusinessName'";
            $stmt = $conn->prepare($sqlforNoAccount);
            $stmt->execute();
            $result = $stmt->get_result();
        
            while ($row = $result->fetch_assoc()) {

                $ISdetails = $row['details'];

            }


   header("Location: ../SEA/cashflow.php");
    $_SESSION ['year_is'] = $year;
    $_SESSION ['month_is']= $month;
    $_SESSION ['ISdetails']= $ISdetails;

}


if(isset($_POST['Search_Yearly_CF'])){

    $month = $_POST['yearCShowY'];
    $year = $_POST['yearCShowY'];
    $BusinessName = $_SESSION ['business_name'];

    $sqlforNoAccount = "SELECT DISTINCT details FROM `tblincomestatement` WHERE `date_month` = '$month' AND `date_year` = '$year' AND `business_name` = '$BusinessName'";
            $stmt = $conn->prepare($sqlforNoAccount);
            $stmt->execute();
            $result = $stmt->get_result();
        
            while ($row = $result->fetch_assoc()) {

                $ISdetails = $row['details'];

            }


   header("Location: ../SEA/cashflow.php");
    $_SESSION ['year_is'] = $year;
    $_SESSION ['month_is']= $month;
    $_SESSION ['ISdetails']= $ISdetails;

}

if(isset($_POST['print_CF'])){


    $BusinessName = $_SESSION ['business_name'];
    $BusinessOwner =  $_SESSION ['business_owner'];
    $Yearly =   $_SESSION ['year_is'];
    $Monthly = $_SESSION ['month_is'];
    $Detailsly = $_SESSION ['ISdetails'];

    $MonthDetails = array('',
    'January',
    'February',
    'March',
    'April',
    'May',
    'June',
    'July ',
    'August',
    'September',
    'October',
    'November',
    'December',);


    if($Monthly == "Q1" || $Monthly == "Q2" || $Monthly == "Q3" || $Monthly == "Q4"){

        $fileName = $BusinessName .'-Cash Flow '.$Monthly.'-of-'.$Yearly.'.pdf';

    }else if(strlen($Monthly) == 4){
        $fileName = $BusinessName .'-Cash Flow Year-of-'.$Yearly.'.pdf';
    }else{
        $fileName = $BusinessName .'-Cash Flow '. $MonthDetails[$Monthly] .$Yearly.'.pdf';
    }


                
    class PDF extends FPDF
    {

        function __construct()
		{
			parent::__construct();
		}

		function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false)
		{
			parent::MultiCell($w, $h, $this->normalize($txt), $border, $align, $fill);
		}

		function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
		{
			parent::Cell($w, $h, $this->normalize($txt), $border, $ln, $align, $fill, $link);
		}

		function Write($h, $txt, $link='')
		{
			parent::Write($h, $this->normalize($txt), $link);
		}

		function Text($x, $y, $txt)
		{
			parent::Text($x, $y, $this->normalize($txt));
		}

		protected function normalize($word)
		{
			// Thanks to: http://stackoverflow.com/questions/3514076/special-characters-in-fpdf-with-php
			
			$word = str_replace("@","%40",$word);
			$word = str_replace("`","%60",$word);
			$word = str_replace("¢","%A2",$word);
			$word = str_replace("£","%A3",$word);
			$word = str_replace("¥","%A5",$word);
			$word = str_replace("|","%A6",$word);
			$word = str_replace("«","%AB",$word);
			$word = str_replace("¬","%AC",$word);
			$word = str_replace("¯","%AD",$word);
			$word = str_replace("º","%B0",$word);
			$word = str_replace("±","%B1",$word);
			$word = str_replace("ª","%B2",$word);
			$word = str_replace("µ","%B5",$word);
			$word = str_replace("»","%BB",$word);
			$word = str_replace("¼","%BC",$word);
			$word = str_replace("½","%BD",$word);
			$word = str_replace("¿","%BF",$word);
			$word = str_replace("À","%C0",$word);
			$word = str_replace("Á","%C1",$word);
			$word = str_replace("Â","%C2",$word);
			$word = str_replace("Ã","%C3",$word);
			$word = str_replace("Ä","%C4",$word);
			$word = str_replace("Å","%C5",$word);
			$word = str_replace("Æ","%C6",$word);
			$word = str_replace("Ç","%C7",$word);
			$word = str_replace("È","%C8",$word);
			$word = str_replace("É","%C9",$word);
			$word = str_replace("Ê","%CA",$word);
			$word = str_replace("Ë","%CB",$word);
			$word = str_replace("Ì","%CC",$word);
			$word = str_replace("Í","%CD",$word);
			$word = str_replace("Î","%CE",$word);
			$word = str_replace("Ï","%CF",$word);
			$word = str_replace("Ð","%D0",$word);
			$word = str_replace("Ñ","%D1",$word);
			$word = str_replace("Ò","%D2",$word);
			$word = str_replace("Ó","%D3",$word);
			$word = str_replace("Ô","%D4",$word);
			$word = str_replace("Õ","%D5",$word);
			$word = str_replace("Ö","%D6",$word);
			$word = str_replace("Ø","%D8",$word);
			$word = str_replace("Ù","%D9",$word);
			$word = str_replace("Ú","%DA",$word);
			$word = str_replace("Û","%DB",$word);
			$word = str_replace("Ü","%DC",$word);
			$word = str_replace("Ý","%DD",$word);
			$word = str_replace("Þ","%DE",$word);
			$word = str_replace("ß","%DF",$word);
			$word = str_replace("à","%E0",$word);
			$word = str_replace("á","%E1",$word);
			$word = str_replace("â","%E2",$word);
			$word = str_replace("ã","%E3",$word);
			$word = str_replace("ä","%E4",$word);
			$word = str_replace("å","%E5",$word);
			$word = str_replace("æ","%E6",$word);
			$word = str_replace("ç","%E7",$word);
			$word = str_replace("è","%E8",$word);
			$word = str_replace("é","%E9",$word);
			$word = str_replace("ê","%EA",$word);
			$word = str_replace("ë","%EB",$word);
			$word = str_replace("ì","%EC",$word);
			$word = str_replace("í","%ED",$word);
			$word = str_replace("î","%EE",$word);
			$word = str_replace("ï","%EF",$word);
			$word = str_replace("ð","%F0",$word);
			$word = str_replace("ñ","%F1",$word);
			$word = str_replace("ò","%F2",$word);
			$word = str_replace("ó","%F3",$word);
			$word = str_replace("ô","%F4",$word);
			$word = str_replace("õ","%F5",$word);
			$word = str_replace("ö","%F6",$word);
			$word = str_replace("÷","%F7",$word);
			$word = str_replace("ø","%F8",$word);
			$word = str_replace("ù","%F9",$word);
			$word = str_replace("ú","%FA",$word);
			$word = str_replace("û","%FB",$word);
			$word = str_replace("ü","%FC",$word);
			$word = str_replace("ý","%FD",$word);
			$word = str_replace("þ","%FE",$word);
			$word = str_replace("ÿ","%FF",$word);

			return urldecode($word);
		}





    // Page header
    function Header()
    {

        if ( $this->PageNo() === 1 ) {
                    // Logo
       // $this->Image('../img/logo.png',100,6,170);
        // Arial bold 15
        $this->SetFont('Arial','B',14);
        // Move to the right
        
        $this->Ln(10);
        // Title
        $this->Cell(100);
        $this->Cell(10,10,'STATEMENT OF CASHFLOW',0,0,'C');
        }

    }
    
    // Page footer
    function Footer()
    {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial','I',8);
        // Page number
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }
    
    // Page footer
    function headerTable()
    {
        
        
      
        // Arial italic 8
        $this->SetFont('Arial','B',10);
        // Page number
        $this->Cell(5);
        $this->Cell(95,10,'DESCRIPTION',1,0,'C');
        $this->Cell(95,10,'AMOUNT',1,0,'C');
        $this->Ln();
    }


   
    function queryTableOP($month, $year, $Bname,$conn){
            $this->SetFont('Arial','B',10);
           
          
            $sqlforNoAccount = "SELECT `description`, `amount`, `sign` FROM `tblcashflow` WHERE `date_month` = '$month' AND `date_year` = '$year' AND `business_name` = '$Bname' AND `category` = 'OPERATING'";
            $stmt = $conn->prepare($sqlforNoAccount);
            $stmt->execute();
            $result = $stmt->get_result();
    
            while ($row = $result->fetch_assoc()) {
                    $this->Cell(5);
                    $this->Cell(95,10,$row['description'],1,0,'L');
                    if($row['sign'] == "negative"){
                        $amount = '('.number_format($row['amount']).')'; 
                      }else{
                        
                        $amount = number_format($row['amount']); 
                      }
                    $this->Cell(95,10,$amount,1,0,'R');
                    $this->Ln();
            }

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

                    if($totalpositive > $totalnegative){
                        $totalOP = $totalpositive - $totalnegative;
                        $totalOPO = $totalpositive - $totalnegative;
                        $totalOP = number_format($totalOP);
                      }else{
                        $totalOP = $totalnegative - $totalpositive;
                        $totalOPO = $totalpositive - $totalnegative;
                        $totalOP = '('.number_format($totalOP).')';
                      }


                    $this->Cell(5);
                    $this->Cell(95,10,'Net cash provided (used) from operating activities',1,0,'L');
                    $this->Cell(95,10,$totalOP,1,0,'R');
                    $this->Ln();

    }

    function queryTableIN($month, $year, $Bname,$conn){
        $this->SetFont('Arial','B',10);
       
      
        $sqlforNoAccount = "SELECT `description`, `amount`, `sign` FROM `tblcashflow` WHERE `date_month` = '$month' AND `date_year` = '$year' AND `business_name` = '$Bname' AND `category` = 'INVESTING'";
        $stmt = $conn->prepare($sqlforNoAccount);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
                $this->Cell(5);
                $this->Cell(95,10,$row['description'],1,0,'L');
                if($row['sign'] == "negative"){
                    $amount = '('.number_format($row['amount']).')'; 
                  }else{
                    
                    $amount = number_format($row['amount']); 
                  }
                $this->Cell(95,10,$amount,1,0,'R');
                $this->Ln();
        }

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

                if($totalpositive > $totalnegative){
                    $totalOP = $totalpositive - $totalnegative;
                    $totalOPO = $totalpositive - $totalnegative;
                    $totalOP = number_format($totalOP);
                  }else{
                    $totalOP = $totalnegative - $totalpositive;
                    $totalOPO = $totalpositive - $totalnegative;
                    $totalOP = '('.number_format($totalOP).')';
                  }


                $this->Cell(5);
                $this->Cell(95,10,'Net cash provided (used) from investing activities',1,0,'L');
                $this->Cell(95,10,$totalOP,1,0,'R');
                $this->Ln();

}

function queryTableFN($month, $year, $Bname,$conn){
    $this->SetFont('Arial','B',10);
   
  
    $sqlforNoAccount = "SELECT `description`, `amount`, `sign` FROM `tblcashflow` WHERE `date_month` = '$month' AND `date_year` = '$year' AND `business_name` = '$Bname' AND `category` = 'FINANCING'";
    $stmt = $conn->prepare($sqlforNoAccount);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
            $this->Cell(5);
            $this->Cell(95,10,$row['description'],1,0,'L');
            if($row['sign'] == "negative"){
                $amount = '('.number_format($row['amount']).')'; 
              }else{
                
                $amount = number_format($row['amount']); 
              }
            $this->Cell(95,10,$amount,1,0,'R');
            $this->Ln();
    }

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

            if($totalpositive > $totalnegative){
                $totalOP = $totalpositive - $totalnegative;
                $totalOPO = $totalpositive - $totalnegative;
                $totalOP = number_format($totalOP);
              }else{
                $totalOP = $totalnegative - $totalpositive;
                $totalOPO = $totalpositive - $totalnegative;
                $totalOP = '('.number_format($totalOP).')';
              }


            $this->Cell(5);
            $this->Cell(95,10,'Net cash provided (used) from financing activities',1,0,'L');
            $this->Cell(95,10,$totalOP,1,0,'R');
            $this->Ln();

}

function queryTableNC($month, $year, $Bname,$conn){
    $this->SetFont('Arial','B',10);
   
    
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

    
    $totalOPO = $totalpositive - $totalnegative;

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

    
    $totalINO = $totalpositive - $totalnegative;


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

    
    $totalFNO = $totalpositive - $totalnegative;

    $netC = $totalFNO + $totalINO + $totalOPO;

  
    
    $query = "SELECT  first_balance FROM `tblcashflow` WHERE `date_month` = '$month' AND `date_year` = '$year' AND `business_name` = '$Bname'";    
    $stmt = $conn->prepare($query);
    $stmt-> execute();
    $result = $stmt->get_result();  

    while ($row = $result->fetch_assoc()) {
        $first_balance = $row['first_balance'];
    }   


    $ending_balance = $first_balance + $netC;

    if($netC < 0){
        $netC = '('.number_format(substr($netC,1)).')';
      }else{
        $netC =  number_format($netC);
      }


      if($ending_balance < 0){
        $ending_balance = '('.number_format(substr($ending_balance,1)).')';
      }else{
        $ending_balance =  number_format($ending_balance);
      }

    

            $this->Cell(5);
            $this->Cell(95,10,'NET CHANGE IN CASH',1,0,'L');
            $this->Cell(95,10,$netC,1,0,'R');
            $this->Ln();

            $this->Cell(5);
            $this->Cell(95,10,'CASH BEGINNING',1,0,'L');
            $this->Cell(95,10,number_format($first_balance),1,0,'R');
            $this->Ln();

            $this->Cell(5);
            $this->Cell(95,10,'CASH ENDING',1,0,'L');
            $this->Cell(95,10,$ending_balance,1,0,'R');
            $this->Ln();



}

    
    
    function Signatory($BusinessOwner, $BusinessName)
    {
        $this->Ln(15);
        $this->SetFont('Arial','B',12);
        $this->Cell(15);
        $this->Cell(0,10,$BusinessOwner,0,0,'R');
        $this->Ln(6);
        $this->Cell(15);
        $this->SetFont('Arial','B',12);
        $this->Cell(0,10,$BusinessName .', Owner',0,0,'R');
        // Line break
        $this->Ln(20);
    }

    function subhead($ISdetails)
{
    $this->Ln(8);
    $this->SetFont('Arial','B',12);
    $this->Cell(15);
    $this->Cell(0,10, $ISdetails ,0,0,'C');
    // Line break
    $this->Ln(12);
}

function subhead1($ISdetails)
{
    $this->Ln(8);
    $this->SetFont('Arial','B',12);
    $this->Cell(10);
    $this->Cell(0,10, $ISdetails ,0,0,'C');
    // Line break
    $this->Ln(12);
}
    
    
    
    }
    
    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage('P','Legal',0);

    $pdf->subhead($Detailsly);

    $pdf->subhead1("OPERATING");
    $pdf->headerTable();
    $pdf->queryTableOP($Monthly, $Yearly, $BusinessName,$conn);

    $pdf->subhead1("INVESTING");
    $pdf->headerTable();
    $pdf->queryTableIN($Monthly, $Yearly, $BusinessName,$conn);

    $pdf->subhead1("FINANCING");
    $pdf->headerTable();
    $pdf->queryTableFN($Monthly, $Yearly, $BusinessName,$conn);

    $pdf->subhead1("NET CHANGE IN CASH");
    $pdf->headerTable();
    $pdf->queryTableNC($Monthly, $Yearly, $BusinessName,$conn);
    
    
    $pdf->Signatory($BusinessOwner, $BusinessName);
    $pdf->Output($fileName, 'D');
          

}