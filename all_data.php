<?php
include 'util.php';

$queryDummyData = "SELECT `Nama Lengkap` as `name`, Koordinat, Jam, Tanggal as `date`
                    FROM t_tracking 
                    ORDER BY `name`, `date`, Jam";

$result = mysqli_query($db, $queryDummyData);

$queryRealData = "SELECT `em_id` as `name`, longlat as Koordinat, `time` as Jam, atten_date as `date` 
                    FROM emp_control
                    ORDER BY `name`, `date`, `time`";

$result = mysqli_query($db, $queryRealData);


[$geojson, $trackingData] = convertToGeoJson($result);

echo json_encode($geojson);
// echo '<pre>' . print_r($geojson, true) . '</pre>';
?>