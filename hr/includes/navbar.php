<div class="header">
		<div class="header-left">
			<div class="menu-icon dw dw-menu"></div>			
		</div>
		<div class="header-right">
			<div class="dashboard-setting user-notification">
				<div class="dropdown">
					<a class="dropdown-toggle no-arrow" href="javascript:;" data-toggle="right-sidebar">
						<i class="dw dw-settings2"></i>
					</a>
				</div>
			</div>
			
			<div class="user-info-dropdown">
				<div class="dropdown">

				<?php $query= mysqli_query($conn,"select * from employee where employee_id = '$session_id'")or die(mysqli_error());
								$row = mysqli_fetch_array($query);
						?>
					<a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
						<span class="user-name"><?php echo $row['name']; ?></span>
					</a>
					<div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
						<a class="dropdown-item" href="../logout.php"><i class="dw dw-logout"></i> Log Out</a>
					</div>
				</div>
			</div>
			
		</div>
	</div>