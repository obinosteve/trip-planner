<x-app-layout>
    <div class="">
        <div class="">
            <div class="bg-white overflow-hidden shadow-sm position-relative">
                <div id="map"></div>
                <div class="card position-absolute" style="top:0;height:100%;width:300px;">
                    <div class="card-header">
                        <h2 class="card-title">Lets Plan Your Next Trip</h2>
                    </div>
                    <div class="card-body overflow-scroll">
                        <div class="mt-2">
                            <x-input-label for="startInput" :value="__('Start location:')" />
                            <div id="locationInput"></div>

                            {{-- <div>
                                <x-input-label for="startInput" :value="__('Start location:')" />
                                <x-text-input id="startInput" name="startInput" type="text"
                                    class="mt-1 block w-full search-input" placeholder="Enter start location" />
                            </div> --}}

                            <x-input-label for="startInput" class="mt-2" :value="__('Destinations:')" />
                            <div id="destinationList" class="mt-2">
                            </div>
                            <div id="destinationsContainer" class="mt-2">
                                {{-- <div id="destinationList" class="destination-list"></div> --}}
                                <button type="button" class="btn btn-primary w-full" onclick="addDestinationField()"
                                    class="mt-2">Add
                                    Destination</button>
                            </div>

                            <div class="mt-2">
                                <div>
                                    <x-input-label for="startDate" :value="__('Trip start date:')" />
                                    <x-text-input id="startDate" name="startDate" type="date"
                                        class="mt-1 block w-full" placeholder="Enter start date" />
                                </div>
                                <div>
                                    <x-input-label for="endDate" :value="__('Trip end date:')" />
                                    <x-text-input id="endDate" name="endDate" type="date" class="mt-1 block w-full"
                                        placeholder="Enter end date" />
                                </div>
                            </div>

                            <div class="mt-2" align="center">
                                <div>
                                    <x-primary-button class=" text-center" id="btnSummary"
                                        onclick="calculateDistances()">Get
                                        Trip
                                        Summary</x-primary-button>

                                </div>

                                <button class="btn btn-link" onclick="clearPlan()">Clear Plan</button>
                            </div>


                            <table class="table table-bordered mt-5" id="tbSummary">
                                <thead>
                                    <tr>
                                        <th colspan="2">Trip Summary</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Total Time</td>
                                        <td>
                                            <span id="totalTime"></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Total Distance</td>
                                        <td>
                                            <span id="totalDistance"></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Total Fuel Needed</td>
                                        <td>
                                            <span id="fuelNeeded"></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Points of Interest</td>
                                        <td>
                                            <div id="pointsOfInterest"></div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Suggested Hotels</td>
                                        <td>
                                            <div id="hotels"></div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Events Along Your Route</td>
                                        <td>
                                            <ul id="events"></ul>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

</x-app-layout>
