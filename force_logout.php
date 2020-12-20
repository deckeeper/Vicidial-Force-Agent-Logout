<?php	
	$link = mysqli_connect("localhost","cron","1234","asterisk");
	$now_date_epoch = date('U');
	$inactive_epoch = ($now_date_epoch - 60);
	$stmt = "SELECT user,campaign_id,UNIX_TIMESTAMP(last_update_time),status,conf_exten,server_ip from vicidial_live_agents where user='admin';";
	$rslt=mysqli_query($link,$stmt);
	$vla_ct = mysqli_num_rows($rslt);
	if ($vla_ct > 0)
		{
		$row=mysqli_fetch_row($rslt);
		$VLA_user =					$row[0];
		$VLA_campaign_id =			$row[1];
		$VLA_update_time =			$row[2];
		$VLA_status =				$row[3];
		$VLA_conf_exten =			$row[4];
		$VLA_server_ip =			$row[5];

		if ($VLA_update_time > $inactive_epoch)
			{
			$lead_active=0;
			$stmt = "SELECT agent_log_id,user,user_group from vicidial_agent_log where user='$VLA_user' order by agent_log_id desc LIMIT 1;";
			$rslt=mysqli_query($link,$stmt);
			$val_ct = mysqli_num_rows($rslt);
			if ($val_ct > 0)
				{
				$row=mysqli_fetch_row($rslt);
				$VAL_agent_log_id =		$row[0];
				$VAL_user =				$row[1];
				$VAL_user_group =		$row[2];
				}
			}

		$stmt="DELETE from vicidial_live_agents where user='$VLA_user';";
		$rslt=mysqli_query($link,$stmt);

		$local_DEF = 'Local/5555';
		$local_AMP = '@';
		$ext_context = 'default';
		$kick_local_channel = "$local_DEF$VLA_conf_exten$local_AMP$ext_context";
		$queryCID = "ULGH3457$StarTtimE";

		$stmtC="INSERT INTO vicidial_manager values('','',NOW(),'NEW','N','$VLA_server_ip','','Originate','$queryCID','Channel: $kick_local_channel','Context: $ext_context','Exten: 8300','Priority: 1','Callerid: $queryCID','','','','$channel','$exten');";
		$rslt=mysqli_query($link,$stmtC);

		$stmtB = "INSERT INTO vicidial_user_log (user,event,campaign_id,event_date,event_epoch,user_group,extension) values('$VLA_user','LOGOUT','$VLA_campaign_id',NOW(),'$now_date_epoch','$VAL_user_group','MGR LOGOUT: $PHP_AUTH_USER');";
		$rslt=mysqli_query($link,$stmtB);

		$stmt="INSERT INTO vicidial_admin_log set event_date=NOW(), user='$VLA_user', ip_address='$VLA_server_ip', event_section='USERS', event_type='LOGOUT', record_id='$VLA_user', event_code='EMERGENCY LOGOUT FROM STATUS PAGE', event_sql=\"$SQL_log\", event_notes='agent_log_id: $VAL_agent_log_id';";
		$rslt=mysqli_query($link,$stmt);
	}