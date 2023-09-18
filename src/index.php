<?php
/*
-----------------------------------------------------
PVA's internal Vehicle Status Board
-----------------------------------------------------
*/
// Define status board constants
$operations = "Operations ";
$on_call_position = "On-Call";
$supervisor_position = "On-Duty";
$shift_types_icon = ["Crew" => "fa-truck-medical", "Medic" => "fa-car-side"];

// Load configuration variables
$api_host = getenv("API_HOST");
$api_token = getenv("API_TOKEN");

// Confirm configuration variables are set
if (!$api_host || !$api_token) echo "Error: Ensure API_HOST and API_TOKEN environment variables are set";

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

/** Creates the regex pattern string for inputted patterns.
 * @param array $patterns array of patterns to search for.
 * @return string regex pattern string looking for one of the provided patterns at the beginning of the string.
 */
function createRegex(array $patterns): string {
    $regex = "/";
    foreach ($patterns as $i=>$pattern) {
        if ($i != 0) {
            $regex .= "|";
        }
        $regex .= "(?:^$pattern.*$)";
    }
    $regex .= "/";
    return $regex;
}

/** Adds inputted shift to inputted status.
 * @param array $status reference to status array containing all parsed shifts.
 * @param mixed $shift shift data.
 * @param string $operations operations shift name.
 * @param string $on_call_position on-call position name.
 * @param string $supervisor_position supervisor position name.
 * @return void
 */
function addShiftToStatus(
    array &$status,
    mixed $shift,
    string $operations,
    string $on_call_position,
    string $supervisor_position
): void {
    if ($shift->shift_name == $operations) {
        switch ($shift->position) {
            case $on_call_position:
                $status[$on_call_position][] = $shift;
                break;
            case $supervisor_position:
                $status[$supervisor_position][] = $shift;
                break;
        }
    } else {
        $status[$shift->shift_name][] = $shift;
    }
}

/** Get shift icon of inputted shift.
 * @param array $shift_icons array of shift icons.
 * @param string $shift shift.
 * @return string shift icon of inputted shift or empty string if no icon exists for inputted shift.
 */
function getShiftIcon(array $shift_icons, string $shift): string {
    foreach ($shift_icons as $shift_name=>$shift_icon) {
        if (str_contains($shift, $shift_name)) {
            return $shift_icon;
        }
    }
    return "";
}


/** Outputs inputted shifts in a standardized Crew Table.
 * @param mixed $shifts shift data.
 * @param bool $showLevel show crew's level if true.
 * @return void
 */
function createCrewTable(mixed $shifts, bool $showLevel): void {
    foreach($shifts as $shift) { ?>
        <tr<?php if (property_exists($shift, "future") && $shift->future) { ?> class="opacity-50" <?php }?>>
            <?php if ($showLevel) { ?><td class="<?php echo "level $shift->position" ?>">
                <?php echo $shift->position ?>
                </td><?php } ?>
            <td><?php echo "$shift->first_name $shift->last_name" ?></td>
            <td><?php echo $shift->start_time->format("n/j  H:i") ?></td>
            <td><?php echo $shift->end_time->format("n/j  H:i") ?></td>
        </tr>
    <?php }
}

/** Displays a Crew Table based on inputted shift data.
 * @param string $shift_name shift name.
 * @param mixed $shifts shift data.
 * @param int $row_number row number for this Crew Table.
 * @param string $vehicle_icon vehicle icon for the shift.
 * @return void
 * @throws Exception DateTime can technically throw an Exception, but with how the DateTime objects are created in this
 * app, it should be unlikely for an Exception to occur.
 */
function displayCrewTable(string $shift_name, mixed $shifts, int $row_number, string $vehicle_icon): void { ?>
    <div id="<?php echo str_replace(" ", "", $shift_name) ?>"
         class="vehicle row d-flex align-items-center"<?php if ($row_number % 2 != 0) {
        echo " style=\"background: #efefef;\"";
    } ?>>
        <div class="vehicle-card col-1">
            <div class="card text-center">
                <span class="fa-solid <?php echo $vehicle_icon ?>"></span>
            </div> <!-- end .card -->
            <div class="card-body">
                <h5 class="card-title text-center"><?php echo $shift_name ?></h5>
            </div> <!-- end .card-body -->
        </div> <!-- end .vehicle-card -->
        <div class="crew-table-wrapper col-11">
            <table class="table crew">
                <?php // Populate crew data for $shift_name
                createCrewTable($shifts, true); ?>
            </table>
        </div> <!-- end .crew-table-wrapper -->
    </div> <!-- end #$shift_name -->
<?php }

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

// Start timer
$startTimer = new DateTime("now", estTimeZone());

// ------------LOCAL JSON TESTING ---------------
// Read the JSON file
//$json = file_get_contents("data.json");

// Decode the JSON file
//$json_data = json_decode($json,true);

// Print the JSON data
// print_r($json);

// Define the status array that contains the parsed, current shifts.
$status = [];

// Create regex pattern used to search for shifts to show on status board.
$shift_search_terms = [$operations];
foreach ($shift_types_icon as $shift_type=>$icon) {
    $shift_search_terms[] = $shift_type;
}
$shift_names = createRegex($shift_search_terms);

// Parse shift data to get current (and future shifts starting in the next two hours).
if ($json_data) {
    foreach ($json_data->shifts as $shift) {
        $shift->start_time = convertToEst(new DateTime($shift->start_time, utcTimeZone()));
        $shift->end_time = convertToEst(new DateTime($shift->end_time, utcTimeZone()));
        if ($shift->start_time < $currentTime && $currentTime < $shift->end_time && preg_match($shift_names, $shift->shift_name)) {
            addShiftToStatus($status, $shift, $operations, $on_call_position, $supervisor_position);
        } elseif ($shift->start_time > $currentTime
            && $shift->start_time < $currentTimePlusTwoHours
            && preg_match($shift_names, $shift->shift_name)
        ) {
            $shift->future = true;
            addShiftToStatus($status, $shift, $operations, $on_call_position, $supervisor_position);
        }
    }
} ?>
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
                        createCrewTable(
                            $json_data,
                            "position",
                            "On-Call",
                            false,
                            $currentTime,
                            $currentTimePlusTwoHours
                        ); ?>
					</table>
				</div> <!-- end .bucket -->
			</div> <!-- end .col -->
			<div class="col-6">
				<div class="container-fluid bucket">
					<h2 class="on-duty-heading">Shift Supervisor</h2>
					<table class="table">
						<?php // Populate crew data for On-Duty Supervisor
                        if (array_key_exists($supervisor_position, $status)) {
                            createCrewTable($status[$supervisor_position], false);
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
					<div id="Crew1" class="vehicle row d-flex align-items-center">
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
                                createCrewTable(
                                    $json_data,
                                    "shift_name",
                                    "Crew 1",
                                    true,
                                    $currentTime,
                                    $currentTimePlusTwoHours
                                ); ?>
							</table>
						</div> <!-- end crew-table-wrapper -->
					</div> <!-- end #Crew1 -->
					<div id="Crew2" class="vehicle row d-flex align-items-center" style="background: #efefef;">
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
                                createCrewTable(
                                    $json_data,
                                    "shift_name",
                                    "Crew 2",
                                    true,
                                    $currentTime,
                                    $currentTimePlusTwoHours
                                ); ?>
							</table>
						</div> <!-- end .crew-table-wrapper -->
					</div> <!-- end #Crew2 -->
					<div id="Crew3" class="vehicle row d-flex align-items-center">
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
                                createCrewTable(
                                    $json_data,
                                    "shift_name",
                                    "Crew 3",
                                    true,
                                    $currentTime,
                                    $currentTimePlusTwoHours
                                ); ?>
							</table>
						</div> <!-- end .crew-table-wrapper -->
					</div> <!-- end #Crew3 -->
                    <div id="Crew4" class="vehicle row d-flex align-items-center" style="background: #efefef;">
                        <div class="vehicle-card col-1">
                            <div class="card text-center">
                                <span class="fa-solid fa-truck-medical"></span>
                            </div> <!-- end .card -->
                            <div class="card-body">
                                <h5 class="card-title text-center">Crew 4</h5>
                            </div> <!-- end .card-body -->
                        </div>
                        <div class="crew-table-wrapper col-11">
                            <table class="table crew">
                                <?php // iterate through JSON shifts and populate crew data for Crew 4
                                createCrewTable(
                                    $json_data,
                                    "shift_name",
                                    "Crew 4",
                                    true,
                                    $currentTime,
                                    $currentTimePlusTwoHours
                                ); ?>
                            </table>
                        </div> <!-- end .crew-table-wrapper -->
                    </div> <!-- end #Crew4 -->
                    <div id="Crew5" class="vehicle row d-flex align-items-center">
                        <div class="vehicle-card col-1">
                            <div class="card text-center">
                                <span class="fa-solid fa-truck-medical"></span>
                            </div> <!-- end .card -->
                            <div class="card-body">
                                <h5 class="card-title text-center">Crew 5</h5>
                            </div> <!-- end .card-body -->
                        </div>
                        <div class="crew-table-wrapper col-11">
                            <table class="table crew">
                                <?php // iterate through JSON shifts and populate crew data for Crew 5
                                createCrewTable(
                                    $json_data,
                                    "shift_name",
                                    "Crew 5",
                                    true,
                                    $currentTime,
                                    $currentTimePlusTwoHours
                                ); ?>
                            </table>
                        </div> <!-- end .crew-table-wrapper -->
                    </div> <!-- end #Crew5 -->
					<div id="Medic36" class="vehicle row d-flex align-items-center" style="background: #efefef;">
						<div class="vehicle-card col-1">
							<div class="card text-center">
								<span class="fa-solid fa-car-side"></span>
							</div> <!-- end .vehicle-card -->
							<div class="card-body">
								<h5 class="card-title text-center">Medic 36</h5>
							</div> <!-- end .card-body -->
						</div>
						<div class="crew-table-wrapper col-11">
							<table class="table crew">
								<?php // iterate through JSON shifts and populate crew data for Medic 36
                                createCrewTable(
                                    $json_data,
                                    "shift_name",
                                    "Medic 36",
                                    true,
                                    $currentTime,
                                    $currentTimePlusTwoHours
                                ); ?>
							</table>
						</div> <!-- end .crew-table-wrapper -->
					</div> <!-- end #Medic36 -->
					<div id="Medic38" class="vehicle row d-flex align-items-center">
						<div class="vehicle-card col-1">
							<div class="card text-center">
								<span class="fa-solid fa-car-side"></span>
							</div> <!-- end .vehicle-card -->
							<div class="card-body">
								<h5 class="card-title text-center">Medic 38</h5>
							</div> <!-- end .card-body -->
						</div>
						<div class="crew-table-wrapper col-11">
							<table class="table crew">
								<?php // iterate through JSON shifts and populate crew data for Medic 38
                                createCrewTable(
                                    $json_data,
                                    "shift_name",
                                    "Medic 38",
                                    true,
                                    $currentTime,
                                    $currentTimePlusTwoHours
                                ); ?>
							</table>
						</div> <!-- end crew-table-wrapper -->
					</div> <!-- end #Medic38 -->
				</div> <!-- end .vehicles .container -->
			</div> <!-- end #duty-crew-wrapper -->
            <?php // End timer
            $endTimer = new DateTime("now", estTimeZone()); ?>
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
<?php // Output timer duration
$interval = $startTimer->diff($endTimer);
echo $interval->format("%s.%Fs"); ?>
