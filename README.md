# ESS TRACKER Backend API

A professional PHP backend API for ESS TRACKER inquiry management system.

## Features

- ✅ Inquiry submission with validation
- ✅ Spam protection (max 3 inquiries per phone)
- ✅ Email notifications
- ✅ Database integration
- ✅ CORS support
- ✅ JSON and Form data support

## Project Structure

```
ess-track-backend/
├── api/           # API endpoints
│   ├── submit.php # Main inquiry submission
│   └── test-db.php # Database connectivity test
├── config/        # Configuration
│   └── database.php # Database connection
├── src/           # Business logic
│   ├── inquiry.php   # Inquiry model
│   ├── validator.php # Input validation
│   └── response.php  # Response helper
└── .htaccess     # URL routing
```

## API Endpoints

### GET /api/submit.php
Returns API status information.

**Response:**
```json
{
  "success": true,
  "message": "ESSPL Backend API is running",
  "timestamp": "2026-04-20 19:13:59",
  "data": {
    "version": "2.0",
    "company": "Electronic Safety and Security Private Limited",
    "endpoints": {
      "POST /api/submit.php": "Submit inquiry",
      "GET /api/submit.php": "API status check"
    }
  }
}
```

### POST /api/submit.php
Submit a new inquiry.

**Required Fields:**
- `first_name` (min 2 chars)
- `last_name` (required)
- `email` (valid email)
- `phone` (11-digit Pakistani number)
- `message` (min 10 chars)

**Optional Fields:**
- `vehicleType`
- `interested_package` (default: "Not Sure")

**Success Response:**
```json
{
  "success": true,
  "message": "Thank you! Your inquiry has been received...",
  "timestamp": "2026-04-20 19:13:59",
  "data": {
    "inquiry_id": 123,
    "name": "John Doe"
  }
}
```

## Setup Instructions

1. **Database Setup:**
   - Create database: `ess-track-backend-db`
   - Create table:
   ```sql
   CREATE TABLE inquiries (
     id INT AUTO_INCREMENT PRIMARY KEY,
     first_name VARCHAR(50) NOT NULL,
     last_name VARCHAR(50) NOT NULL,
     email VARCHAR(100) NOT NULL,
     phone_number VARCHAR(20) NOT NULL,
     vehicle_type VARCHAR(50),
     message TEXT NOT NULL,
     interested_package VARCHAR(100) DEFAULT 'Not Sure',
     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );
   ```

2. **Web Server:**
   - Use XAMPP/WAMP or any PHP server
   - Place in `htdocs` folder
   - Access via: `http://localhost/ess-track-backend/`

3. **Testing:**
   - GET status: `http://localhost/ess-track-backend/api/submit.php`
   - POST inquiry: Use Postman or curl

## Email Configuration

Emails are sent to `info@esspl.com.pk` after successful submission.
Configure your server mail settings for production use.

## Technologies Used

- PHP 7.4+
- MySQL/MariaDB
- JSON API
- OOP Architecture

## License

© 2026 Electronic Safety and Security Private Limited