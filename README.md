# The Villa - Luxury Hotel Website & Booking System

A premium luxury hotel website with a functional PHP/MySQL booking system.

## Project Structure

- **Main Website (HTML/CSS/JS)**:
  - `index.html`: Home page.
  - `rooms.html`: Room catalog.
  - `facilities.html`: Services and amenities.
  - `gallery.html`: Photo gallery.
  - `about.html`: Hotel story.
  - `contact.html`: Contact info.
  - `css/style.css`: Main stylesheet.
  - `js/main.js`: Interaction scripts.

- **Booking System (PHP/MySQL)**:
  - `booking/index.php`: Booking form.
  - `booking/process.php`: Booking logic.
  - `booking/confirmation.php`: Success page.
  - `booking/db_connect.php`: Database credentials.
  - `booking/schema.sql`: Database setup script.

## Deployment Instructions

### 1. Database Setup
1.  Access your MySQL database (e.g., via phpMyAdmin).
2.  Create a new database named `hotel_booking`.
3.  Import the `booking/schema.sql` file to create the necessary tables (`rooms`, `bookings`) and seed initial data.

### 2. Configure Connection
1.  Open `booking/db_connect.php`.
2.  Update the `$user` and `$pass` variables with your MySQL credentials.

### 3. Web Server
1.  Deploy the entire `villa` folder to your web server (e.g., Apache/Nginx).
2.  Ensure PHP is enabled.
3.  Navigate to `http://your-server/villa/index.html` to view the site.

## Features
- **Responsive Design**: Works on mobile, tablet, and desktop.
- **Dynamic Booking**: Checks dates and calculates total price based on room rates.
- **Validation**: Ensures check-out is after check-in and all fields are filled.
- **SQL Injection Protection**: Uses PDO prepared statements for security.

## Customization
- **Images**: Replace the placeholder images in `index.html`, `rooms.html`, and `gallery.html` with your high-res hotel photography.
- **Colors**: Edit the CSS variables in `css/style.css` to change the theme.
