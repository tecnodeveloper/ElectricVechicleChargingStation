# EV Charging Management System

A comprehensive Electric Vehicle (EV) charging management system built with Laravel 12, featuring a complete web interface and RESTful API architecture.

## 🚗 Features

### Web Application
- **Responsive Dashboard** - Modern EV charging interface with real-time station data
- **User Authentication** - Complete registration, login, and OTP verification system
- **Admin Panel** - Comprehensive management interface for stations, users, and bookings
- **User Pages** - Bookings, reservations, and profile management interfaces
- **Real-time Updates** - Live charging session monitoring and notifications

### API Features
- **RESTful API** - Complete API coverage for all system functionality
- **Authentication** - Sanctum-based API authentication with token management
- **Station Management** - CRUD operations with location-based search
- **Booking System** - Complete booking lifecycle management
- **User Management** - Profile, preferences, and vehicle management
- **Admin Dashboard** - Analytics, user management, and system monitoring
- **Notifications** - Real-time notification system with multiple delivery methods

## 🛠️ Tech Stack

- **Backend**: Laravel 12, PHP 8.2+
- **Frontend**: TailwindCSS 4.0, Alpine.js
- **Database**: MySQL with Eloquent ORM
- **Authentication**: Laravel Sanctum (API) + Session-based (Web)
- **APIs**: RESTful architecture with comprehensive endpoints

## 📋 Requirements

- PHP 8.2 or higher
- Composer
- Node.js & NPM
- MySQL 8.0+
- Laravel 12

## 🚀 Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd evc
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure database**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=evc
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

6. **Run migrations**
   ```bash
   php artisan migrate
   ```

7. **Build assets**
   ```bash
   npm run build
   ```

8. **Start the development server**
   ```bash
   php artisan serve
   ```

## 📁 Project Structure

```
├── app/
│   ├── Http/Controllers/
│   │   ├── API/               # API Controllers
│   │   │   ├── AuthApiController.php
│   │   │   ├── UserApiController.php
│   │   │   ├── StationApiController.php
│   │   │   ├── BookingApiController.php
│   │   │   ├── AdminApiController.php
│   │   │   └── NotificationApiController.php
│   │   └── [Other Controllers] # Web Controllers
│   ├── Models/
│   │   ├── User.php
│   │   ├── Station.php
│   │   ├── Booking.php
│   │   ├── Notification.php
│   │   ├── Payment.php
│   │   ├── PaymentMethod.php
│   │   └── VehiclePreference.php
├── database/migrations/       # Database schemas
├── resources/views/          # Blade templates
├── routes/
│   ├── web.php              # Web routes
│   └── api.php              # API routes
```

## 🔗 API Documentation

### Base URL
```
http://localhost:8000/api/v1
```

### Authentication Endpoints

#### Register User
```http
POST /auth/register
```
**Request Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "+1234567890"
}
```

#### Login User
```http
POST /auth/login
```
**Request Body:**
```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

#### Verify OTP
```http
POST /auth/verify-otp
```
**Request Body:**
```json
{
    "email": "john@example.com",
    "otp": "123456"
}
```

#### Logout
```http
POST /auth/logout
Authorization: Bearer {token}
```

### Station Endpoints

#### Get All Stations
```http
GET /stations?latitude=40.7128&longitude=-74.0060&radius=10&available=true
```

#### Get Station Details
```http
GET /stations/{id}
```

#### Get Nearby Stations
```http
GET /stations/nearby?latitude=40.7128&longitude=-74.0060&radius=5
```

#### Get Station Availability
```http
GET /stations/{id}/availability
```

#### Create Station (Admin)
```http
POST /stations
Authorization: Bearer {token}
```
**Request Body:**
```json
{
    "name": "Downtown Charging Hub",
    "address": "123 Main St, City",
    "latitude": 40.7128,
    "longitude": -74.0060,
    "connector_type": "Type 2",
    "power_output": 150,
    "pricing_per_hour": 25.00
}
```

### Booking Endpoints

#### Get User Bookings
```http
GET /bookings?status=active&from_date=2025-01-01&to_date=2025-12-31
Authorization: Bearer {token}
```

#### Create Booking
```http
POST /bookings
Authorization: Bearer {token}
```
**Request Body:**
```json
{
    "station_id": 1,
    "start_time": "2025-09-27T14:00:00Z",
    "end_time": "2025-09-27T16:00:00Z",
    "estimated_energy_needed": 50
}
```

#### Start Charging Session
```http
POST /bookings/{id}/start
Authorization: Bearer {token}
```

#### Stop Charging Session
```http
POST /bookings/{id}/stop
Authorization: Bearer {token}
```
**Request Body:**
```json
{
    "actual_energy_consumed": 45.5
}
```

#### Cancel Booking
```http
POST /bookings/{id}/cancel
Authorization: Bearer {token}
```

### User Management Endpoints

#### Get User Profile
```http
GET /user/profile
Authorization: Bearer {token}
```

#### Update Profile
```http
PUT /user/profile
Authorization: Bearer {token}
```
**Request Body:**
```json
{
    "name": "John Doe Updated",
    "phone": "+1234567891",
    "profile_picture": "base64_image_data"
}
```

#### Change Password
```http
POST /user/change-password
Authorization: Bearer {token}
```
**Request Body:**
```json
{
    "current_password": "oldpassword",
    "new_password": "newpassword123",
    "new_password_confirmation": "newpassword123"
}
```

#### Get Booking Statistics
```http
GET /user/booking-stats
Authorization: Bearer {token}
```

#### Vehicle Preferences
```http
GET /user/vehicle-preferences
POST /user/vehicle-preferences
PUT /user/vehicle-preferences/{id}
DELETE /user/vehicle-preferences/{id}
Authorization: Bearer {token}
```

### Notification Endpoints

#### Get Notifications
```http
GET /notifications?read=false&type=booking
Authorization: Bearer {token}
```

#### Mark as Read
```http
POST /notifications/{id}/mark-read
Authorization: Bearer {token}
```

#### Mark All as Read
```http
POST /notifications/mark-all-read
Authorization: Bearer {token}
```

#### Get Unread Count
```http
GET /notifications/unread-count
Authorization: Bearer {token}
```

### Admin Endpoints

#### Admin Dashboard
```http
GET /admin/dashboard
Authorization: Bearer {admin_token}
```

#### User Management
```http
GET /admin/users?search=john&verified=true
GET /admin/users/{id}
PUT /admin/users/{id}/status
Authorization: Bearer {admin_token}
```

#### Revenue Analytics
```http
GET /admin/analytics/revenue?period=month
Authorization: Bearer {admin_token}
```

#### Cancel Booking (Admin)
```http
POST /admin/bookings/{id}/cancel
Authorization: Bearer {admin_token}
```
**Request Body:**
```json
{
    "reason": "Station maintenance required"
}
```

## 🗄️ Database Schema

### Key Tables
- **users** - User accounts and profiles
- **stations** - Charging station information
- **bookings** - Charging session bookings
- **notifications** - User notifications
- **payments** - Payment records
- **payment_methods** - User payment methods
- **vehicle_preferences** - User vehicle information

### Relationships
- User → Bookings (One to Many)
- User → VehiclePreferences (One to Many)
- User → Notifications (One to Many)
- Station → Bookings (One to Many)
- Booking → Payment (One to One)

## 🔒 Authentication

The system supports dual authentication:
- **Web Application**: Session-based authentication
- **API**: Laravel Sanctum token-based authentication

### Getting API Token
After successful login via API, you'll receive a token:
```json
{
    "success": true,
    "user": {...},
    "token": "1|abc123...",
    "token_type": "Bearer"
}
```

Use this token in the Authorization header for subsequent requests:
```
Authorization: Bearer 1|abc123...
```

## 🚀 Usage Examples

### Complete Booking Flow
1. **Find nearby stations**
2. **Check availability**
3. **Create booking**
4. **Start charging session**
5. **Stop charging session**
6. **View booking history**

### Admin Workflow
1. **Login as admin**
2. **View dashboard analytics**
3. **Manage users and stations**
4. **Monitor bookings**
5. **Generate reports**

## 📊 Response Format

All API responses follow a consistent format:

### Success Response
```json
{
    "success": true,
    "data": {...},
    "message": "Operation completed successfully"
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error description",
    "errors": {...}
}
```

### Paginated Response
```json
{
    "success": true,
    "data": {
        "data": [...],
        "current_page": 1,
        "last_page": 5,
        "per_page": 15,
        "total": 75
    }
}
```

## ⚡ Performance Features

- **Pagination** - All list endpoints support pagination
- **Filtering** - Advanced filtering options for data retrieval
- **Caching** - Model relationships and query optimization
- **Location-based Search** - Efficient distance calculations for stations
- **Background Processing** - Async notification delivery

## 🛡️ Security Features

- **Input Validation** - Comprehensive request validation
- **Rate Limiting** - API rate limiting protection
- **CORS Support** - Configurable cross-origin requests
- **Token Management** - Secure API token handling
- **Admin Authorization** - Role-based access control

## 🐛 Error Handling

The API includes comprehensive error handling with appropriate HTTP status codes:
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

## 📝 Development

### Testing
```bash
php artisan test
```

### Code Style
```bash
./vendor/bin/pint
```

### Database Seeding
```bash
php artisan db:seed
```

## 📞 Support

For issues, feature requests, or questions:
- Create an issue in the repository
- Contact the development team
- Check the API documentation for detailed endpoint information

## 📄 License

This project is licensed under the MIT License.

---

**Built with ❤️ for the future of electric mobility** 🚗⚡
