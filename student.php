<?php
    include_once "connect.php";
    $reg_success = 0;
    $card_exist = 0;

//if any login cookie, check here

//any form submit logicSS
    if(isset($_POST['register_student']))
    {  
        $student_id = $_POST['student_id'];                    
        $student_name = $_POST['student_name'];                    
        $class_id = $_POST['class_id'];       
        
        //check if card already assigned to a student
        $check_card_exist = $conn->prepare("SELECT * FROM student WHERE student_id=(?)");
        $check_card_exist->bind_param('s',$student_id);
        $check_card_exist->execute();
        $card_exist_res = $check_card_exist->get_result();
        $card_exist_row = $card_exist_res->fetch_assoc();
        if($card_exist_row)
        {
            $card_exist = 1;
        }
        else
        {
            $student_insert = $conn->prepare("INSERT INTO student (student_id,student_name,class_id) VALUES (?,?,?)");
            $student_insert->bind_param('ssi',$student_id,$student_name,$class_id);
            if($student_insert->execute())
            {
                ?><script>alert("New Student Registered.");window.location.href="student.php";</script><?php
            }
            else{
                ?><script>alert("Unexpected Error.");history.back();</script><?php

            }

        }
    }

//select queries

    //prepopulate student_card_uid from rfid_punch
    $get_card_uid = $conn->prepare("SELECT * 
                             FROM rfid_punch 
                             WHERE DATE(entry_timestamp) = DATE(NOW()) 
                             AND TIMESTAMPDIFF(SECOND, entry_timestamp, NOW()) <= 10"
                            );
    $get_card_uid->execute();
    $get_card_uid_res = $get_card_uid->get_result();
    $get_card_uid_row = $get_card_uid_res->fetch_assoc();                      

    //pre-populate class
    $fetch_class = $conn->prepare("SELECT * FROM classroom");
    $fetch_class->execute();
    $fetch_class_res = $fetch_class->get_result();

    //fetch all student and their resp class_name
    $fetch_student = $conn->prepare("SELECT student.*, classroom.class_name FROM student JOIN classroom ON student.class_id = classroom.class_id");
    $fetch_student->execute();
    $fetch_student_res = $fetch_student->get_result();
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Student Attendance</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="assets/css/style.css" rel="stylesheet">

    
</head>

<body>
  <!-- ======= Header ======= -->
  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="class.php" class="logo d-flex align-items-center">
        <img src="assets/img/logo.png" alt="">
        <span class="d-none d-lg-block">Admin</span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

    

  </header><!-- End Header -->

  <!-- ======= Sidebar ======= -->
  <aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">




      <li class="nav-item">
        <a class="nav-link collapsed" href="class.php">
          <i class="bi bi-question-circle"></i>
          <span>Class</span>
        </a>
      </li><!-- End F.A.Q Page Nav -->

      <li class="nav-item">
        <a class="nav-link collapsed" href="subject.php">
          <i class="bi bi-question-circle"></i>
          <span>Subject</span>
        </a>
      </li><!-- End Subject Page Nav -->

      <li class="nav-item">
        <a class="nav-link collapsed" href="student.php">
          <i class="bi bi-envelope"></i>
          <span>Student</span>
        </a>
      </li><!-- End Contact Page Nav -->

      <li class="nav-item">
        <a class="nav-link collapsed" href="timetable.php">
          <i class="bi bi-card-list"></i>
          <span>Timetable</span>
        </a>
      </li><!-- End Register Page Nav -->

      <li class="nav-item">
        <a class="nav-link collapsed" href="student.php">
          <i class="bi bi-box-arrow-in-right"></i>
          <span>Attendance</span>
        </a>
      </li><!-- End Login Page Nav -->

    </ul>

  </aside><!-- End Sidebar-->


  <main id="main" class="main">
  <?php
if ($card_exist) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <strong>Student Exists !</strong> Card already assigned. Retry using a different Card.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>';
}
?>

    <div class="pagetitle">
      <h1>Manage Studends</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="#">Home</a></li>
          <li class="breadcrumb-item">Pages</li>
          <li class="breadcrumb-item active">Blank</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row">
        <div class="col-lg-12">

          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Register Student</h5>

              <!-- Add Students form -->
              <form method="post">

                <div class="form-group">
                  <label for="student_id">Student Card UID:</label>
                  <input type="text" class="form-control" id="student_id" aria-describedby="emailHelp" name="student_id" value="<?php echo isset($get_card_uid_row) ? $get_card_uid_row['rfid_data'] : "No Card Scanned"; ?>" required>
                </div>
                <div class="form-group my-3">
                  <label for="student_name">Student Name:</label>
                  <input type="text" class="form-control" id="student_name" aria-describedby="emailHelp" placeholder="Enter Student name" name="student_name" required>
                </div>
                
                <!-- Dropdown for Class Prepopulated -->
                <div class="form-group my-3">
                  <label for="class_id">Class:</label>
                  <select class="form-control" id="class_id" name="class_id" required>
                    <option disabled selected>--Select Class--</option>
                      <?php
                        while($fetch_class_row = $fetch_class_res->fetch_assoc())
                        {
                            echo "<option value='".$fetch_class_row['class_id']."'>".$fetch_class_row['class_name']."</option>";
                        }
                      ?>
                  </select>
                </div>
                <div class="form-group my-2">
                    <button type="submit" class="btn btn-primary" name="register_student">Register</button>
                </div>
              </form>
            </div>
          </div>

        </div>

        <div class="col-lg-12">

          <div class="card">
            <div class="card-body">
              <h5 class="card-title">All Students</h5>
              <table class="table datatable">
                <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Card ID</th>
                        <th scope="col">Class</th>
                        <th scope="col">Operation</th>
                        <th scope="col">Attendance</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    while($fetch_student_row  = $fetch_student_res->fetch_assoc())
                    {

                        echo "<tr>
                                <td>".$fetch_student_row['student_name']."</td>
                                <td>".$fetch_student_row['student_id']."</td>
                                <td>".$fetch_student_row['class_name']."</td>
                                <td>
                                    <button class='btn btn-primary'><a href='student_update.php?student_update_id=".$fetch_student_row['id']."' class='text-light'>Update</a></button>
                                    <button class='btn btn-danger'><a onClick=\" javascript:return confirm('Are You Sure to delete this'); \" href='student_delete.php?student_delete_id=".$fetch_student_row['id']."' class='text-light'>Delete</a></button>
                                </td>
                                <td>
                                  <button class='btn btn-dark'><a href='attendance.php?student_id=".$fetch_student_row['student_id']."&student_name=".$fetch_student_row['student_name']."&class_id=".$fetch_student_row['class_id']."&class_name=".$fetch_student_row['class_name']."' class='text-light'>View</a></button>
                                </td>
                              </tr>";
                    }
                    ?>

                </tbody>
              </table>
            </div>
          </div>

        </div>
      </div>
    </section>

  </main><!-- End #main -->

  <!-- ======= Footer ======= -->
  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer">
    <div class="copyright">
      &copy; Copyright <strong><span>APV</span></strong>. All Rights Reserved
    </div>
    <div class="credits">
    </div>
  </footer><!-- End Footer -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/chart.js/chart.umd.js"></script>
  <script src="assets/vendor/echarts/echarts.min.js"></script>
  <script src="assets/vendor/quill/quill.min.js"></script>
  <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>

</body>

</html>