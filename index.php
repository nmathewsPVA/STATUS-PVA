<?php
/*
-----------------------------------------------------
PVA's internal Vehicle Status Board
-----------------------------------------------------
*/
// JSON data feed URL
$url = "https://www2.monroecounty.gov/etc/ambulance/json.php?u=PITE&p=s24.PITE.42";

// set up curl request
$curl = curl_init($url);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$headers = array(
   "Accept: application/json",
   "Access-Control-Allow-Origin: *",
);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

//for debug only!
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

// call curl
$json = curl_exec($curl);

/* verbose errors
if(curl_exec($curl) === false)
{
    echo 'Curl error: ' . curl_error($ch);
}
else
{
    echo 'Operation completed without any errors';
} */

// close curl
curl_close($curl);

// decode json data into usable object
$json_data = json_decode($json,true);

// ------------LOCAL JSON TESTING ---------------
// Read the JSON file
//$json = file_get_contents('data.json');

// Decode the JSON file
//$json_data = json_decode($json,true);

// Print the JSON data
// print_r($json);

?>
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="refresh" content="60">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
	<style>
		.supervisors {
			margin-bottom: 2rem;
		}
		td.level {
			text-align: center;
			font-weight: 600;
			}
		.ALS {
			background-color: #FD6500 !important;
			color: #ffffff;
		}
		.BLS {
			background-color: #3232CB !important;
			color: #ffffff;
		}
		.RSI {
			background-color: #33CC00 !important;
			color: #ffffff;
		}
		.bucket {
			border: 1px solid rgba(0,0,0,.125);
			background: #efefef;
		}
		h2.duty-crew-heading, h2.on-call-heading, h2.on-duty-heading {
			text-align: center;
		}
		h2.duty-crew-heading {
		}
		.vehicle {
			padding: 10px 0 0 0;
		}
		.card {
			border: none;
			background: none;
		}
		.vehicle-card > .card > span.fa-solid {
			font-size: 2rem;
		}
		.card-body {
			padding: 0;
		}
	</style>
    <title>PVA Status Board</title>
	<link href="assets/fontawesome-free-6.0.0-web/css/all.css" rel="stylesheet" >
  </head>
  <body>
	<h1 class="text-center">PVA Status Board</h1>
	<div class="supervisors container-fluid">
		<div class="row">
			<div class="col-6">
					<div class="container-fluid bucket">
					<h2 class="on-call-heading">On-Call Chief</h2>
					<table class="table">
						<?php
							// iterate through JSON currentdata and populate crew data for On-Call Chief
							$search_val_chief = '2181';
							if($json_data) {
								foreach($json_data['currentdata'] as $currentdata) {
									if ($currentdata['vehicle_id'] == $search_val_chief && strtotime($currentdata['in']) < strtotime("now")) {
										echo("<tr><td>".$currentdata['first_name']." ".$currentdata['last_name']."</td><td>".$currentdata['phone']."</td><td>".date('n/j  H:i', strtotime($currentdata['in']))."</td><td>".date('n/j  H:i', strtotime($currentdata['out']))."</td></tr>");
									} elseif ($currentdata['vehicle_id'] == $search_val_chief && strtotime($currentdata['in']) > strtotime(time()) && strtotime($currentdata['in']) < strtotime('+2 hour',time())) {
										echo("<tr class=\"opacity-50\"><td class=\"level ".$currentdata['level']."\">".$currentdata['level']."</td><td>".$currentdata['first_name']." ".$currentdata['last_name']."</td><td>".$currentdata['phone']."</td><td>".date('n/j  H:i', strtotime($currentdata['in']))."</td><td>".date('n/j  H:i', strtotime($currentdata['out']))."</td></tr>");
									}
								}
							}
						?>
					</table>
				</div> <!-- end .bucket -->
			</div> <!-- end .col -->
			<div class="col-6">
				<div class="container-fluid bucket">
					<h2 class="on-duty-heading">Shift Supervisor</h2>
					<table class="table">
						<?php
							// iterate through JSON currentdata and populate crew data for the On-Duty Supervisor
							$search_val_supv = '2294';
							if($json_data) {
								foreach($json_data['currentdata'] as $currentdata) {
									if ($currentdata['vehicle_id'] == $search_val_supv && strtotime($currentdata['in']) < strtotime("now")) {
										echo("<tr><td>".$currentdata['first_name']." ".$currentdata['last_name']."</td><td>".$currentdata['phone']."</td><td>".date('n/j  H:i', strtotime($currentdata['in']))."</td><td>".date('n/j  H:i', strtotime($currentdata['out']))."</td></tr>");
									} elseif ($currentdata['vehicle_id'] == $search_val_supv && strtotime($currentdata['in']) > strtotime(time()) && strtotime($currentdata['in']) < strtotime('+2 hour',time())) {
										echo("<tr class=\"opacity-50\"><td>".$currentdata['first_name']." ".$currentdata['last_name']."</td><td>".$currentdata['phone']."</td><td>".date('n/j  H:i', strtotime($currentdata['in']))."</td><td>".date('n/j  H:i', strtotime($currentdata['out']))."</td></tr>");
									}
								}
							}
						?>
					</table>
				</div> <!-- end .bucket -->
			</div> <!-- end .col -->
		</div> <!-- end .row -->
	</div> <!-- end .supervisors -->
	<div class="container-fluid">
		<div class="row">
			<div id="duty-crew-wrapper" class="col-9">
				<h2 class="duty-crew-heading">On-Duty Crews</h2>
				<div class="vehicles container">
					<div id="3859" class="vehicle row d-flex align-items-center">
						<div class="vehicle-card col-1">
							<div class="card text-center">
								<span class="fa-solid fa-truck-medical"></span>
							</div> <!-- end .card -->
							<div class="card-body">
								<h5 class="card-title text-center">3859</h5>
							</div> <!-- end .card-body -->
						</div> <!-- end vehicle-card -->
						<div class="crew-table-wrapper col-11">
							<table class="table crew">
								<?php
									// iterate through JSON currentdata and populate crew data for 3859
									$search_val_3859 = '1990';
									if($json_data) {
										foreach($json_data['currentdata'] as $currentdata) {
											if ($currentdata['vehicle_id'] == $search_val_3859 && strtotime($currentdata['in']) < strtotime("now")) {
												echo("<tr><td class=\"level ".$currentdata['level']."\">".$currentdata['level']."</td><td>".$currentdata['first_name']." ".$currentdata['last_name']."</td><td>".$currentdata['phone']."</td><td>".date('n/j  H:i', strtotime($currentdata['in']))."</td><td>".date('n/j  H:i', strtotime($currentdata['out']))."</td></tr>");
											} elseif ($currentdata['vehicle_id'] == $search_val_3859 && strtotime($currentdata['in']) > strtotime(time()) && strtotime($currentdata['in']) < strtotime('+2 hour',time())) {
												echo("<tr class=\"opacity-50\"><td class=\"level ".$currentdata['level']."\">".$currentdata['level']."</td><td>".$currentdata['first_name']." ".$currentdata['last_name']."</td><td>".$currentdata['phone']."</td><td>".date('n/j  H:i', strtotime($currentdata['in']))."</td><td>".date('n/j  H:i', strtotime($currentdata['out']))."</td></tr>");
											}
										}
									}
								?>
							</table>
						</div> <!-- end crew-table-wrapper -->
					</div> <!-- end #3859 -->
					<div id="3869" class="vehicle row d-flex align-items-center" style="background: #efefef;">
						<div class="vehicle-card col-1">
							<div class="card text-center">
								<span class="fa-solid fa-truck-medical"></span>
							</div> <!-- end card -->
							<div class="card-body">
								<h5 class="card-title text-center">3869</h5>
							</div> <!-- end card-body -->
						</div> <!-- end .vehicle-card -->
						<div class="crew-table-wrapper col-11">
							<table class="table crew">
								<?php
									// iterate through JSON currentdata and populate crew data for 3869
									$search_val_3869 = '1991';
									if($json_data) {
										foreach($json_data['currentdata'] as $currentdata) {
											if ($currentdata['vehicle_id'] == $search_val_3869 && strtotime($currentdata['in']) < strtotime("now")) {
												echo("<tr><td class=\"level ".$currentdata['level']."\">".$currentdata['level']."</td><td>".$currentdata['first_name']." ".$currentdata['last_name']."</td><td>".$currentdata['phone']."</td><td>".date('n/j  H:i', strtotime($currentdata['in']))."</td><td>".date('n/j  H:i', strtotime($currentdata['out']))."</td></tr>");
											} elseif ($currentdata['vehicle_id'] == $search_val_3869 && strtotime($currentdata['in']) > strtotime(time()) && strtotime($currentdata['in']) < strtotime('+2 hour',time())) {
												echo("<tr class=\"opacity-50\"><td class=\"level ".$currentdata['level']."\">".$currentdata['level']."</td><td>".$currentdata['first_name']." ".$currentdata['last_name']."</td><td>".$currentdata['phone']."</td><td>".date('n/j  H:i', strtotime($currentdata['in']))."</td><td>".date('n/j  H:i', strtotime($currentdata['out']))."</td></tr>");
											}
										}
									}
								?>
							</table>
						</div> <!-- end .crew-table-wrapper -->
					</div> <!-- end #3869 -->
					<div id="3879" class="vehicle row d-flex align-items-center">
						<div class="vehicle-card col-1">
							<div class="card text-center">
								<span class="fa-solid fa-truck-medical"></span>
							</div> <!-- end .card -->
							<div class="card-body">
								<h5 class="card-title text-center">3879</h5>
							</div> <!-- end .card-body -->
						</div>
						<div class="crew-table-wrapper col-11">
							<table class="table crew">
								<?php
									// iterate through JSON currentdata and populate crew data for 3879
									$search_val_3879 = '1992';
									if($json_data) {
										foreach($json_data['currentdata'] as $currentdata) {
											if ($currentdata['vehicle_id'] == $search_val_3879 && strtotime($currentdata['in']) < strtotime("now")) {
												echo("<tr><td class=\"level ".$currentdata['level']."\">".$currentdata['level']."</td><td>".$currentdata['first_name']." ".$currentdata['last_name']."</td><td>".$currentdata['phone']."</td><td>".date('n/j  H:i', strtotime($currentdata['in']))."</td><td>".date('n/j  H:i', strtotime($currentdata['out']))."</td></tr>");
											} elseif ($currentdata['vehicle_id'] == $search_val_3879 && strtotime($currentdata['in']) > strtotime(time()) && strtotime($currentdata['in']) < strtotime('+2 hour',time())) {
												echo("<tr class=\"opacity-50\"><td class=\"level ".$currentdata['level']."\">".$currentdata['level']."</td><td>".$currentdata['first_name']." ".$currentdata['last_name']."</td><td>".$currentdata['phone']."</td><td>".date('n/j  H:i', strtotime($currentdata['in']))."</td><td>".date('n/j  H:i', strtotime($currentdata['out']))."</td></tr>");
											}
										}
									}
								?>
							</table>
						</div> <!-- end .crew-table-wrapper -->
					</div> <!-- end #3879 -->
					<div id="MED36" class="vehicle row d-flex align-items-center" style="background: #efefef;">
						<div class="vehicle-card col-1">
							<div class="card text-center">
								<span class="fa-solid fa-car-side"></span>
							</div> <!-- end .vehicle-card -->
							<div class="card-body">
								<h5 class="card-title text-center">MED36</h5>
							</div> <!-- end .card-body -->
						</div>
						<div class="crew-table-wrapper col-11">
							<table class="table crew">
								<?php
									// iterate through JSON currentdata and populate crew data for MED36
									$search_val_36 = '1551';
									if($json_data) {
										foreach($json_data['currentdata'] as $currentdata) {
											if ($currentdata['vehicle_id'] == $search_val_36 && strtotime($currentdata['in']) < strtotime("now")) {
												echo("<tr><td class=\" level".$currentdata['level']."\">".$currentdata['level']."</td><td>".$currentdata['first_name']." ".$currentdata['last_name']."</td><td>".$currentdata['phone']."</td><td>".date('n/j  H:i', strtotime($currentdata['in']))."</td><td>".date('n/j  H:i', strtotime($currentdata['out']))."</td></tr>");
											} elseif ($currentdata['vehicle_id'] == $search_val_36 && strtotime($currentdata['in']) > strtotime(time()) && strtotime($currentdata['in']) < strtotime('+2 hour',time())) {
												echo("<tr class=\"opacity-50\"><td class=\"level ".$currentdata['level']."\">".$currentdata['level']."</td><td>".$currentdata['first_name']." ".$currentdata['last_name']."</td><td>".$currentdata['phone']."</td><td>".date('n/j  H:i', strtotime($currentdata['in']))."</td><td>".date('n/j  H:i', strtotime($currentdata['out']))."</td></tr>");
											}
										}
									}
								?>
							</table>
						</div> <!-- end .crew-table-wrapper -->
					</div> <!-- end #MED36 -->
					<div id="MED38" class="vehicle row d-flex align-items-center">
						<div class="vehicle-card col-1">
							<div class="card text-center">
								<span class="fa-solid fa-car-side"></span>
							</div> <!-- end .vehicle-card -->
							<div class="card-body">
								<h5 class="card-title text-center">MED38</h5>
							</div> <!-- end .card-body -->
						</div>
						<div class="crew-table-wrapper col-11">
							<table class="table crew">
								<?php
									// iterate through JSON currentdata and populate crew data for MED38
									$search_val_38 = '1552';
									if($json_data) {
										foreach($json_data['currentdata'] as $currentdata) {
											if ($currentdata['vehicle_id'] == $search_val_38 && strtotime($currentdata['in']) < strtotime("now")) {
												echo("<tr><td class=\" level ".$currentdata['level']."\">".$currentdata['level']."</td><td>".$currentdata['first_name']." ".$currentdata['last_name']."</td><td>".$currentdata['phone']."</td><td>".date('n/j  H:i', strtotime($currentdata['in']))."</td><td>".date('n/j  H:i', strtotime($currentdata['out']))."</td></tr>");
											} elseif ($currentdata['vehicle_id'] == $search_val_38 && strtotime($currentdata['in']) > strtotime(time()) && strtotime($currentdata['in']) < strtotime('+2 hour',time())) {
												echo("<tr class=\"opacity-50\"><td class=\"level ".$currentdata['level']."\">".$currentdata['level']."</td><td>".$currentdata['first_name']." ".$currentdata['last_name']."</td><td>".$currentdata['phone']."</td><td>".date('n/j  H:i', strtotime($currentdata['in']))."</td><td>".date('n/j  H:i', strtotime($currentdata['out']))."</td></tr>");
											}
										}
									}
								?>
							</table>
						</div> <!-- end crew-table-wrapper -->
					</div> <!-- end #MED38 -->
				</div> <!-- end .vehicles .container -->
			</div> <!-- end #duty-crew-wrapper -->
			<div id="sidebar" class="col-3">
				<div id="weather" class="container">
					<a class="weatherwidget-io" href="https://forecast7.com/en/43d09n77d51/pittsford/?unit=us" data-label_1="PITTSFORD" data-label_2="WEATHER" data-font="Roboto" data-icons="Climacons Animated" data-days="3" data-theme="pure" >PITTSFORD WEATHER</a>
					<script>
					!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src='https://weatherwidget.io/js/widget.min.js';fjs.parentNode.insertBefore(js,fjs);}}(document,'script','weatherwidget-io-js');
					</script>
				</div> <!-- end #weather -->
			</div> <!-- end #sidebar -->
		</div> <!-- end .row -->
	</div> <!-- end .container-fluid -->

    <!-- Optional JavaScript; choose one of the two! -->

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

    <!-- Option 2: Separate Popper and Bootstrap JS -->
    <!--
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js" integrity="sha384-7+zCNj/IqJ95wo16oMtfsKbZ9ccEh31eOz1HGyDuCQ6wgnyJNSYdrPa03rtR1zdB" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js" integrity="sha384-QJHtvGhmr9XOIpI6YVutG+2QOK9T+ZnN4kzFN1RtK3zEFEIsxhlmWl5/YESvpZ13" crossorigin="anonymous"></script>
    -->
  </body>
</html>
