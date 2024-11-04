# Project requirement
- php 8.3
- Google map ApI key
- Allow browser location

# Setup instruction

- Ensure your pc is connected to the internet
- Ensure your pc is setup to run php Eg. You have any of WAMP/XAMP/Laragon/Herd installed on your machine
- Use git to clone this repository to your machine
- CD into the project repository that you just cloned above
- Run `composer install`
- Open this file on your favourite text editor: `resources/views/layouts/app.blade.php` and replace the google API key with your own key.
- On the terminal, run: `php artisan serve` to start the dev server
- Finally, go to `http://127.0.0.1:8000` to login


# Usage
- Create a new account
- Login with the new account
- Enter the start address
- Click on `Add Destination` to start adding the destinations and waypoints
- Select the trip start and end dates
- Finally, click on `Get Trip Summary` to get the trip summary
- Click on the `Clear Plan` button to refresh your selection