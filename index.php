<?php
include 'util.php';

$queryDummyData = "SELECT `Nama Lengkap` as `name`, Koordinat, Jam, Tanggal as `date`
                    FROM t_tracking 
                    WHERE Tanggal ='2024-01-12' 
                    ORDER BY `name`, Jam";

$result = mysqli_query($db, $queryDummyData);

// $queryRealData = "SELECT `em_id` as `name`, longlat as Koordinat, `time` as Jam 
//                     FROM emp_control
//                     WHERE atten_date ='2023-10-30' 
//                     ORDER BY `name`, `time`";
// 
// $result = mysqli_query($db, $queryRealData);


[$geojson, $trackingData] = convertToGeoJson($result);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Mengunakan -->
    <!-- 1. Tailwind CSS-->
    <!-- 2. jQuery -->
    <!-- 3. Leaflet.js -->

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Tracking</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <link rel="stylesheet" href="./src/output.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="/resources/demos/style.css">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">

    <style>
        body {
            margin: 0;
        }
        #map {
            height: 50vh;
            width: 80vw;
            margin: auto;
            border: solid black;
        }
        .selection {
            height:330px;
            background-color: salmon;
            margin: 30px;
            padding: 0 10%;
            display: flex;
            flex-direction: row;
            
        }
        #dataContainerCheckbox {
            display:flex;
            flex-direction: column;
        }
        .emblem {
            width: 30px;
            height: 5px;
            background-color: blue;
        }
    </style>
</head>
<body>
<div class="selection justify-between">


    <!-- Dropdown single menu -->
    <div class="m-5">
        <div class="flex items-center">
            <div class="relative group">
                <button id="dropdown-button" class="inline-flex justify-center w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-100 focus:ring-blue-500">
                    <span class="mr-2">Pilih Karyawan <i style="font-size: 0.5rem; font-weight:bold">[satuaan]</i></span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 ml-2 -mr-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M6.293 9.293a1 1 0 011.414 0L10 11.586l2.293-2.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
                <div id="dropdown-menu" class="hidden absolute right-0 mt-2 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 p-1 space-y-1">
                    <!-- Search input -->
                    <input id="search-input" class="block w-full px-4 py-2 text-gray-800 border rounded-md  border-gray-300 focus:outline-none" type="text" placeholder="Search items" autocomplete="off">
                   
                    <!-- data dibawah dynamic `#dataContainerSingle` -->
                    <!-- =================================== -->
                    <div id="dataContainerSingle">
                        <?php foreach ($trackingData as $name => $data) { ?>
                            <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 active:bg-blue-100 cursor-pointer rounded-md" id="<?= $name ?>_single" data-value="<?= $name ?>"><?= $name ?> <div class="emblem" style="background-color: <?= $colors[$name]?>"></div></a>
                        <?php } ?>
                    </div>
                    <!-- =================================== -->
                </div>
            </div>
        </div>
    </div>


    <!-- DATE -->
    <div class="m-7" id="dateSection">
        <b>Date: </b><input type="text" id="datepicker" placeholder="Date">
        <form class="mt-14">
        <b>From: </b><input type="text" class="timepicker" name="timeFrom" placeholder="From"/>
        <span></span>
        </form>
        <form class="mt-7">
        <b>To: </b><input type="text" class="timepicker" name="timeTo" placeholder="To"/>
        <span></span>
        </form>
    </div>


    <!-- Checkbox -->
    <div class="p-6 bg-white m-6 shadow-md flex items-center space-x-4">
        <div class="flex flex-col">
        <div class="mb-5" style="font-size: 0.7rem;"><b>[alternative] </b> <i>bisa tampil lebih dari satu orang</i></div>
            <label class="inline-flex items-center mb-6">
                <input type="checkbox" id="selectAll" class="form-checkbox h-5 w-5 text-blue-600"><span class="ml-2 text-gray-700">Select All </span>
            </label>
            <!-- data dibawah dynamic `#dataContainerCheckbox` -->
            <!-- =================================== -->
            <div id="dataContainerCheckbox">
                <?php foreach ($trackingData as $name => $data) { ?>
                    <label class="inline-flex items-center mt-3">
                        <input type="checkbox" class="item form-checkbox h-5 w-5 text-blue-600" data-value="<?= $name ?>"><span class="ml-2 text-gray-700"><?= $name ?><div class="emblem" style="background-color: <?= $colors[$name]?>"></div></span>
                    </label>
                <?php } ?>
            </div>
            <!-- =================================== -->
        </div>
    </div>

</div>
<!-- Timestamps -->
    <!-- data dibawah dynamic `#singlesTimestamp` -->
    <!-- =================================== -->
    <div id="singlesTimestamp" class='max-w-full mx-auto'>
    
    </div>
    <!-- =================================== -->
<div id="map"></div>



</body>
</html>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://code.jquery.com/jquery-3.6.0.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>


<script>
// JavaScript to toggle the dropdown
const dropdownButton = document.getElementById('dropdown-button');
const dropdownMenu = document.getElementById('dropdown-menu');
const searchInput = document.getElementById('search-input');
let isOpen = false; // Set to true to open the dropdown by default

// Function to toggle the dropdown state
function toggleDropdown() {
    isOpen = !isOpen;
    dropdownMenu.classList.toggle('hidden', !isOpen);
}

// Set initial state
toggleDropdown();

dropdownButton.addEventListener('click', () => {
    toggleDropdown();
});

// Add event listener to filter items based on input
searchInput.addEventListener('input', () => {
    const searchTerm = searchInput.value.toLowerCase();
    const items = dropdownMenu.querySelectorAll('a');
    
    items.forEach((item) => {
        const text = item.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});
</script>

<script>
    // general UI
    // 1. pilih satuaan
    // 2. centang checkboxes

    // --------------------------------------------------------------------------------
    // `geoJsonData` adalah data yang telah dibaca.
    // 
    // `toBeDisplayed` adalah sebagai filter untuk queries SQL.
    // 
    // `lastMarkerCoords` adalah data coordinate terakhir.
    // --------------------------------------------------------------------------------
    var geoJsonData = <?= json_encode($geojson)?>;
    var toBeDisplayed;
    var lastMarkerCoords;

    $(document).ready(function() {
        
        // 1. Pilih satuaan logics 
        // Listener for click event on anchor tags with ids
        $('#dataContainerSingle').on('click', 'a', function(e) {
            e.preventDefault();

            let name = $(this).attr('data-value');
            
            // hanya centang orang yang di select satuaan
            $('input.item:checked').prop('checked', false);
            $('#selectAll').prop('checked', false);
            $('#dataContainerCheckbox input[data-value="'+ name + '"]').prop('checked', true);
            filterToBeDisplayed();
            // map bergerak ke posisi terakhir orang yang di select
            if (geoJsonData && lastMarkerCoords) {
                // map.flyTo(lastMarkerCoords, 18);
                map.panTo(lastMarkerCoords);
            }


            // menambahkan UI timestamps --- EXPERIMENT
            // $.ajax({
            //     url: 'ajax_handler.php',
            //     type: 'POST',
            //     data: {
            //         selectedDate: dateData,
            //         timepickerFrom: timeFrom,
            //         timepickerTo: timeTo,
            //         responseType: 'singleTimestamps',
            //         selectedName: name
            //     },
            //     success: function(response) {
            //         $('#singlesTimestamp').html(response);
            //     },
            //     error: function(xhr, status, error) {
            //         console.error("AJAX Error:", error);
            //     }
            // });
        });

        // 2. Checkboxes
        // Ketika 'Select All' checkbox di click
        $('#selectAll').click(function() {
            // Check atau uncheck semua item checkboxes
            $('.item').prop('checked', this.checked);
            filterToBeDisplayed();
        });

        // Ketika item checkbox apapun di click
        $('#dataContainerCheckbox').on('click', '.item', function() {
            // Ketika semua item checkboxes di centang, centang juga 'Select All'
            // Kalau tidak, uncheck 'Select All'
            if ($('.item:checked').length === $('.item').length) {
                $('#selectAll').prop('checked', true);
            } else {
                $('#selectAll').prop('checked', false);
            }
        });

        // update map ketika salah satu checkbox berubah kondisi
        $('#dataContainerCheckbox').on('change', '.item', function() {
            filterToBeDisplayed();
        });
 
    });

    // mengambarkan orang-orang yang dipilih
    function filterToBeDisplayed() {
        toBeDisplayed = []

        // membaca checkboxes yang di centang
        $('input.item:checked').each(function() {
            let nameDataCheckbox = $(this).attr('data-value');
            // Compare with actual data
            if (geoJsonData) {
                geoJsonData.features.forEach(function(feature) {
                    let nameActual = feature.properties.name;
                    
                    if (nameDataCheckbox === nameActual) {
                        toBeDisplayed.push(feature);
                    }
                });
                
            }
        });
        // Clear semua
        lineSegments.clearLayers();
        
        // Draw semua
        L.geoJson(toBeDisplayed, {
            onEachFeature: onEachFeature
        });
    }
    
</script>

<script>
    // Menggambarkan map dengan coordinate tertentu.
    // 
    var coordinateOriginal = [-6.134326, 106.735743];
    var zoom = 16; 
    var map = L.map('map').setView(coordinateOriginal, zoom);
        
    // tileLayer bisa diganti..
    // L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    //     attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    // }).addTo(map);

    // google maps layer
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png',{
        maxZoom: 21,
        subdomains:['mt0','mt1','mt2','mt3']
    }).addTo(map);


    // Mengambil lokasi user sekarang
    if (!navigator.geolocation) {
        console.log("does not support");
    } else {
        navigator.geolocation.getCurrentPosition(getPosition);
    }
    
    var marker, circle;
    // Menambahkan marker dan circle radius accuracy di posisi user sekarang
    function getPosition(position) {

        var lat = position.coords.latitude;
        var lng = position.coords.longitude;
        var acc = position.coords.accuracy;
        
        if (marker) {
            map.removeLayer(marker);
            map.removeLayer(circle);
        }
        marker = L.marker([lat, lng]);
        circle = L.circle([lat, lng], {radius: acc});
        
        var featureGroup = L.featureGroup([marker, circle]).addTo(map);
        // map diarahkan ke marker dan circle sekarang
        map.fitBounds(featureGroup.getBounds());
    }
    // Initialize a layer group for line segments
    var lineSegments = L.layerGroup().addTo(map);


</script>



<script>
    // untuk menggambar setiap `line`
    function onEachFeature(feature, layer) {
        let name = feature.properties.name;

        let currColor, marker;
        let colors = <?= json_encode($colors)?>;
        currColor = colors[name];
        
        // Icon setiap karyawan 
        var myCustomIcon = L.icon({
            iconUrl: 'img/' + name + '.png',
            iconSize: [25, 50], // size of the icon
            iconAnchor: [12.5, 50], // point of the icon which will correspond to marker's location
            popupAnchor: [0, -50] // point from which the popup should open relative to the iconAnchor
        });

        if (feature.properties.timestamps) {
            var timestamps = feature.properties.timestamps;
            var coords = feature.geometry.coordinates;

            for (var i = 0; i < coords.length - 1; i++) {
                var latlng1 = [coords[i][1], coords[i][0]]; // Swap coordinates
                var latlng2 = [coords[i + 1][1], coords[i + 1][0]]; // Swap coordinates
                
                // menggambarkan garis sesuai warna mereka
                var segment = L.polyline([latlng1, latlng2], {color: currColor, opacity: 0.6});
                
                // menambahkan waktu ketika `line` di click
                segment.bindPopup("Time: " + timestamps[i]);
                lineSegments.addLayer(segment);
            }
            // menyimpan data coordinate terakhir 
            lastMarkerCoords = [coords[coords.length - 1][1], coords[coords.length - 1][0]];

            // menambahkan marker di akhir perjalanan
            marker = L.marker(lastMarkerCoords, {icon: myCustomIcon});

            // menambahkan data nama dan waktu ketika marker di click
            marker.bindPopup(name + ' <br><br>' + timestamps[coords.length - 1]);
            
            // gambar
            lineSegments.addLayer(marker);

        }
    }

</script>


<script>
// --------------------------------------------------------------------------------
// `dateData` adalah data tanggal yang dipilih.
// 
// `timeFrom` adalah data waktu From dipilih.
// 
// `timeTo` adalah data waktu To dipilih.
// --------------------------------------------------------------------------------
var dateData = '<?= $today ?>';
var timeFrom = '00:00:00';
var timeTo = '23:59:59';

// helper function untuk format data
function formatDateForProcessing(dateStr) {
    var date = $.datepicker.parseDate("dd MM yy", dateStr);
    var year = date.getFullYear();
    var month = ("0" + (date.getMonth() + 1)).slice(-2); // Months are 0-based
    var day = ("0" + date.getDate()).slice(-2);
    return year + "-" + month + "-" + day;
}

$(function() {
    // 1. UI pemilihan tanggal dan update data:
    //     - dateData
    //     - geoJsonData
    $("#datepicker").datepicker({
        dateFormat: "dd MM yy",
        maxDate: 0,
        onSelect: function(dateText, inst) {
            // Ketika di click, 3 ajax di kirim
            // 1. mengupdate tampilan HTML pemilihan satuaan
            // 2. mengupdate tampilan HTML pemilihan checkbox
            // 3. update geoJsonData, data untuk orang-orang yang akan ditampilkan di tanggal tertentu.
            
            // update dateData
            dateData = formatDateForProcessing(dateText);

            // 1. Ajax pertama, mengupdate tampilan Satuaan
            var ajax1 = $.ajax({
                url: 'ajax_handler.php',
                type: 'POST',
                data: {
                    selectedDate: dateData,
                    timepickerFrom: timeFrom,
                    timepickerTo: timeTo,
                    responseType: 'single',
                },
                success: function(response) {
                    // DOM Manipulation untuk dynamic
                    $('#dataContainerSingle').html(response);
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                }
            });

            // 2. Ajax kedua, mengupdate tampilan Checkbox
            var ajax2 = $.ajax({
                url: 'ajax_handler.php',
                type: 'POST',
                data: {
                    selectedDate: dateData,
                    timepickerFrom: timeFrom,
                    timepickerTo: timeTo,
                    responseType: 'checkbox',
                },
                success: function(response) {
                    // DOM Manipulation untuk dynamic
                    $('#dataContainerCheckbox').html(response);
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                }
            });

            // 3. Ajax ketiga, menunggu ajax1 dan ajax2 untuk tampil, 
            //                 baru mengupdate geoJsonData.
            $.when(ajax1, ajax2).then(function() {
                $.ajax({
                    url: 'ajax_handler.php',
                    type: 'POST',
                    data: {
                        selectedDate: dateData,
                        timepickerFrom: timeFrom,
                        timepickerTo: timeTo,
                        responseType: 'json',
                    },
                    dataType: 'json',
                    success: function(geojsonData) {
                        // update geoJsonData
                        geoJsonData = geojsonData
                        
                        // secara automatis tampilkan semua 
                        $('input.item').prop('checked', true);
                        $('#selectAll').prop('checked', true);
                        filterToBeDisplayed();
    
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", error);
                    }
                });

            });
        
        }
    });
});

</script>

<script>

// --------------------------------------------------------------------------------
// `isUpdatingTimePicker` adalah flag agar tidak double input. [ignore]
// --------------------------------------------------------------------------------

var isUpdatingTimePicker = false;

// helper function untuk format data
function formatTimeForProcessing(timeStr) {
    var time = timeStr.match(/(\d+) : (\d+) (\w+)/);
    var hours = parseInt(time[1], 10);
    var minutes = time[2];
    var modifier = time[3];

    if (modifier === 'PM' && hours < 12) {
        hours += 12;
    }
    if (modifier === 'AM' && hours === 12) {
        hours = 0;
    }

    hours = ("0" + hours).slice(-2); // Add leading zero if needed
    return hours + ":" + minutes + ":00"; // Assuming seconds are "00"
}

$(function() {
    // UI pemilihan waktu dan update data:
    // 1. timeFrom
    // 2. timeTo

    // 1. update timeFrom
    $('input.timepicker[name="timeFrom"]').timepicker({
        timeFormat: 'h : mm p',
        dynamic: 'true',
        change: function(time) {
            // -----------ignore-------
            if (isUpdatingTimePicker) {
                return;
            }
            isUpdatingTimePicker = true;
            // ------------------------
            
            let element = $(this);

            // get access to this Timepicker instance
            let timepicker = element.timepicker();
            let selectedTime = $(this).timepicker('getTime');
            
            // mengubah waktu minimum timeTo, 
            // karena timeTo tidak boleh lebih awal dibanding timeFrom.
            if (selectedTime) {
                $('input.timepicker[name="timeTo"]').timepicker('option', 'minTime', selectedTime);
            }

            // update timeFrom data.
            timeFrom = formatTimeForProcessing(timepicker.format(time));

            // update geoJsonData.
            $.ajax({
                url: 'ajax_handler.php',
                type: 'POST',
                data: {
                    selectedDate: dateData,
                    timepickerFrom: timeFrom,
                    timepickerTo: timeTo,
                    responseType: 'json',
                },
                dataType: 'json',
                success: function(geojsonData) {
                    geoJsonData = geojsonData;
                    filterToBeDisplayed();
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                }
            })

            isUpdatingTimePicker = false;

        }
    });


    // 2. update timeTo
    $('input.timepicker[name="timeTo"]').timepicker({
        timeFormat: 'h : mm p',
        dynamic: 'true',
        change: function(time) {


            // -----------ignore-------
            if (isUpdatingTimePicker) {
                return;
            }
            isUpdatingTimePicker = true;
            // ------------------------

            
            let element = $(this);

            // get access to this Timepicker instance
            let timepicker = element.timepicker();
            let selectedTime = $(this).timepicker('getTime');

            // mengubah waktu maximum timeFrom, 
            // karena timeFrom tidak boleh lebih telat dibanding timeTo.
            $('input.timepicker[name="timeFrom"]').timepicker('option', 'maxTime', selectedTime);

            // update timeTo
            timeTo = formatTimeForProcessing(timepicker.format(time));
            $.ajax({
                url: 'ajax_handler.php',
                type: 'POST',
                data: {
                    selectedDate: dateData,
                    timepickerFrom: timeFrom,
                    timepickerTo: timeTo,
                    responseType: 'json',
                },
                dataType: 'json',
                success: function(geojsonData) {
                    
                    // update geoJsonData
                    geoJsonData = geojsonData;
                    filterToBeDisplayed();
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                }
            })

            isUpdatingTimePicker = false;

        }
    });
});

</script>






