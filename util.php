<?php
$db = mysqli_connect("localhost", "root", "", "test");
$colors = [
    'FREDRICK' => 'blue',
    'PAK IWAN' => 'purple',
    'VICKY' => 'red',
    'JOSHE' => 'yellow',
    'SIS199404001' => 'green',
];

$today = date('Y-m-d');


// function untuk mengubah query sql menjadi data di map
function convertToGeoJson($result) {
    $trackingData = array();


    // ############################
    // ####  [reading data..]  ####
    // ############################
    
    if ($result && $result->num_rows > 0) {
    
        while ($row = $result->fetch_assoc()) {
            // echo '<pre>' . print_r($row, true) . '</pre>';
            
            $name = $row['name'];
            $date = $row['date'];
            // Data dibagi per nama, mempunyai array of lines
            // --------------------------------------------------------------------------------
            // `lines`       adalah hasil dari semua jalur.
            // 
            // `currentLine` adalah jalur yang sedang dibaca dimana waktunya masih continuous,
            //               dalam arti lokasi tracker nyala secara lancar..
            // --------------------------------------------------------------------------------
            if (!isset($trackingData[$name])) {
                $trackingData[$name] = array();
            }
            
            
            if (!isset($trackingData[$name][$date])) {
                
                $trackingData[$name][$date] = array(
                    'lines' => array(),
                    
                    'currentLine' => array(
                        'coordinates' => array(), 
                        'timestamps' => array(),
                    ),
                    
                );
    
            }
    
            // Check lokasi tracker masih sejalur atau tidak
            // - Menghindari 'teleportasi'. 
            if (isset($trackingData[$name][$date]['currentLine']['timestamps'])) {
    
                $timestamps = $trackingData[$name][$date]['currentLine']['timestamps'];
                $lastTimestamp = end($timestamps);
                $oldTimestamp = strtotime($lastTimestamp);
                $currentTimestamp = strtotime($row['Jam']);
                $timeDiff = $currentTimestamp - $oldTimestamp;
    
                // menggunakan 10 detik untuk jarak waktu/kesenjangan waktu
                // eg. 8:30:04 -> 8:30:14 -> 8:30:24 -> 8:30:34 -> 8:30:44 ...  ['timestamps']
                if ($timeDiff > 10) {
    
                    if (!empty($trackingData[$name][$date]['currentLine']['coordinates'])) {
                        $trackingData[$name][$date]['lines'][] = $trackingData[$name][$date]['currentLine'];
                    }
    
                    $trackingData[$name][$date]['currentLine'] = array('coordinates' => array(), 'timestamps' => array());
                }
    
            }
    
            // Parsing the coordinates
            list($longitude, $latitude) = explode(',', $row['Koordinat']);
            $longitude = floatval(trim($longitude));
            $latitude = floatval(trim($latitude));

            // Menambahkan coordinate dan timestamp sekarang 
            $trackingData[$name][$date]['currentLine']['coordinates'][] = array($latitude, $longitude);
            $trackingData[$name][$date]['currentLine']['timestamps'][] = $row['Jam'];

        }
    
        // Append the final line for each user
        foreach ($trackingData as $name => $dates) {
            
            foreach ($dates as $date => $data) {
                if (!empty($data['currentLine']['coordinates'])) {
                    $trackingData[$name][$date]['lines'][] = $trackingData[$name][$date]['currentLine'];
                }
            }
        }
    }
    // code dibawah bisa di run untuk cek data (Uncomment)
    // ----------------------------------------------------------------
    
    // echo '<pre>' . $name . print_r($trackingData, true) . '</pre>';
    
    // ----------------------------------------------------------------
    
    $features = array();
    
    
    // ###############################
    // ####  [converting data..]  ####
    // ###############################
    
    // Data diubah menjadi data object `geoJson`, leaflet.js
    // https://leafletjs.com/examples/geojson/
    
    foreach ($trackingData as $name => $dates) {
        // Iterate through each date
        foreach($dates as $date => $data) {
            // Iterate through each line segment for this user
            foreach ($data['lines'] as $line) {
                
                $lineStringFeature = array(
                    'type' => 'Feature',
                    'properties' => array(
                        'name' => $name,
                        'date' => $date,
                        'timestamps' => $line['timestamps'],
                    ),
                    'geometry' => array(
                        'type' => 'LineString',
                        'coordinates' => $line['coordinates']
                    ),
                );
        
                $features[] = $lineStringFeature;
            }
        }
    }
    
    // Construct the final GeoJSON structure
    $geojson = array(
        'type'      => 'FeatureCollection',
        'features'  => $features 
    );

    return [$geojson, $trackingData];
    
    // You can then use $geojson as needed, like converting it to a JSON string
    // echo json_encode($geojson);

}
?>