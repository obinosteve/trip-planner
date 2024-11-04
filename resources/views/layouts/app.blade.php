<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <script>
        (g => {
            var h, a, k, p = "The Google Maps JavaScript API",
                c = "google",
                l = "importLibrary",
                q = "__ib__",
                m = document,
                b = window;
            b = b[c] || (b[c] = {});
            var d = b.maps || (b.maps = {}),
                r = new Set,
                e = new URLSearchParams,
                u = () => h || (h = new Promise(async (f, n) => {
                    await (a = m.createElement("script"));
                    e.set("libraries", [...r] + "");
                    for (k in g) e.set(k.replace(/[A-Z]/g, t => "_" + t[0].toLowerCase()), g[k]);
                    e.set("callback", c + ".maps." + q);
                    a.src = `https://maps.${c}apis.com/maps/api/js?` + e;
                    d[q] = f;
                    a.onerror = () => h = n(Error(p + " could not load."));
                    a.nonce = m.querySelector("script[nonce]")?.nonce || "";
                    m.head.append(a)
                }));
            d[l] ? console.warn(p + " only loads once. Ignoring:", g) : d[l] = (f, ...n) => r.add(f) && u().then(() =>
                d[l](f, ...n))
        })({
            key: "", // Replace your google map API key here
            v: "weekly",
        });
    </script>


    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        #map {
            height: 700px;
            width: 100%;

            /* height: calc(100% - (100px + 150px)); */
        }
    </style>
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <script>
        let map;
        let destinations = [];
        let markers = [];
        let polyline;
        let service;
        let inputIndex = null;
        const averageFuelConsumption = 8; // 8 liters per 100 km

        async function initMap() {

            const defaultLocation = {
                lat: 9.0820,
                lng: 8.6753
            }; // Center on Nigeria

            const {
                Map
            } = await google.maps.importLibrary("maps");

            // The map 
            map = new Map(document.getElementById("map"), {
                zoom: 11,
                center: defaultLocation,
                mapId: "map",
            });

            directionsService = new google.maps.DirectionsService();
            directionsRenderer = new google.maps.DirectionsRenderer({
                map: map,
                polylineOptions: {
                    strokeColor: "red", // Set the polyline color to red
                    strokeWeight: 5 // Adjust the thickness if desired
                },
            });

            loadDestinationsFromLocalStorage(); // Load saved destinations
            initAutocomplete();

        }

        async function initAutocomplete() {
            const inputElements = document.querySelectorAll(".search-input"); // Adjust selector as needed
            const {
                Place
            } = await google.maps.importLibrary("places")

            for (const input of inputElements) {
                const autocomplete = new google.maps.places.Autocomplete(input, {
                    fields: ["formatted_address", "geometry"],
                    types: ["geocode"] // Adjust type if needed
                });

                autocomplete.addListener("place_changed", () => {
                    const place = autocomplete.getPlace();

                    if (place.geometry) {
                        let location = {
                            lat: place.geometry.location.lat(),
                            lng: place.geometry.location.lng(),
                            address: place.formatted_address,
                            type: "end"
                        };

                        if (input.id == 'startInput') {
                            location.type = "start";
                        }

                        addDestination(location);
                        // saveDestinationsToLocalStorage();

                    } else {
                        alert("No details available for the selected location.");
                    }
                });
            }
        }


        async function addDestination(location) {

            const {
                AdvancedMarkerElement
            } = await google.maps.importLibrary("marker");

            if (inputIndex) {
                destinations[inputIndex] = location;
            } else {
                destinations.push(location);
            }

            let latLng = {
                lat: location.lat,
                lng: location.lng
            };

            // The marker positioned
            const marker = new AdvancedMarkerElement({
                map: map,
                position: latLng,
                title: `Destination ${destinations.length}`,
            });

            markers.push(marker);

            calculateAndDisplayRoute();
            renderDestinationList();
            updateMapView();
            // calculateDistances();
            saveDestinationsToLocalStorage(); // Save destinations
        }

        function renderDestinationList() {
            const locationInput = document.getElementById("locationInput");
            const destinationList = document.getElementById("destinationList");

            locationInput.innerHTML = "";
            destinationList.innerHTML = "";

            if (destinations.length > 0) {
                destinations.forEach((destination, index) => {
                    // Create container div for each destination
                    const div = document.createElement("div");
                    div.dataset.index = index;

                    // Create an input field for the destination
                    const input = document.createElement("input");
                    input.type = "text";
                    input.name = `destination[${index}]`;
                    input.className =
                        "border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full search-input";
                    input.value = destination.address;
                    input.placeholder = index == 0 ? "Enter start address" : "Enter destination address";
                    input.dataset.index = index;
                    // input.readOnly = true;

                    // Add an event listener to access the index if needed
                    input.addEventListener("click", function() {
                        inputIndex = this.dataset.index;
                    });

                    // Create a remove button for each destination
                    const removeButton = document.createElement("button");
                    if (index != 0) {
                        removeButton.textContent = "Remove";
                        removeButton.className = "btn btn-link text-danger text-sm remove-button";
                        removeButton.onclick = () => removeDestination(index);
                    }

                    // Append input and remove button to the container div
                    if (index == 0) {
                        div.appendChild(input);
                        locationInput.appendChild(div);
                    } else {
                        div.appendChild(input);
                        div.appendChild(removeButton);
                        destinationList.appendChild(div);
                    }
                });

                // Apply Sortable.js for drag-and-drop reordering
                if (!destinationList.sortableInitialized) {
                    new Sortable(destinationList, {
                        animation: 150,
                        ghostClass: "ghost",
                        onEnd: function(event) {
                            // Rearrange the destinations array based on the new order
                            const movedItem = destinations.splice(event.oldIndex, 1)[0];
                            destinations.splice(event.newIndex, 0, movedItem);
                            renderDestinationList(); // Re-render the list to update indices
                        }
                    });
                    destinationList.sortableInitialized = true;
                }

            }


            initAutocomplete();
        }

        // Function to remove a destination by index
        function removeDestination(index) {
            destinations.splice(index, 1); // Remove destination from array
            renderDestinationList(); // Re-render the list
            calculateAndDisplayRoute();

            saveDestinationsToLocalStorage();
        }

        function addDestinationField() {
            let newLocation = {
                lat: 0,
                lng: 0,
                address: null
            };

            addDestination(newLocation);

            renderDestinationList();

        }

        function updateMapView() {
            if (polyline) polyline.setMap(null);

            const path = destinations.map(dest => ({
                lat: dest.lat,
                lng: dest.lng
            }));
            polyline = new google.maps.Polyline({
                path: path,
                geodesic: true,
                strokeColor: "#FF0000",
                strokeOpacity: 1.0,
                strokeWeight: 2,
                map: map,
            });

            const bounds = new google.maps.LatLngBounds();
            destinations.forEach(dest => bounds.extend(dest));
            map.fitBounds(bounds);
        }

        // Function to calculate and display the route on the map
        function calculateAndDisplayRoute() {
            if (destinations.length === 0) {
                alert("Please add at least one destination.");
                return;
            }

            const startLoc = {
                lat: destinations[0].lat,
                lng: destinations[0].lng,
            }; // Start location

            const end = destinations[destinations.length - 1]; // End location

            const endLoc = {
                lat: end.lat,
                lng: end.lng,
            };

            const waypoints = destinations
                .slice(1, -1) // Get all items except the first and last destination
                .filter(dest => dest.lat !== 0)
                .map(dest => {
                    // Map the filtered destinations to the desired waypoint format
                    return {
                        location: {
                            lat: dest.lat,
                            lng: dest.lng,
                        },
                        stopover: true
                    };
                });


            directionsService.route({
                    origin: startLoc,
                    destination: endLoc,
                    waypoints: waypoints,
                    travelMode: google.maps.TravelMode.DRIVING,
                },
                (response, status) => {
                    if (status === google.maps.DirectionsStatus.OK) {
                        directionsRenderer.setDirections(response); // Render the route on the map
                    } else {
                        console.log("Directions request failed due to " + status);
                    }
                }
            );
        }

        function calculateDistances() {
            disableSubmitButton();

            var service = new google.maps.DistanceMatrixService();
            const distanceMatrix = service.getDistanceMatrix({
                origins: destinations,
                destinations: destinations,
                travelMode: google.maps.TravelMode.DRIVING,
                unitSystem: google.maps.UnitSystem.METRIC,
                region: 'NG',
            }, (response, status) => {
                if (status === google.maps.DistanceMatrixStatus.OK) {
                    const totalDistance = calculateTotalDistance(response);
                    const totalTime = calculateTotalTime(response);
                    const fuelNeeded = (totalDistance / 100) * averageFuelConsumption;

                    document.getElementById('totalTime').innerHTML = `${totalTime} mins`;
                    document.getElementById('totalDistance').innerHTML = `${totalDistance} km`;
                    document.getElementById('fuelNeeded').innerHTML = `${fuelNeeded.toFixed(2)} liters`;

                    fetchPointsOfInterest(); // Fetch attractions after calculating distances
                    fetchHotels(); // Fetch hotels after calculating distances
                    fetchEvents(); // Fetch events after calculating distances
                } else {
                    console.error("Error calculating distances:", status);
                }
            });

            // enableSubmitButton();
        }

        function calculateTotalDistance(response) {
            let total = 0;
            response.rows.forEach(row => {
                row.elements.forEach(element => {
                    if (element.status === "OK") {
                        total += element.distance.value; // value in meters
                    }
                });
            });
            return (total / 1000).toFixed(2); // convert to km
        }

        function calculateTotalTime(response) {
            let total = 0;
            response.rows.forEach(row => {
                row.elements.forEach(element => {
                    if (element.status === "OK") {
                        total += element.duration.value; // value in seconds
                    }
                });
            });
            return Math.round(total / 60); // convert to minutes
        }

        function fetchPointsOfInterest() {
            const pointsOfInterestList = document.getElementById("pointsOfInterest");
            pointsOfInterestList.innerHTML = ""; // Clear previous points

            for (let i = 0; i < destinations.length - 1; i++) {
                const origin = destinations[i];
                const destination = destinations[i + 1];

                const request = {
                    location: origin,
                    radius: '50000', // Search radius in meters
                    type: ['tourist_attraction'], // Customize this based on user interests
                };

                const service = new google.maps.places.PlacesService(map);
                service.nearbySearch(request, (results, status) => {
                    if (status === google.maps.places.PlacesServiceStatus.OK) {
                        results.forEach(place => {
                            const poiItem = document.createElement("span");
                            poiItem.className = "badge text-bg-primary";
                            poiItem.style.marginRight = "5px";
                            poiItem.textContent = place.name;

                            pointsOfInterestList.appendChild(poiItem);
                        });
                    } else {
                        console.error("Places service failed:", status);
                    }
                });
            }
        }

        function fetchHotels() {
            const hotelList = document.getElementById("hotels");
            hotelList.innerHTML = ""; // Clear previous hotels

            for (let i = 0; i < destinations.length - 1; i++) {
                const origin = destinations[i];
                const destination = destinations[i + 1];
                const distance = google.maps.geometry.spherical.computeDistanceBetween(origin, destination);

                // Define a threshold for long distances (e.g., more than 100 km)
                if (distance > 100000) { // 100 km
                    const midPoint = {
                        lat: (origin.lat + destination.lat) / 2,
                        lng: (origin.lng + destination.lng) / 2
                    };

                    const request = {
                        location: midPoint,
                        radius: '50000', // Search radius in meters
                        type: ['lodging'], // Hotels or lodging
                    };

                    const service = new google.maps.places.PlacesService(map);
                    service.nearbySearch(request, (results, status) => {
                        if (status === google.maps.places.PlacesServiceStatus.OK) {
                            results.forEach(place => {
                                const hotelItem = document.createElement("span");
                                hotelItem.className = "badge text-bg-success";
                                hotelItem.style.marginRight = "5px";
                                hotelItem.textContent = `${place.name} - Rating: ${place.rating || "N/A"}`;

                                hotelList.appendChild(hotelItem);
                            });
                        } else {
                            console.error("Places service failed:", status);
                        }
                    });
                }
            }
        }

        function fetchEvents() {
            enableSubmitButton();

            const eventsList = document.getElementById("events");
            eventsList.innerHTML = ""; // Clear previous events

            // const startDate = document.getElementById("startDate").value;
            // const endDate = document.getElementById("endDate").value;

            // if (!startDate || !endDate) {
            //     alert("Please select start and end dates for the trip.");
            //     return;
            // }

            // for (let destination of destinations) {
            //     const requestUrl =
            //         `https://api.eventservice.com/events?location=${destination.lat},${destination.lng}&start_date=${startDate}&end_date=${endDate}`; // Use a real event service API
            //     fetch(requestUrl)
            //         .then(response => response.json())
            //         .then(data => {
            //             data.events.forEach(event => {
            //                 const eventItem = document.createElement("li");
            //                 eventItem.textContent = `${event.name} - Date: ${event.date}`;
            //                 eventsList.appendChild(eventItem);
            //             });
            //         })
            //         .catch(err => console.error("Failed to fetch events:", err));
            // }
        }

        function loadDestinationsFromLocalStorage() {
            const storedDestinations = JSON.parse(localStorage.getItem('destinations')) || [];

            if (storedDestinations.length > 0) {
                storedDestinations.forEach(dest => {
                    addDestination({
                        lat: dest.lat,
                        lng: dest.lng,
                        address: dest.address,
                    });
                });

                calculateDistances();
            } else {
                loadCurrentLocation();
            }
        }

        function loadCurrentLocation() {
            let defaultLocation = {
                lat: 0,
                lng: 0,
                address: null
            };

            function success(position) {
                defaultLocation = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude,
                    address: null
                };

                // addDestination(defaultLocation);
                geocodeLatLng(defaultLocation);
            }

            function error(error) {
                addDestination(defaultLocation);
                let msg = error.message ?? 'Unable to retrieve your current location';

                alert(msg);
            }

            if (!navigator.geolocation) {
                addDestination(defaultLocation);
                alert('Geolocation is not supported by your browser');
            } else {
                const options = {
                    enableHighAccuracy: true,
                    maximumAge: 30000,
                    timeout: 27000,
                };
                navigator.geolocation.getCurrentPosition(success, error, options);
            }


        }

        function geocodeLatLng(location) {

            let latlng = {
                lat: location.lat,
                lng: location.lng
            };
            new google.maps.Geocoder()
                .geocode({
                    location: latlng
                })
                .then((response) => {
                    if (response.results[0]) {
                        location = {
                            lat: location.lat,
                            lng: location.lng,
                            address: response.results[0].formatted_address
                        };

                        addDestination(location);
                    } else {
                        window.alert("No results found");
                    }
                })
                .catch((e) => window.alert("Geocoder failed due to: " + e));
        }

        function saveDestinationsToLocalStorage() {
            if (inputIndex) {
                // update destination
                destinations[inputIndex] = {
                    lat: destinations[inputIndex].lat,
                    lng: destinations[inputIndex].lng,
                    address: destinations[inputIndex].address
                };

                localStorage.setItem('destinations', JSON.stringify(destinations));
            } else {
                // add destination
                localStorage.setItem('destinations', JSON.stringify(destinations));
            }
        }

        function clearPlan() {
            destinations = [];
            document.getElementById("pointsOfInterest").innerHTML = "";
            document.getElementById("hotels").innerHTML = "";
            document.getElementById("events").innerHTML = "";
            document.getElementById("startDate").value = "";
            document.getElementById("endDate").value = "";
            destinations = [];
            updateMapView();
            localStorage.clear('destinations');
            renderDestinationList();
            loadCurrentLocation();
            // clearDestinationsFromLocalStorage();

            const inputElements = document.querySelectorAll(".search-input");
            for (const input of inputElements) {
                input.value = "";
            }

        }

        function disableSubmitButton() {
            const submitButton = document.getElementById("btnSummary");
            const tbSummary = document.getElementById("tbSummary");

            tbSummary.style.display = "none";
            submitButton.innerText = "Please wait...";
            submitButton.disabled = true;
        }

        function enableSubmitButton() {
            const submitButton = document.getElementById("btnSummary");
            const tbSummary = document.getElementById("tbSummary");

            tbSummary.style.display = "block";
            submitButton.innerText = "Get Trip Summary";
            submitButton.disabled = false;

        }

        // Initialize the map when the window loads
        // window.onload = initMap;
        window.onload = initMap();
    </script>
</body>

</html>
