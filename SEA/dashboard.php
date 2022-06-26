
<?php 

include_once '../includes/dbprocess.php';

if(isset($_SESSION['isLoggedin'])){
  
}else{
  header("Location: ../index.php");
}


include 'includes/header.php'; 

include 'includes/menu.php'; 


?>

<body onload="setDataOnSelection();">
     <div class="title-top">

            <div class="title-wrapper">
                <h2><?php echo $_SESSION['business_name']?></h2>
            </div>
            <div class="title-wrapper">
                <h2>Annual Report</h2>
            </div>
    </div>
          <form action="../includes/dbprocess.php" Method="POST">
                    <div id="Quarterly" class="filter-wrapper">
                        <span class="custom-dropdown big">
                        <?php
                    $query = "SELECT DISTINCT YEAR(date) as year FROM `tblcashbookentry` WHERE `business_name` = '$Bname'";    
                    $stmt = $conn->prepare($query);
                    $stmt->execute();
                    $result = $stmt->get_result();
                ?>   
               <select id = "year_run" name="year">    
                    <option value="" disabled selected>YEAR</option>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <option value="<?= $row['year']; ?>"><?= $row['year']; ?></option>
                    <?php } ?>  
               </select>


                        </span>
              
                        <div class="input-container cta">
                                        <button class="signup-btn continue" type="submit" name="run_dashboard_report">RUN REPORT</button>
                                </div>
                                
                        </div>
             </form>

             <script>
             function setDataOnSelection(){
                            $('#year_run').val("<?php echo $_SESSION['year_is']?>");
                        }

                    </script>

  <div class="main-content">
                <div class="info-card">
                  
                <div class="card1">

                <h3 class="graph-header">INCOME STATEMENT FOR THE YEAR <?php echo $_SESSION['year_is']?>  </h3>
                <div id="piechart" class="cards" style="width: 600px; height: 400px;"></div>
                </div>
               
                <div class="card1">
                <h3 class="graph-header">CASH FLOW FOR THE YEAR <?php echo $_SESSION['year_is']?>  </h3>
                <div id="donutchart" class="cards" style="width: 600px; height: 400px;"></div>
                </div>
            </div>

     </div>
     <div class="main-content ">

     <div class="info-card chart2">
      <div class="card1">
      <h3 class="graph-header">INCOME STATEMENT FOR THE YEAR <?php echo $_SESSION['year_is']?>  </h3>
                  <div id="chart_div" class="cards" style="width: 500px; height: 300px;"></div>
              </div>

     </div>

     </div>


     <div class="main-content ">
                <div class="info-card chart1">
               
                <div class="card1">
                <h3 class="graph-header">CASH FLOW FOR THE YEAR <?php echo $_SESSION['year_is']?>  </h3>
                <div id="columnchart_values" class="cards" style="width: 700px; height: 300px;"></div>
                </div>
                
            </div>

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


         <?php include 'includes/footer.php' ?>

         
  <!-- <
       
<div class="dashboard">
          <div class="wrapper">

               <div id="piechart" class="piechart cards"></div>
               <div id="columnchart_values" class="bar cards"></div>

           </div>
   </div>

   
  -->
<?php

    $Bname = $_SESSION['business_name'];
    $year =  $_SESSION ['year_is'];
    $income = array();
    $expense = array();

          for ($i=0; $i < 13; $i++) { 
            # code...
            $income[$i] = 0;
            $expense[$i] = 0;
          }


          $query = "SELECT COUNT(DISTINCT date_month) AS total_month FROM `tblincomestatement` WHERE `type` = 'Monthly' AND `date_year` = '$year' AND `business_name` = '$Bname' AND (`category` = 'INCOME' OR `category` = 'EXPENSES')";    
          $stmt = $conn->prepare($query);
          $stmt-> execute();
          $result = $stmt->get_result();  
      
          while ($row = $result->fetch_assoc()) {
              $total_month = $row['total_month'];
          }


    for ($i=1; $i <= $total_month ; $i++) {  
        # code...

        $query = "SELECT  SUM(amount) AS total_amount FROM `tblincomestatement` WHERE `date_month` = '$i' AND `date_year` = '$year' AND `business_name` = '$Bname' AND `category` = 'INCOME'";    
        $stmt = $conn->prepare($query);
        $stmt-> execute();
        $result = $stmt->get_result();  
    
        while ($row = $result->fetch_assoc()) {
            $income[$i] = $row['total_amount'];
        }


        $query = "SELECT  SUM(amount) AS total_amount FROM `tblincomestatement` WHERE `date_month` = '$i' AND `date_year` = '$year' AND `business_name` = '$Bname' AND `category` = 'EXPENSES'";    
        $stmt = $conn->prepare($query);
        $stmt-> execute();
        $result = $stmt->get_result();  
    
        while ($row = $result->fetch_assoc()) {
            $expense[$i] = $row['total_amount'];
        }
    }

    for ($i=1; $i < 13 ; $i++) { 
      # code...
      $total_income = $total_income + $income[$i];
      $total_expenses = $total_expenses + $expense[$i];
    }




          $lastmoney = array ();         
          $lastcolor = array ();     
          
          for ($i=0; $i < 13; $i++) { 
            # code...
            $lastmoney[$i] = 0;
            $lastcolor[$i] = "";
          }


          $f_neg = 0;
          $f_pos = 0;

          $o_neg = 0;
          $o_pos = 0;

          $i_neg = 0;
          $i_pos = 0;


    for ($i=1; $i <= 12 ; $i++) { 
      # code...
                    //financing
                    $query = "SELECT  SUM(amount) AS total_amount FROM `tblcashflow` WHERE `date_month` = '$i' AND `date_year` = '$year' AND `business_name` = '$Bname' AND `category` = 'FINANCING' AND `sign` = 'negative'";    
                    $stmt = $conn->prepare($query);
                    $stmt-> execute();
                    $result = $stmt->get_result();  

                  
                    while ($row = $result->fetch_assoc()) {
                        $f_neg = $row['total_amount'];
                    }

                    $query = "SELECT  SUM(amount) AS total_amount FROM `tblcashflow` WHERE `date_month` = '$i' AND `date_year` = '$year' AND `business_name` = '$Bname' AND `category` = 'FINANCING' AND `sign` = 'positive'";    
                    $stmt = $conn->prepare($query);
                    $stmt-> execute();
                    $result = $stmt->get_result();  

                    while ($row = $result->fetch_assoc()) {
                      $f_pos = $row['total_amount'];
                    }

                    $totalFIO = $f_pos - $f_neg;

                    //operating
                    $query = "SELECT  SUM(amount) AS total_amount FROM `tblcashflow` WHERE `date_month` = '$i' AND `date_year` = '$year' AND `business_name` = '$Bname' AND `category` = 'OPERATING' AND `sign` = 'negative'";    
                    $stmt = $conn->prepare($query);
                    $stmt-> execute();
                    $result = $stmt->get_result();  

                  
                    while ($row = $result->fetch_assoc()) {
                        $o_neg = $row['total_amount'];
                    }

                    $query = "SELECT  SUM(amount) AS total_amount FROM `tblcashflow` WHERE `date_month` = '$i' AND `date_year` = '$year' AND `business_name` = '$Bname' AND `category` = 'OPERATING' AND `sign` = 'positive'";    
                    $stmt = $conn->prepare($query);
                    $stmt-> execute();
                    $result = $stmt->get_result();  

                    while ($row = $result->fetch_assoc()) {
                      $o_pos = $row['total_amount'];
                    }

                    $totalOPO = $o_pos - $o_neg;


                    //investing
                    $query = "SELECT  SUM(amount) AS total_amount FROM `tblcashflow` WHERE `date_month` = '$i' AND `date_year` = '$year' AND `business_name` = '$Bname' AND `category` = 'INVESTING' AND `sign` = 'negative'";    
                    $stmt = $conn->prepare($query);
                    $stmt-> execute();
                    $result = $stmt->get_result();  

                  
                    while ($row = $result->fetch_assoc()) {
                        $i_neg = $row['total_amount'];
                    }

                    $query = "SELECT  SUM(amount) AS total_amount FROM `tblcashflow` WHERE `date_month` = '$i' AND `date_year` = '$year' AND `business_name` = '$Bname' AND `category` = 'INVESTING' AND `sign` = 'positive'";    
                    $stmt = $conn->prepare($query);
                    $stmt-> execute();
                    $result = $stmt->get_result();  

                    while ($row = $result->fetch_assoc()) {
                      $i_pos = $row['total_amount'];
                    }


                    $totalINO = $i_pos - $i_neg;

                    $lastmoney[$i] = $totalFIO + $totalINO + $totalOPO;

                    if($lastmoney[$i] > 0){
                      $lastcolor[$i] = "#17b978";
                    }else{
                      $lastcolor[$i] = "#F25E5E"; 
                    }


    }

    $financing = 0;
    $investing = 0;
    $operating = 0;


    //financing
    $query = "SELECT  SUM(amount) AS total_amount FROM `tblcashflow` WHERE `date_month` = '$year' AND `date_year` = '$year' AND `business_name` = '$Bname' AND `category` = 'FINANCING' AND `sign` = 'negative'";    
    $stmt = $conn->prepare($query);
    $stmt-> execute();
    $result = $stmt->get_result();  

  
    while ($row = $result->fetch_assoc()) {
        $f_neg = $row['total_amount'];
    }

    $query = "SELECT  SUM(amount) AS total_amount FROM `tblcashflow` WHERE `date_month` = '$year' AND `date_year` = '$year' AND `business_name` = '$Bname' AND `category` = 'FINANCING' AND `sign` = 'positive'";    
    $stmt = $conn->prepare($query);
    $stmt-> execute();
    $result = $stmt->get_result();  

    while ($row = $result->fetch_assoc()) {
      $f_pos = $row['total_amount'];
    }

    $financing = $f_pos - $f_neg;
    if($financing < 0){
      $financing = substr($financing,1);
    }

    //operating
    $query = "SELECT  SUM(amount) AS total_amount FROM `tblcashflow` WHERE `date_month` = '$year' AND `date_year` = '$year' AND `business_name` = '$Bname' AND `category` = 'OPERATING' AND `sign` = 'negative'";    
    $stmt = $conn->prepare($query);
    $stmt-> execute();
    $result = $stmt->get_result();  

  
    while ($row = $result->fetch_assoc()) {
        $o_neg = $row['total_amount'];
    }

    $query = "SELECT  SUM(amount) AS total_amount FROM `tblcashflow` WHERE `date_month` = '$year' AND `date_year` = '$year' AND `business_name` = '$Bname' AND `category` = 'OPERATING' AND `sign` = 'positive'";    
    $stmt = $conn->prepare($query);
    $stmt-> execute();
    $result = $stmt->get_result();  

    while ($row = $result->fetch_assoc()) {
      $o_pos = $row['total_amount'];
    }

    $operating = $o_pos - $o_neg;
    if($operating < 0){
      $operating = substr($operating,1);
    }


    //investing
    $query = "SELECT  SUM(amount) AS total_amount FROM `tblcashflow` WHERE `date_month` = '$year' AND `date_year` = '$year' AND `business_name` = '$Bname' AND `category` = 'INVESTING' AND `sign` = 'negative'";    
    $stmt = $conn->prepare($query);
    $stmt-> execute();
    $result = $stmt->get_result();  

  
    while ($row = $result->fetch_assoc()) {
        $i_neg = $row['total_amount'];
    }

    $query = "SELECT  SUM(amount) AS total_amount FROM `tblcashflow` WHERE `date_month` = '$year' AND `date_year` = '$year' AND `business_name` = '$Bname' AND `category` = 'INVESTING' AND `sign` = 'positive'";    
    $stmt = $conn->prepare($query);
    $stmt-> execute();
    $result = $stmt->get_result();  

    while ($row = $result->fetch_assoc()) {
      $i_pos = $row['total_amount'];
    }


    $investing = $i_pos - $i_neg;
    if($investing < 0){
      $investing = substr($investing,1);
    }


?>


  
  <script type="text/javascript">
      google.charts.load('current', {'packages':['corechart', 'bar']});
      google.charts.setOnLoadCallback(drawStuff);

      function drawStuff() {

        var button = document.getElementById('change-chart');
        var chartDiv = document.getElementById('chart_div');

        var data = google.visualization.arrayToDataTable([
          ['', 'INCOME', 'EXPENSES'],
          ['JAN', <?php echo $income[1]?>, <?php echo $expense[1]?>],
          ['FEB', <?php echo $income[2]?>, <?php echo $expense[2]?>],
          ['MAR', <?php echo $income[3]?>, <?php echo $expense[3]?>],
          ['APR', <?php echo $income[4]?>, <?php echo $expense[4]?>],
          ['MAY', <?php echo $income[5]?>, <?php echo $expense[5]?>],
          ['JUN', <?php echo $income[6]?>, <?php echo $expense[6]?>],
          ['JUL', <?php echo $income[7]?>, <?php echo $expense[7]?>],
          ['AUG', <?php echo $income[8]?>, <?php echo $expense[8]?>],
          ['SEP', <?php echo $income[9]?>, <?php echo $expense[9]?>],
          ['OCT', <?php echo $income[10]?>, <?php echo $expense[10]?>],
          ['NOV', <?php echo $income[11]?>, <?php echo $expense[11]?>],
          ['DEC', <?php echo $income[12]?>, <?php echo $expense[12]?>]
        ]);

        var materialOptions = {
          width: 800,
          chart: {
            title: '',
            subtitle: ''
          },
          series: {
            0: { axis: 'INCOME' }, // Bind series 0 to an axis named 'distance'.
            1: { axis: 'EXPENSES' } // Bind series 1 to an axis named 'brightness'.
          },
          axes: {
            y: {
              distance: {label: 'parsecs'}, // Left y-axis.
              brightness: {side: 'right', label: 'apparent magnitude'} // Right y-axis.
            }
          }
        };

        

        function drawMaterialChart() {
          var materialChart = new google.charts.Bar(chartDiv);
          materialChart.draw(data, google.charts.Bar.convertOptions(materialOptions));
          button.innerText = 'Change to Classic';
          button.onclick = drawClassicChart;
        }

        function drawClassicChart() {
          var classicChart = new google.visualization.ColumnChart(chartDiv);
          classicChart.draw(data, classicOptions);
          button.innerText = 'Change to Material';
          button.onclick = drawMaterialChart;
        }

        drawMaterialChart();
    };
    


    // DONUT CHART

google.charts.load("current", {packages:["corechart"]});
google.charts.setOnLoadCallback(drawChart);
function drawChart() {
  var data = google.visualization.arrayToDataTable([
    ['INCOME', 'EXPENSES'],
    ['OPERATING',      <?php echo $operating?>],
    ['FINANCING',      <?php echo $financing?>],
    ['INVESTING',      <?php echo $investing?>]
    
  ]);

  var options = {
    title: '',
    pieHole: 0.4,
  };

  var chart = new google.visualization.PieChart(document.getElementById('donutchart'));
  chart.draw(data, options);
}





</script>




//
<script>

  google.charts.load("current", {packages:['corechart']});
    google.charts.setOnLoadCallback(drawChart);
    function drawChart() {
      var data = google.visualization.arrayToDataTable([
        ["cash", "amount", { role: "style" } ],
        ["JAN", <?php echo $lastmoney[1]?>, "<?php echo $lastcolor[1]?>"],
        ["FEB", <?php echo $lastmoney[2]?>, "<?php echo $lastcolor[2]?>"],
        ["MAR", <?php echo $lastmoney[3]?>, "<?php echo $lastcolor[3]?>"],
        ["APR", <?php echo $lastmoney[4]?>, "<?php echo $lastcolor[4]?>"],
        ["MAY", <?php echo $lastmoney[5]?>, "<?php echo $lastcolor[5]?>"],
        ["JUN", <?php echo $lastmoney[6]?>, "<?php echo $lastcolor[6]?>"],
        ["JUL", <?php echo $lastmoney[7]?>, "<?php echo $lastcolor[7]?>"],
        ["AUG", <?php echo $lastmoney[8]?>, "<?php echo $lastcolor[8]?>"],
        ["SEP", <?php echo $lastmoney[9]?>, "<?php echo $lastcolor[9]?>"],
        ["OCT", <?php echo $lastmoney[10]?>, "<?php echo $lastcolor[10]?>"],
        ["NOV", <?php echo $lastmoney[11]?>, "<?php echo $lastcolor[11]?>"],
        ["DEC", <?php echo $lastmoney[12]?>, "<?php echo $lastcolor[12]?>"]
      ]);

      var view = new google.visualization.DataView(data);
      view.setColumns([0, 1,
                       { calc: "stringify",
                         sourceColumn: 1,
                         type: "string",
                         role: "annotation" },
                       2]);

      var options = {
        title: "",
        width: 900,
        height: 300,
        bar: {groupWidth: "80%"},
        legend: { position: "none" },
      };
      var chart = new google.visualization.ColumnChart(document.getElementById("columnchart_values"));
      chart.draw(view, options);
  }
  
      </script>

    

      <script>

google.charts.load('current', {'packages':['corechart']});
google.charts.setOnLoadCallback(drawChart);

function drawChart() {

  var data = google.visualization.arrayToDataTable([
    ['INCOME', 'AMOUNT'],
    ['INCOME',   <?php echo $total_income?>],
    ['EXPENSES', <?php echo $total_expenses?>],
    
  ]);

  var options = {
    title:''
  };

  var chart = new google.visualization.PieChart(document.getElementById('piechart'));

  chart.draw(data, options);
}
      </script>


</body>