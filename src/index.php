<?php
/*
-----------------------------------------------------
PVA's internal Vehicle Status Board
-----------------------------------------------------
*/
// Load configuration variables
$api_host = getenv("API_HOST");
$api_token = getenv("API_TOKEN");

// Confirm configuration variables are set
if(!$api_host || !$api_token) echo "Error: Ensure API_HOST and API_TOKEN environment variables are set";

/** Return an Eastern Time Zone DataTimeZone object.
 * @return DateTimeZone Eastern Time Zone.
 */
function estTimeZone(): DateTimeZone {
    return new DateTimeZone("America/New_York");
}

/** Return a Coordinated Universal Time DataTimeZone object.
 * @return DateTimeZone Coordinated Universal Time.
 */
function utcTimeZone(): DateTimeZone {
    return new DateTimeZone("UTC");
}

/** Converts inputted DateTime to an Eastern Time Zone formatted DateTime.
 * @param DateTime $dateTime
 * @return DateTime Eastern Time Zone formatted DateTime.
 */
function convertToEst(DateTime $dateTime): DateTime {
    return $dateTime->setTimezone(estTimeZone());
}

// Get current time
$currentTime = new DateTime("now", estTimeZone());

// Get current time plus two hours
$currentTimePlusTwoHours = clone $currentTime;
$twoHourInterval = DateInterval::createFromDateString("2 hours");
$currentTimePlusTwoHours->add($twoHourInterval);

// Get yesterday's date
$yesterday = clone $currentTime;
$oneDayInterval = DateInterval::createFromDateString("1 day");
$yesterday->sub($oneDayInterval);

// JSON data feed URL
$url = "https://$api_host/eschedule_api/product/read.php?start_time=" . $yesterday->format("Y-m-d");

// set up curl request
$curl = curl_init($url);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$headers = array(
   "Content-Type: application/json",
   "token: $api_token",
);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

// call curl
$json = curl_exec($curl);

/* verbose errors
if(curl_exec($curl) === false)
{
    echo "Curl error: " . curl_error($ch);
}
else
{
    echo "Operation completed without any errors";
} */

// close curl
curl_close($curl);

// decode json data into usable object
$json_data = json_decode($json);

// ------------LOCAL JSON TESTING ---------------
// Read the JSON file
//$json = file_get_contents("data.json");

// Decode the JSON file
//$json_data = json_decode($json,true);

// Print the JSON data
// print_r($json);

/** Iterates through inputted shift data to create a Crew Table based on matching inputted shift search key and value.
 * @param mixed $json the JSON data provided from the API containing shift data.
 * @param string $searchKey the search key to being used from the shift data.
 * @param string $searchVal the search value to look for the provided search key.
 * @param bool $showLevel show crew's level if true.
 * @param DateTime $currentTime the current time.
 * @param DateTime $currentTimePlusTwoHours the current time plus two hours.
 * @return void
 * @throws Exception DateTime can technically throw an Exception, but with how the DateTime objects are created in this
 * function, it should be unlikely for an Exception to occur.
 */
function createCrewTable(
        mixed    $json,
        string   $searchKey,
        string   $searchVal,
        bool     $showLevel,
        DateTime $currentTime,
        DateTime $currentTimePlusTwoHours
): void {
    if($json) {
        foreach($json->shifts as $shift) {
            $startTime = convertToEst(new DateTime($shift->start_time, utcTimeZone()));
            $endTime = convertToEst(new DateTime($shift->end_time, utcTimeZone()));
            if ($shift->$searchKey == $searchVal && $startTime < $currentTime && $currentTime < $endTime) { ?>
                <tr>
                    <?php if ($showLevel) { ?><td class=<?php echo "\"level $shift->position\"" ?>>
                        <?php echo $shift->position ?>
                    </td><?php } ?>
                    <td><?php echo "$shift->first_name $shift->last_name" ?></td>
                    <td><?php echo $startTime->format("n/j  H:i") ?></td>
                    <td><?php echo $endTime->format("n/j  H:i") ?></td>
                </tr>
            <?php } elseif ($shift->$searchKey == $searchVal
                && $startTime > $currentTime
                && $startTime < $currentTimePlusTwoHours
            ) { ?>
                <tr class="opacity-50">
                    <?php if ($showLevel) { ?><td class=<?php echo "\"level $shift->position\"" ?>>
                        <?php echo $shift->position ?>
                        </td><?php } ?>
                    <td><?php echo "$shift->first_name $shift->last_name" ?></td>
                    <td><?php echo $startTime->format("n/j  H:i") ?></td>
                    <td><?php echo $endTime->format("n/j  H:i") ?></td>
                </tr>
            <?php }
        }
    }
}
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
                        <?php // iterate through JSON shifts and populate crew data for On-Call Chief
                        $search_val_chief = "On-Call";
                        if($json_data) {
                            foreach($json_data->shifts as $shift) {
                                $startTime = convertToEst(new DateTime($shift->start_time, utcTimeZone()));
                                $endTime = convertToEst(new DateTime($shift->end_time, utcTimeZone()));
                                if ($shift->position == $search_val_chief
                                    && $startTime < $currentTime
                                    && $currentTime < $endTime
                                ) { ?>
                                    <tr>
                                        <td><?php echo "$shift->first_name $shift->last_name" ?></td>
                                        <td><?php echo $startTime->format("n/j  H:i") ?></td>
                                        <td><?php echo $endTime->format("n/j  H:i") ?></td>
                                    </tr>
                                <?php } elseif ($shift->position == $search_val_chief
                                    && $startTime > $currentTime
                                    && $startTime < $currentTimePlusTwoHours
                                ) { ?>
                                    <tr class="opacity-50">
                                        <td><?php echo "$shift->first_name $shift->last_name" ?></td>
                                        <td><?php echo $startTime->format("n/j  H:i") ?></td>
                                        <td><?php echo $endTime->format("n/j  H:i") ?></td>
                                    </tr>
                                <?php }
                            }
                        } ?>
					</table>
				</div> <!-- end .bucket -->
			</div> <!-- end .col -->
			<div class="col-6">
				<div class="container-fluid bucket">
					<h2 class="on-duty-heading">Shift Supervisor</h2>
					<table class="table">
						<?php // iterate through JSON shifts and populate crew data for On-Duty Supervisor
                        if($json_data) {
                            foreach($json_data->shifts as $shift) {
                                $startTime = convertToEst(new DateTime($shift->start_time, utcTimeZone()));
                                $endTime = convertToEst(new DateTime($shift->end_time, utcTimeZone()));
                                if (isset($shift->observer)
                                    && $startTime < $currentTime
                                    && $currentTime < $endTime
                                ) { ?>
                                    <tr>
                                        <td><?php echo "$shift->first_name $shift->last_name" ?></td>
                                        <td><?php echo $startTime->format("n/j  H:i") ?></td>
                                        <td><?php echo $endTime->format("n/j  H:i") ?></td>
                                    </tr>
                                <?php } elseif (isset($shift->observer)
                                    && $startTime > $currentTime
                                    && $startTime < $currentTimePlusTwoHours
                                ) { ?>
                                    <tr class="opacity-50">
                                        <td><?php echo "$shift->first_name $shift->last_name" ?></td>
                                        <td><?php echo $startTime->format("n/j  H:i") ?></td>
                                        <td><?php echo $endTime->format("n/j  H:i") ?></td>
                                    </tr>
                                <?php }
                            }
                        } ?>
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
								<h5 class="card-title text-center">Crew 1</h5>
							</div> <!-- end .card-body -->
						</div> <!-- end vehicle-card -->
						<div class="crew-table-wrapper col-11">
							<table class="table crew">
								<?php // iterate through JSON shifts and populate crew data for Crew 1
                                createCrewTable("Crew 1", $json_data, $currentTime, $currentTimePlusTwoHours);
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
								<h5 class="card-title text-center">Crew 2</h5>
							</div> <!-- end card-body -->
						</div> <!-- end .vehicle-card -->
						<div class="crew-table-wrapper col-11">
							<table class="table crew">
                                <?php // iterate through JSON shifts and populate crew data for Crew 2
                                createCrewTable("Crew 2", $json_data, $currentTime, $currentTimePlusTwoHours);
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
								<h5 class="card-title text-center">Crew 3</h5>
							</div> <!-- end .card-body -->
						</div>
						<div class="crew-table-wrapper col-11">
							<table class="table crew">
								<?php // iterate through JSON shifts and populate crew data for Crew 3
                                createCrewTable("Crew 3", $json_data, $currentTime, $currentTimePlusTwoHours);
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
								<h5 class="card-title text-center">Crew 4</h5>
							</div> <!-- end .card-body -->
						</div>
						<div class="crew-table-wrapper col-11">
							<table class="table crew">
								<?php // iterate through JSON shifts and populate crew data for Crew 4
                                createCrewTable("Crew 4", $json_data, $currentTime, $currentTimePlusTwoHours);
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
								<h5 class="card-title text-center">Crew 5</h5>
							</div> <!-- end .card-body -->
						</div>
						<div class="crew-table-wrapper col-11">
							<table class="table crew">
								<?php // iterate through JSON shifts and populate crew data for Crew 5
                                createCrewTable("Crew 5", $json_data, $currentTime, $currentTimePlusTwoHours);
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
					!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://weatherwidget.io/js/widget.min.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","weatherwidget-io-js");
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
