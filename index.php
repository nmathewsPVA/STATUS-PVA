<?php
// PVA's internal Vehicle Status Board

// $url = "https://www.monroecounty.gov/etc/ambulance/json.php?u=PITE&p=s24.PITE.42";

// $curl = curl_init($url);
// curl_setopt($curl, CURLOPT_URL, $url);
// curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

// $headers = array(
//    "Accept: application/json",
//    "Access-Control-Allow-Origin: *",
// );
// curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
//for debug only!
// curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
// curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

// $resp = curl_exec($curl);
// curl_close($curl);
// $json = json_decode($resp, true);

// Read the JSON file
$json = file_get_contents('data.json');

// Decode the JSON file
$json_data = json_decode($json,true);

// Print the JSON data
// print_r($json_data['currentdata']);

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
		h2.duty-crew-heading, h2.on-call-heading, h2.on-duty-heading {
			text-align: center;
		}
	</style>
    <title>PVA Status Board</title>
  </head>
  <body>
	<h1 class="text-center">PVA Status Board</h1>
	<div class="supervisors container">
		<div class="row">
			<div class="col">
				<h2 class="on-call-heading">On-Call Chief</h2>
				<table class="table">
					<?php
						// iterate through JSON currentdata and populate crew data for On-Call Chief
						$search_val_chief = '2181';
						foreach($json_data as $elem) {
							foreach($elem['currentdata'] as $key => $val) {
								if ($key == 'vehicle_id' && val == $search_val_chief) {
									echo("<tr><td>".$elem['first_name']." ".$elem['last_name']."</td><td>".$elem['phone']."</td><td>".$elem['in']."</td><td>".$elem['out']."</td></tr>");
								} else {
									echo("<tr><td><i>none listed</i></td></tr>");
								}
							}
						}
					?>
				</table>
			</div>
			<div class="col">
				<h2 class="on-duty-heading">Shift Supervisor</h2>
				<table class="table">
					<?php
						// iterate through JSON currentdata and populate crew data for MED36
						$search_val_supv = '1450';
					?>
					<tr>
						<td>Jenny Carver - 3M58</td>
						<td>585-385-2401</td>
						<td>0600</td>
						<td>1800</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
	<h2 class="duty-crew-heading">On-Duty Crews</h2>
	<div class="vehicles container">
		<div id="3859" class="vehicle row">
			<div class="vehicle-card col-1 position-relative">
				<div class="position-absolute top-50 start-50 translate-middle">
					3859
				</div>
			</div>
			<div class="crew-table-wrapper col-11">
				<table class="table crew">
					<?php
						// iterate through JSON currentdata and populate crew data for 3859
						$search_val_3859 = '1990';
					?>
					<tr>
						<td class="text-center">ALS</td>
						<td>Neil Mathews</td>
						<td>585-747-3255</td>
					</tr>
					<tr>
						<td class="BLS text-center">BLS</td>
						<td>Jenny Carver</td>
						<td>585-385.2401</td>
					</tr>
				</table>
			</div>
		</div>
		<div id="3869" class="vehicle row">
			<div class="vehicle-card col-1 position-relative">
				<div class="position-absolute top-50 start-50 translate-middle">
					3869
				</div>
			</div>
			<div class="crew-table-wrapper col-11">
				<table class="table crew">
					<?php
						// iterate through JSON currentdata and populate crew data for 3869
						$search_val_3869 = '1991';
					?>
					<tr>
						<td>ALS</td>
						<td>Neil Mathews</td>
						<td>585-747-3255</td>
					</tr>
					<tr>
						<td>BLS</td>
						<td>Jenny Carver</td>
						<td>585-385.2401</td>
					</tr>
				</table>
			</div>
		</div>
		<div id="3879" class="vehicle row">
			<div class="vehicle-card col-1 position-relative">
				<div class="position-absolute top-50 start-50 translate-middle">
					3879
				</div>
			</div>
			<div class="crew-table-wrapper col-11">
				<table class="table crew">
					<?php
						// iterate through JSON currentdata and populate crew data for 3879
						$search_val_3879 = '1992';
					?>
					<tr>
						<td>ALS</td>
						<td>Neil Mathews</td>
						<td>585-747-3255</td>
					</tr>
					<tr>
						<td>BLS</td>
						<td>Jenny Carver</td>
						<td>585-385.2401</td>
					</tr>
				</table>
			</div>
		</div>
		<div id="MED36" class="vehicle row">
			<div class="vehicle-card col-1 position-relative">
				<div class="position-absolute top-50 start-50 translate-middle">
					MED36
				</div>
			</div>
			<div class="crew-table-wrapper col-11">
				<table class="table crew">
					<tr>
					<?php
						// iterate through JSON currentdata and populate crew data for MED36
						$search_val_36 = '1551';
					?>
					<td>ALS</td>
						<td>Neil Mathews</td>
						<td>585-747-3255</td>
					</tr>
				</table>
			</div>
		</div>
		<div id="MED38" class="vehicle row">
			<div class="vehicle-card col-1 position-relative">
				<div class="position-absolute top-50 start-50 translate-middle">
					MED38
				</div>
			</div>
			<div class="crew-table-wrapper col-11">
				<table class="table crew">
					<?php
						// iterate through JSON currentdata and populate crew data for MED38
						$search_val_38 = '1552';
					?>
					<tr>
						<td>ALS</td>
						<td>Neil Mathews</td>
						<td>585-747-3255</td>
					</tr>
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
