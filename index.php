<?php
/*
-----------------------------------------------------
PVA's internal Vehicle Status Board
-----------------------------------------------------
*/
// JSON data feed URL
$url = "https://www.monroecounty.gov/etc/ambulance/json.php?u=PITE&p=s24.PITE.42";

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

// verbose errors
if(curl_exec($curl) === false)
{
    echo 'Curl error: ' . curl_error($ch);
}
else
{
    echo 'Operation completed without any errors';
}

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
print_r($json_data);

?>
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
	<style>
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
		}
		h2.duty-crew-heading, h2.on-call-heading, h2.on-duty-heading {
			text-align: center;
		}
		.card {
			border: none;
		}
		.vehicle-card > .card > span.fa-solid {
			font-size: 2rem;
		}
	</style>
    <title>PVA Status Board</title>
	<link href="assets/fontawesome-free-6.0.0-web/css/all.css" rel="stylesheet" >
  </head>
  <body>
	<h1 class="text-center">PVA Status Board</h1>
	<div class="supervisors container">
		<div class="row">
			<div class="col">
					<div class="container-fluid bucket">
					<h2 class="on-call-heading">On-Call Chief</h2>
					<table class="table">
						<?php
							// iterate through JSON currentdata and populate crew data for On-Call Chief
							$search_val_chief = '2181';
							if($json_data) {
								foreach($json_data['currentdata'] as $currentdata) {
									if ($currentdata['vehicle_id'] == $search_val_chief) {
										echo("<tr><td>".$currentdata['first_name']." ".$currentdata['last_name']."</td><td>".$currentdata['phone']."</td><td>".date('n/j  H:i', strtotime($currentdata['in']))."</td><td>".date('n/j  H:i', strtotime($currentdata['out']))."</td></tr>");
									}
								}
							} else {
								echo("<tr><td><i>none statused</i></td></tr>");
							}
						?>
					</table>
				</div>
			</div>
			<div class="col">
				<div class="container-fluid bucket">
					<h2 class="on-duty-heading">Shift Supervisor</h2>
					<table class="table">
						<?php
							// iterate through JSON currentdata and populate crew data for MED36
							$search_val_supv = '1450';
							if($json_data) {
								foreach($json_data['currentdata'] as $currentdata) {
									if ($currentdata['vehicle_id'] == $search_val_supv) {
										echo("<tr><td>".$currentdata['first_name']." ".$currentdata['last_name']."</td><td>".$currentdata['phone']."</td><td>".date('n/j  H:i', strtotime($currentdata['in']))."</td><td>".date('n/j  H:i', strtotime($currentdata['out']))."</td></tr>");
									}
								}
							} else {
								echo("<tr><td><i>none statused</i></td></tr>");
							}
						?>
					</table>
				</div>
			</div>
		</div>
	</div>
	<h2 class="duty-crew-heading">On-Duty Crews</h2>
	<div class="vehicles container">
		<div id="3859" class="vehicle row">
			<div class="vehicle-card col-1">
				<div class="card text-center">
					<span class="fa-solid fa-truck-medical"></span>
				</div>
				<div class="card-body">
					<h5 class="card-title text-center">3859</h5>
				</div>
			</div>
			<div class="crew-table-wrapper col-11">
				<table class="table crew">
					<?php
						// iterate through JSON currentdata and populate crew data for 3859
						$search_val_3859 = '1990';
						if($json_data) {
							foreach($json_data['currentdata'] as $currentdata) {
								if ($currentdata['vehicle_id'] == $search_val_3859) {
									echo("<tr><td class=\"".$currentdata['level']."\">".$currentdata['level']."</td><td>".$currentdata['first_name']." ".$currentdata['last_name']."</td><td>".$currentdata['phone']."</td><td>".date('n/j  H:i', strtotime($currentdata['in']))."</td><td>".date('n/j  H:i', strtotime($currentdata['out']))."</td><td>".$currentdata['notes']."</tr>");
								}
							}
						} else {
							echo("<tr><td><i>no crew statused</i></td></tr>");
						}
					?>
				</table>
			</div>
		</div>
		<div id="3869" class="vehicle row">
			<div class="vehicle-card col-1">
				<div class="card text-center">
					<span class="fa-solid fa-truck-medical"></span>
				</div>
				<div class="card-body">
					<h5 class="card-title text-center">3869</h5>
				</div>
			</div>
			<div class="crew-table-wrapper col-11">
				<table class="table crew">
					<?php
						// iterate through JSON currentdata and populate crew data for 3869
						$search_val_3869 = '1991';
						if($json_data) {
							foreach($json_data['currentdata'] as $currentdata) {
								if ($currentdata['vehicle_id'] == $search_val_3869) {
									echo("<tr><td class=\"".$currentdata['level']."\">".$currentdata['level']."</td><td>".$currentdata['first_name']." ".$currentdata['last_name']."</td><td>".$currentdata['phone']."</td><td>".date('n/j  H:i', strtotime($currentdata['in']))."</td><td>".date('n/j  H:i', strtotime($currentdata['out']))."</td></tr>");
								}
							}
						} else {
							echo("<tr><td><i>no crew statused</i></td></tr>");
						}
					?>
				</table>
			</div>
		</div>
		<div id="3879" class="vehicle row">
			<div class="vehicle-card col-1">
				<div class="card text-center">
					<span class="fa-solid fa-truck-medical"></span>
				</div>
				<div class="card-body">
					<h5 class="card-title text-center">3879</h5>
				</div>
			</div>
			<div class="crew-table-wrapper col-11">
				<table class="table crew">
					<?php
						// iterate through JSON currentdata and populate crew data for 3879
						$search_val_3879 = '1992';
						if($json_data) {
							foreach($json_data['currentdata'] as $currentdata) {
								if ($currentdata['vehicle_id'] == $search_val_3879) {
									echo("<tr><td class=\"".$currentdata['level']."\">".$currentdata['level']."</td><td>".$currentdata['first_name']." ".$currentdata['last_name']."</td><td>".$currentdata['phone']."</td><td>".date('n/j  H:i', strtotime($currentdata['in']))."</td><td>".date('n/j  H:i', strtotime($currentdata['out']))."</td></tr>");
								}
							}
						} else {
							echo("<tr><td><i>no crew statused</i></td></tr>");
						}
					?>
				</table>
			</div>
		</div>
		<div id="MED36" class="vehicle row">
			<div class="vehicle-card col-1">
				<div class="card text-center">
					<span class="fa-solid fa-car-side"></span>
				</div>
				<div class="card-body">
					<h5 class="card-title text-center">MED36</h5>
				</div>
			</div>
			<div class="crew-table-wrapper col-11">
				<table class="table crew">
					<?php
						// iterate through JSON currentdata and populate crew data for MED36
						$search_val_36 = '1551';
						if($json_data) {
							foreach($json_data['currentdata'] as $currentdata) {
								if ($currentdata['vehicle_id'] == $search_val_36) {
									echo("<tr><td class=\"".$currentdata['level']."\">".$currentdata['level']."</td><td>".$currentdata['first_name']." ".$currentdata['last_name']."</td><td>".$currentdata['phone']."</td><td>".date('n/j  H:i', strtotime($currentdata['in']))."</td><td>".date('n/j  H:i', strtotime($currentdata['out']))."</td></tr>");
								}
							}
						} else {
							echo("<tr><td><i>no crew statused</i></td></tr>");
						}
					?>
				</table>
			</div>
		</div>
		<div id="MED38" class="vehicle row">
			<div class="vehicle-card col-1">
				<div class="card text-center">
					<span class="fa-solid fa-car-side"></span>
				</div>
				<div class="card-body">
					<h5 class="card-title text-center">MED38</h5>
				</div>
			</div>
			<div class="crew-table-wrapper col-11">
				<table class="table crew">
					<?php
						// iterate through JSON currentdata and populate crew data for MED38
						$search_val_38 = '1552';
						if($json_data) {
							foreach($json_data['currentdata'] as $currentdata) {
								if ($currentdata['vehicle_id'] == $search_val_38) {
									echo("<tr><td class=\"".$currentdata['level']."\">".$currentdata['level']."</td><td>".$currentdata['first_name']." ".$currentdata['last_name']."</td><td>".$currentdata['phone']."</td><td>".date('n/j  H:i', strtotime($currentdata['in']))."</td><td>".date('n/j  H:i', strtotime($currentdata['out']))."</td></tr>");
								}
							}
						} else {
							echo("<tr><td><i>no crew statused</i></td></tr>");
						}
					?>
				</table>
			</div>
		</div>
	</div>

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
