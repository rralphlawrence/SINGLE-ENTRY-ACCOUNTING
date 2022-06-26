 
            <?php include_once '../includes/dbprocess.php'; ?>

            <div class="nav" id="navbar">
                    <nav class="nav__container">
                        <div>
                            <a href="./dashboard.php" class="nav__link nav__logo">
                            <i style="font-size: 1.2rem;" class='bx bxs-store' ></i>
                                <?php  $Bname = $_SESSION['business_name']?>
                                <span class="nav__logo-name"><?php echo $Bname ?></span>
                            </a>
            
                            <div class="nav__list">
                                
                        
                                <div class="nav__items">
                                    
                                <h3 class="nav__subtitle">ACCOUNTING</h3>
            
                                    <a href="./dashboard.php" class="nav__link active">
                                        <i class='bx bx-home nav__icon' ></i>
                                        <span class="nav__name">DASHBOARD </span>
                                    </a>
                                  
                                    
                                    <div class="nav__dropdown">
                                        <a href="#" class="nav__link">
                                            <i class='bx bx-user nav__icon' ></i>
                                            <span class="nav__name">CASH BOOK</span>
                                            <i class='bx bx-chevron-down nav__icon nav__dropdown-icon'></i>
                                        </a>
        
                                        <div class="nav__dropdown-collapse">
                                          <div class="nav__dropdown-content">                 
                                                <a href="./cashbook.php" class="nav__dropdown-item">CASH BOOK ENTRY</a></a>
                                                <a href="./cashflow.php" class="nav__dropdown-item">CASHFLOW</a>
                                                <a href="./income.php" class="nav__dropdown-item">INCOME STATEMENT</a>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="../logout.php" class="nav__link">
                                        <i class='bx bx-log-out nav__icon' ></i>
                                        <span class="nav__name">LOG OUT</span>
                                    </a>
                                  
        
            
        
                      
                    </nav>
                </div>