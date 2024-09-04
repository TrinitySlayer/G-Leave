<?php include('includes/header.php')?>
<?php include('../includes/session.php')?>
<?php $get_id = $_GET['edit']; ?>

<?php 
	 if (isset($_GET['delete'])) {
		$department_id = $_GET['delete'];
		$sql = "DELETE FROM department WHERE dept_id = ".$department_id;
		$result = mysqli_query($conn, $sql);
		if ($result) {
			echo "<script>alert('Department deleted Successfully');</script>";
     		echo "<script type='text/javascript'> document.location = 'department.php'; </script>";
		}
	}
?>

<?php
 if(isset($_POST['edit'])) {
	 $deptname = $_POST['dept_name'];
	 $deptShortName = $_POST['departmentshortname'];

	 $result = mysqli_query($conn, "UPDATE department SET dept_name = '$deptname' WHERE dept_id = '$get_id'");
	 
	 if ($result) {
	 	echo "<script>alert('Record Successfully Updated');</script>";
	 	echo "<script type='text/javascript'> document.location = 'department.php'; </script>";
	} else {
		die(mysqli_error($conn));
	}
}
?>

<body>

	<?php include('includes/navbar.php')?>
	<?php include('includes/right_sidebar.php')?>
	<?php include('includes/left_sidebar.php')?>

	<div class="mobile-menu-overlay"></div>

	<div class="main-container">
		<div class="pd-ltr-20 xs-pd-20-10">
			<div class="min-height-200px">
				<div class="page-header">
					<div class="row">
						<div class="col-md-6 col-sm-12">
							<div class="title">
								<h4>Edit Department</h4>
							</div>
							<nav aria-label="breadcrumb" role="navigation">
								<ol class="breadcrumb">
									<li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
									<li class="breadcrumb-item active" aria-current="page">Edit Department</li>
								</ol>
							</nav>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-lg-4 col-md-6 col-sm-12 mb-30">
						<div class="card-box pd-30 pt-10 height-100-p">
							<h2 class="mb-30 h4">Edit Department</h2>
							<section>
								<?php
								$query = mysqli_query($conn, "SELECT * FROM department WHERE dept_id = '$get_id'") or die(mysqli_error($conn));
								$row = mysqli_fetch_array($query);
								?>

								<form name="save" method="post">
									<div class="row">
										<div class="col-md-12">
											<div class="form-group">
												<label>Department Name</label>
												<input name="dept_name" type="text" class="form-control" required="true" autocomplete="off" value="<?php echo $row['dept_name']; ?>">
											</div>
										</div>
									</div>
									<div class="col-sm-12 text-right">
										<div class="dropdown">
											<input class="btn btn-primary" type="submit" value="UPDATE" name="edit" id="edit">
										</div>
									</div>
								</form>
							</section>
						</div>
					</div>
					
					<div class="col-lg-8 col-md-6 col-sm-12 mb-30">
						<div class="card-box pd-30 pt-10 height-100-p">
							<h2 class="mb-30 h4">Department List</h2>
							<div class="pb-20">
								<table class="data-table table stripe hover nowrap">
									<thead>
										<tr>
											<th>SR NO.</th>
											<th class="table-plus">DEPARTMENT</th>
											<th class="datatable-nosort">ACTION</th>
										</tr>
									</thead>
									<tbody>
										<?php 
										$sql = "SELECT * FROM department";
										$query = mysqli_query($conn, $sql);
										$cnt = 1;
										if(mysqli_num_rows($query) > 0) {
											while($result = mysqli_fetch_assoc($query)) {
												echo '<tr>
														<td>' . htmlentities($cnt) . '</td>
														<td>' . htmlentities($result['dept_name']) . '</td>
														<td>
															<div class="table-actions">
																<a href="department.php?delete=' . htmlentities($result['dept_id']) . '" data-color="#e95959"><i class="icon-copy dw dw-delete-3"></i></a>
															</div>
														</td>
													</tr>';
												$cnt++;
											}
										}
										?>  
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>

			</div>

			<?php include('includes/footer.php'); ?>
		</div>
	</div>

	<?php include('includes/scripts.php')?>
</body>
</html>
