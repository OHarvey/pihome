<?php 
#!/usr/bin/php
echo "\033[36m";
echo "\n";
echo "   _____    _   _    _                             \n";
echo "  |  __ \  (_) | |  | |                            \n";
echo "  | |__) |  _  | |__| |   ___    _ __ ___     ___  \n";
echo "  |  ___/  | | |  __  |  / _ \  | |_  \_ \   / _ \ \n";
echo "  | |      | | | |  | | | (_) | | | | | | | |  __/ \n";
echo "  |_|      |_| |_|  |_|  \___/  |_| |_| |_|  \___| \n";
echo " \033[0m \n";
echo "     \033[45m S M A R T   H E A T I N G   C O N T R O L \033[0m \n";
echo "\033[31m";
echo "********************************************************\n";
echo "*   Gateway Script Version 0.2 Build Date 22/01/2018   *\n";
echo "*                                Have Fun - PiHome.eu  *\n";
echo "********************************************************\n";
echo " \033[0m \n";

require_once(__DIR__.'../../st_inc/connection.php');
require_once(__DIR__.'../../st_inc/functions.php'); 

//Set php script execution time in seconds
ini_set('max_execution_time', 40); 

//query to get gateway information 
$query = "SELECT * FROM gateway where status = 1 order by id asc LIMIT 1;";
$result = $conn->query($query);
$row = mysqli_fetch_array($result);
$gw_type = $row['type'];
$gw_location = $row['location'];
$gw_port = $row['port'];
$gw_pid = $row['pid'];
$gw_reboot = $row['reboot'];
$find_gw = $row['find_gw'];

//if reboot set to 1 then kill gateway PID and set reboot status to 0
if ($gw_reboot == '1') {
	exec("kill -9 $gw_pid");
	$query = "UPDATE gateway SET reboot = '0' LIMIT 1;";
	$conn->query($query);
	echo mysqli_error($conn)."\n";
}

//if find_gw set to 1 then start the search script and set find_gw to 0
if ($find_gw == '1') {
	exec("ps ax | grep find_mygw.py", $fgw_pids); 
	$gw_script_txt = 'python /var/www/cron/find_mygw/find_mygw.py';
	$fgw_position = searchArray($gw_script_txt, $fgw_pids);
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Checking Python Script Status to Find Smart Home Gateway \n";
	if($fgw_position===false) {
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Search for Smart Home Gateway \033[41mNot Running\033[0m \n";
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Starting Search for Smart Home Gateway \n";
		exec("python /var/www/cron/find_mygw/find_mygw.py </dev/null >/dev/null 2>&1 & ");
		exec("ps aux | grep '$gw_script_txt' | grep -v grep | awk '{ print $2 }' | head -1", $out);
		echo "\033[36m".date('Y-m-d H:i:s')."\033[0m - Search Script Started on PID: \033[41m".$out[0]."\033[0m \n";
	} else {
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Search for Smart Home Gateway \033[42mRunning\033[0m \n";
		exec("ps aux | grep '$gw_script_txt' | grep -v grep | awk '{ print $2 }' | head -1", $out);
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Search Script PID is: \033[42m" . $out[0]."\033[0m \n";
		$pid_details = exec("ps -p '$out[0]' -o lstart=");
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Search Script Running Since: ".$pid_details."\n";
	}
}

?>