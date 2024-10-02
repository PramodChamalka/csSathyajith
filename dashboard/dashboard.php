<?php include('includes/header.php'); 
// include('session.php');?>
<div class="dashboard-container">
    <?php include('includes/sidebar.php'); ?>
    <main>
        <div class="dashboard-content">
            <h1>Welcome to the Admin Dashboard</h1>
            <!-- $servername = "localhost";
$username = "root";
$password = "";
$dbname = "sathyajith"; -->

            <div class="cards">
                <div class="card">
                    <h3>Users</h3>
                    <p>125 Active Users</p>
                </div>
                <div class="card">
                    <h3>Sales</h3>
                    <p>$23,000</p>
                </div>
                <div class="card">
                    <h3>Orders</h3>
                    <p>312 New Orders</p>
                </div>
            </div>
        </div>
    </main>
</div>
<?php include('includes/footer.php'); ?>
