<?php 
// $trackingData = array();

// // Loop through the rows and add each coordinate to the array
// if ($result && $result->num_rows > 0) {
//     while ($row = $result->fetch_assoc()) {
//         $name = $row['name'];
//         if (!isset($trackingData[$name])) {
//             $trackingData[$name] = array(
//                 'coordinates' => array(),
//                 'timestamps' => array(),
//             );

//         }

//         // Assuming the Koordinat is a string with comma-separated latitude and longitude
//         list($longitude, $latitude) = explode(',', $row['Koordinat']);
        
//         // Trim whitespace and convert to float
//         $longitude = floatval(trim($longitude));
//         $latitude = floatval(trim($latitude));
//         // Add the coordinate pair to the allCoordinates array
//         $trackingData[$name]['coordinates'][] = array($latitude, $longitude);
//         $trackingData[$name]['timestamps'][] = $row['Jam'];
       
//     }
    
// }

// $features = array();
// Construct every single one LineString feature with all the coordinates
// foreach ($trackingData as $name => $data) {

//     $lineStringFeature = array(
//         'type' => 'Feature',
//         'properties' => array(
//             'name' => $name,
//             'timestamps' => $data['timestamps']
//         ), // empty object for properties
//         'geometry' => array(
//             'type' => 'LineString',
//             'coordinates' => $data['coordinates']
//             )
//         );
//     $features[] = $lineStringFeature;
// }
        
// // Construct the final GeoJSON structure
// $geojson = array(
//     'type'      => 'FeatureCollection',
//     'features'  => $features 
// );

?>