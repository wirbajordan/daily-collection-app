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

<?php $result = mysqli_query($mysqli, "SELECT * from users where role='contributor'") or die('Error'); ?>


<div class="panel"><table class="table table-striped title1"  style="vertical-align:middle">
        <tr><td style="vertical-align:middle"><b>S.N.</b></td>
             <td style="vertical-align:middle"><b>username</b></td>
            <td style="vertical-align:middle"><b>email</b></td>
            <td style="vertical-align:middle"><b>phone_number</b></td>
         <td style="vertical-align:middle" colspan="2"><b>Action</b></td>                                    
        </tr>
        <a href="admin.php?q=2 & page=<?php echo base64_encode('ubpages/ubsettings/course/addCourse/addCourse'); ?>"class="btn btn-primary" style="margin-left:92%;">Add Contributor +</a>

        <?php
        $c = 1;
        while ($row = mysqli_fetch_array($result)) {     
            $username = $row['username'];
            $email = $row['email'];
            $phone_number = $row['phone_number'];
            //$quiz_num = $row['number_of_quiz'];
            ?>

            <tr><td style="vertical-align:middle"><?php echo'' . $c++ . '' ?></td>
                <td style="vertical-align:middle"><?php echo'' . $username . '' ?></td>
                <td style="vertical-align:middle"><?php echo'' . $email . '' ?></td>
                <td style="vertical-align:middle"><?php echo'' . $phone_number . '' ?></td>
                <td><a href="admin.php?q=2 & page=<?php echo base64_encode('ubpages/ubsettings/course/editCourse/courseedit'); ?> <?php echo '&course_id=' . $course_id .  '&course_name=' . $name . '&course_price=' . $price . '&number_of_quiz=' . $quiz_num . ' " class="glyphicon glyphicon-edit" style="font-size:18px;padding:5px; color:#0d6efd;"></a></td>' ?>
                <td><a href='admin.php?q=2 & page=<?php echo base64_encode('ubpages/ubsettings/course/courseAjax'); ?>&course_name=<?php echo ''.$name.'' ?>' onclick="return(confirm('Are you sure to delete this course ?'));" class="glyphicon glyphicon-trash" style="font-size:18px;padding:5px; color:red; "></a></td>
            </tr>
            <?php
        }
        ?>
