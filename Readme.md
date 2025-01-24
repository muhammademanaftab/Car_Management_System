# Car Management System (iKarRental)

## Description

The **Car Management System (iKarRental)** is a PHP-based web application designed to facilitate car rental services. The application allows guest users to browse available cars, while registered users can book cars for specific time intervals. Administrators have the capability to manage car listings and view all bookings.

**Key Features:**

- **Guest Users:**
  - Browse available cars.
  - Filter cars by:
    - Availability within a specific time range.
    - Transmission type (Automatic/Manual).
    - Passenger capacity.
    - Daily price range.
  - View detailed information for each car, including brand, model, year, transmission type, fuel type, seating capacity, and daily price.

- **Registered Users:**
  - Register and log in to book cars.
  - Book cars for specific time intervals.
  - View a list of previous bookings on a profile page.
  - Logout functionality.

- **Administrators:**
  - Separate admin login.
  - Add new cars, edit existing cars, and delete cars (including associated bookings).
  - View all bookings on the admin profile page.

## Technologies Used

- **PHP**: Core language for dynamic functionality.
- **HTML & CSS**: For layout and styling.
- **JavaScript**: For client-side interactivity.
- **JSON Files**: Utilized for data storage, including user information, car listings, and reservations.

## Installation

1. **Clone the Repository:**
   ```bash
   git clone https://github.com/muhammademanaftab/Car_Management_System.git
   ```
2. **Set Up a Local Web Server:**
   - Use a local web server environment like XAMPP or WAMP.
   - Place the project files in the appropriate directory (e.g., `htdocs` for XAMPP).

3. **Access the Application:**
   - Navigate to `http://localhost/Car_Management_System/homepage.php` in your browser.

## Usage

1. **Browsing Cars:**
   - Open the homepage to view available cars.
   - Use the filtering options to narrow down choices based on your preferences.

2. **User Registration and Login:**
   - Register as a new user or log in with existing credentials to book cars.

3. **Booking a Car:**
   - Select a car and specify the desired booking period.
   - Confirm the booking to finalize the reservation.

4. **Administrator Access:**
   - Log in using the administrator credentials:
     - Email: `admin@ikarrental.hu`
     - Password: `admin`
   - Manage car listings and view all bookings through the admin profile page.

## Data Storage

The application uses JSON files for data storage:

- `users.json`: Stores user information.
- `cars.json`: Contains car listings.
- `reservations.json`: Records booking details.

These files are located in the root directory of the project.

## Contributing

Contributions are welcome! To contribute:

1. Fork the repository.
2. Create a new branch:
   ```bash
   git checkout -b feature-name
   ```
3. Commit your changes:
   ```bash
   git commit -m "Add feature-name"
   ```
4. Push to the branch:
   ```bash
   git push origin feature-name
   ```
5. Open a pull request.

## Contact

For questions or issues, feel free to contact:

- **Name**: Muhammad Aftab
- **GitHub**: [muhammademanaftab](https://github.com/muhammademanaftab)
- **Email**: emanaftab2022@gmail.com

---

Thank you for exploring this project! Your feedback is appreciated.

