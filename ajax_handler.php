<?php
include 'util.php';


// Check if it's an AJAX request
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

    // Check if the expected data is received
    if (isset($_POST['selectedDate'])) {
        $selectedDate = $_POST['selectedDate'];
    } 
    
    if (isset($_POST['timepickerFrom'])) {
        $timeFrom = $_POST['timepickerFrom'];
    } 

    if (isset($_POST['timepickerTo'])) {
        $timeTo = $_POST['timepickerTo'];
    }

    $queryDummyData = "SELECT `Nama Lengkap` as `name`, Koordinat, Jam, Tanggal as `date`
                        FROM t_tracking 
                        WHERE Tanggal ='$selectedDate'
                        AND Jam >= '$timeFrom'
                        AND Jam <= '$timeTo'
                        ORDER BY `name`, `Jam`";

    $result = mysqli_query($db, $queryDummyData);

    // $queryRealData = "SELECT `em_id` as `name`, longlat as Koordinat, `time` as Jam 
    //                     FROM emp_control 
    //                     WHERE atten_date ='$selectedDate'
    //                     AND `time` >= '$timeFrom'
    //                     AND `time` <= '$timeTo'
    //                     ORDER BY `name`, `Jam`";
    // $result = mysqli_query($db, $queryRealData);

    
    [$geojson, $trackingData] = convertToGeoJson($result);


    // Send back a response
    if ($_POST['responseType'] == 'json') {
        echo json_encode($geojson);
    } else if ($_POST['responseType'] == 'single') {
        foreach ($trackingData as $name => $data) {
            echo "<a href='#' class='block px-4 py-2 text-gray-700 hover:bg-gray-100 active:bg-blue-100 cursor-pointer rounded-md' id='" . $name . "_single' data-value='" . $name . "'>" . $name . "<div class='emblem' style='background-color:" . $colors[$name] . "'></div></a>";
        }
    } else if ($_POST['responseType'] == 'checkbox') {
        foreach ($trackingData as $name => $data) {
            echo "<label class='inline-flex items-center mt-3'>";
            echo "<input type='checkbox' class='item form-checkbox h-5 w-5 text-blue-600' data-value='" . htmlspecialchars($name, ENT_QUOTES) . "'><span class='ml-2 text-gray-700'>" . htmlspecialchars($name, ENT_QUOTES) . "<div class='emblem' style='background-color:" . $colors[$name] . "'></div> </span>";
            echo "</label>";
        }
    }

// --------------------------------------------------------------------------------------------------
// -----------------------------------------EXPERIMENT-----------------------------------------------
// --------------------------------------------------------------------------------------------------

    // } else if ($_POST['responseType'] == 'singleTimestamps') {
    //     $selectedName = $_POST['selectedName'];
    //     $count = 0;
    //     $groupName = 'timeSelection';
        
    //     echo "<div class='grid grid-cols-3 gap-4'>"; // Adjust `grid-cols-{$itemsPerRow}` as needed

    //     foreach($trackingData[$selectedName]['lines'] as $line) {
    //         $firstTimestamp = $line['timestamps'][0];
    //         $lastTimestamp = end($line['timestamps']);

    //         // Start a new row if necessary
    //         if ($count > 0 && ($count % 3 === 0)) {
    //             echo "</div>"; // Close the current row
    //             echo "<div class='grid grid-cols-3 gap-4'>"; // Start a new row
    //         }

    //         // echo '<pre>' . print_r($line) . '</pre>';
    //         // echo '<pre>' . $firstTimestamp . ' --> ' . $lastTimestamp . '</pre>';
            
    //         echo "<label for='bordered-radio-" . $count . "' class='flex flex-row items-center ps-4 border border-gray-200 rounded dark:border-gray-700 mx-5 my-1'><div class='block p-6 text-sm font-medium text-gray-900 dark:text-gray-300'>";
    //         echo "<input id='bordered-radio-" . $count . "' type='radio' name='" . $groupName . "' class='mr-4 w-auto h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600'>";
    //         echo "" . $firstTimestamp . " --> " . $lastTimestamp . "</div></label>";
    //         $count += 1;
    //     }
    // }
// --------------------------------------------------------------------------------------------------
// --------------------------------------------------------------------------------------------------
// --------------------------------------------------------------------------------------------------
    


} else {
    // Handle non-AJAX request here
    echo "This script only handles AJAX requests.";
}
?>