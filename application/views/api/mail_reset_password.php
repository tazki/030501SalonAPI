<!DOCTYPE html
	PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet">
	<title></title>
	<style>
		#btn-reset {
			color: white;
			border: none;
			background: black;
			padding: 10px;
			cursor: pointer;
			outline: none;
			border-radius: 4px;
		}

		.passfield {
			outline: none;
			border: 1px solid #BEBEBE;
			padding: 7px;
			width: 97%;
		}

		.lblpass {
			font-size: 13px;
			font-weight: 600;
		}

	</style>
</head>

<body bgcolor="#fff">
	<img style="width: 300px; display: block; margin: 30px auto;" src="<?php echo base_url('images/');?>nailartist-landscape-black.jpg" alt="" />
	<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#fff">
		<tr>
			<td>
				<table style="box-shadow: 0px 3px 12px 1px rgba(0, 0, 0, 0.1); padding: 10px; width: 30vw" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF" align="center">
					<tr>
						<td>
							<table width="100%" border="0" cellspacing="0" cellpadding="0">
									<font style="font-family: 'Lato', sans-serif;color:#010101; font-size:24px">
										<strong>Reset Password</strong></font>
									<br />
									<br />
									<form method="post" action="<?php echo $form_url; ?>">
										<font style="font-family: 'Lato', sans-serif; line-height:21px">
											<?php if(isset($status))
												{
													echo '<p style="'.(($status=='danger') ? 'color:red;' : '').'">'.$alert.'</p>';
												} ?>
											<?php if(!isset($status) || (isset($status) && $status=='danger')) { ?>
											<div>
												<label class="lblpass">New Password</label>
												<br>
												<input class="passfield" type="password" name="user_password" />
											</div>
											<div>
												<label class="lblpass">Confirm Password</label>
												<br>
												<input class="passfield" type="password" name="user_confirm_password" />
											</div>
											<br>
											<div>
												<input id="btn-reset" type="submit" value="Reset Password">
											</div>
											<?php } ?>
										</font>
									</form>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</body>
</html>
