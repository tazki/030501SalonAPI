<!DOCTYPE html
	PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
   <link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet">
	<title></title>
</head>
<img style="width: 300px; display: block; margin: 30px auto;"
	src="<?php echo base_url('images/');?>nailartist-landscape-black.jpg" alt="" />

<body bgcolor="#fff">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#fff">
		<tr>
			<td>
				<table width="600" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF" align="center">
		</tr>
		<tr>
			<td>
				<table style="box-shadow: 0px 3px 12px 1px rgba(0, 0, 0, 0.1);
    padding: 10px;" width="100%" border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td width="100%" align="justify" valign="top">
							<font style="font-family: 'Lato', sans-serif; color:#010101; font-size:24px">
								<strong>Activate Activation</strong></font><br />
							<font
								style="font-family: 'Lato', sans-serif; color:#666766; font-size:13px; line-height:21px">
								<p>Congratulations, your Nailartists account has been created. To
									activate your account please click the following button below.</p>
								<a href="<?php echo site_url('api/member/activate/'.$encoded_email); ?>"
									style="margin: 0 auto; display: block; background-color: black; border-radius: 4px; color: #fff; font-family: 'Lato', sans-serif; font-size: 13px; font-weight: bold; line-height: 35px; text-align: center; text-decoration: none;width: 150px;">
									Activate Account
								</a>
								<p>If you are unable to validate your account through the link above, you may click the link
									below, or copy and paste it to the address bar of your browser.</p>
								<a href="<?php echo site_url('api/member/activate/'.$encoded_email); ?>"><?php echo site_url('api/member/activate/'.$encoded_email); ?></a>
								<br>
                        <br>
								<p style="margin:0;">Thank you,</p>
								<p style="margin:0;">Nailartists Team</p>
							</font>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	</td>
	</tr>
	</table>
</body>

</html>
