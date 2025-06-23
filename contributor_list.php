<h2 class="text-center"><span class="nam">Contributors</span></h2>

<?php
// Check user role
if ($_SESSION['role'] != 'administrator') {
    header('Location: .../login.php');//redirect to logged in if role not valide
    exit();
}


// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: .../login.php'); // Redirect to login if not logged in
    exit();
}    

 ?>

<?php 
// Modified query to include total contribution amount for each contributor
$result = mysqli_query($mysqli, "
    SELECT u.*, COALESCE(SUM(t.amount), 0) as total_amount 
    FROM users u 
    LEFT JOIN transaction t ON u.user_id = t.user_id 
    WHERE u.role='contributor' 
    GROUP BY u.user_id, u.username, u.email, u.phone_number
    ORDER BY u.username
") or die('Error'); 
?>


<div class="panel"><table class="table table-striped title1"  style="vertical-align:middle">
        <tr><td style="vertical-align:middle"><b>S.N.</b></td>
             <td style="vertical-align:middle"><b>username</b></td>
             <td style="vertical-align:middle"><b>email</b></td>
            <td style="vertical-align:middle"><b>phone_number</b></td>
            <td style="vertical-align:middle"><b>Total Amount (CFA)</b></td>
         <td style="vertical-align:middle" colspan="2"><b>Action</b></td>                                    
        </tr>
        <a href="admin.php?q=2&page=<?php echo base64_encode('ubpages/ubsettings/user/addContributor/addContributor'); ?>" class="btn btn-primary" style="margin-left:92%;">Add Contributor +</a>

        <?php
        $c = 1;
        while ($row = mysqli_fetch_array($result)) {     
            $user_id = $row['user_id'];
            $username = $row['username'];
            $email = $row['email'];
            $phone_number = $row['phone_number'];
            $total_amount = $row['total_amount'];
            ?>

            <tr><td style="vertical-align:middle"><?php echo'' . $c++ . '' ?></td>
                <td style="vertical-align:middle"><?php echo'' . $username . '' ?></td>
                <td style="vertical-align:middle"><?php echo'' . $email . '' ?></td>
                <td style="vertical-align:middle"><?php echo'' . $phone_number . '' ?></td>
                <td style="vertical-align:middle"><strong><?php echo number_format($total_amount, 2); ?> CFA</strong></td>
                <td><a href="admin.php?q=2&page=<?php echo base64_encode('ubpages/ubsettings/user/editUser/useredit'); ?>&user_id=<?php echo $user_id; ?>" class="glyphicon glyphicon-edit" style="font-size:18px;padding:5px; color:#0d6efd;"></a></td>
                <td><a href='admin.php?q=2&page=<?php echo base64_encode('ubpages/ubsettings/user/userAjax'); ?>&user_id=<?php echo $user_id; ?>' onclick="return(confirm('Are you sure to delete this contributor ?'));" class="glyphicon glyphicon-trash" style="font-size:18px;padding:5px; color:red; "></a></td>
            </tr>
            <?php
        }
        ?>
