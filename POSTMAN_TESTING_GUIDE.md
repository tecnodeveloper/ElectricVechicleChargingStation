# 🚀 EV Charging API - Postman Testing Guide

## Base Configuration
- **Base URL**: `http://127.0.0.1:8000/api/v1`
- **Content-Type**: `application/json`
- **Accept**: `application/json`

## Test Users Available
| Email | Password | Type | Status |
|-------|----------|------|---------|
| admin@evc.com | admin123 | Admin | Verified |
| john@example.com | password123 | User | Verified |
| jane@example.com | password123 | User | Unverified |

---

## 🔐 Authentication Endpoints

### 1. Register User
**POST** `/auth/register`

```json
{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "+1234567890"
}
```

**Expected Response (201):**
```json
{
    "success": true,
    "message": "Registration successful. Please verify your email with the OTP sent.",
    "data": {
        "user": {
            "id": 4,
            "name": "Test User",
            "email": "test@example.com",
            "is_verified": false
        },
        "otp_sent": true
    }
}
```

### 2. Login User
**POST** `/auth/login`

```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

**Expected Response (200):**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 2,
            "name": "John Doe",
            "email": "john@example.com",
            "is_admin": false
        },
        "token": "1|abc123def456...",
        "token_type": "Bearer"
    }
}
```

**⚠️ IMPORTANT**: Save the token for authenticated requests!

### 3. Verify OTP (for unverified users)
**POST** `/auth/verify-otp`

```json
{
    "email": "jane@example.com",
    "otp": "123456"
}
```

### 4. Admin Login
**POST** `/auth/login`

```json
{
    "email": "admin@evc.com",
    "password": "admin123"
}
```

### 5. Get User Profile (Authenticated)
**GET** `/auth/profile`

**Headers:** `Authorization: Bearer {your_token}`

### 6. Logout (Authenticated)
**POST** `/auth/logout`

**Headers:** `Authorization: Bearer {your_token}`

---

## 🏢 Station Endpoints

### 1. Get All Stations
**GET** `/stations`

**Optional Query Parameters:**
- `latitude=40.7128&longitude=-74.0060` - For distance calculation
- `radius=10` - Search radius in km
- `available=true` - Filter by availability
- `connector_type=Type 2` - Filter by connector type

**Example Request:**
```
GET /stations?latitude=40.7128&longitude=-74.0060&radius=5&available=true
```

### 2. Get Station by ID
**GET** `/stations/1`

### 3. Get Nearby Stations
**GET** `/stations/nearby?latitude=40.7128&longitude=-74.0060&radius=5`

### 4. Check Station Availability
**GET** `/stations/1/availability`

### 5. Create Station (Admin Only)
**POST** `/stations`

**Headers:** `Authorization: Bearer {admin_token}`

```json
{
    "name": "Test Charging Station",
    "address": "123 Test Street",
    "latitude": 40.7128,
    "longitude": -74.0060,
    "connector_type": "Type 2",
    "power_output": 150,
    "pricing_per_hour": 25.00,
    "is_available": true,
    "description": "Test station for API testing"
}
```

---

## 📅 Booking Endpoints (All Authenticated)

### 1. Get User Bookings
**GET** `/bookings`

**Headers:** `Authorization: Bearer {user_token}`

**Optional Query Parameters:**
- `status=active` - Filter by status
- `from_date=2025-01-01` - Date range filter
- `to_date=2025-12-31` - Date range filter

### 2. Create Booking
**POST** `/bookings`

**Headers:** `Authorization: Bearer {user_token}`

```json
{
    "station_id": 1,
    "start_time": "2025-09-27T16:00:00Z",
    "end_time": "2025-09-27T18:00:00Z",
    "estimated_energy_needed": 50
}
```

### 3. Get Active Bookings
**GET** `/bookings/active`

**Headers:** `Authorization: Bearer {user_token}`

### 4. Get Booking History
**GET** `/bookings/history`

**Headers:** `Authorization: Bearer {user_token}`

### 5. Get Specific Booking
**GET** `/bookings/1`

**Headers:** `Authorization: Bearer {user_token}`

### 6. Start Charging Session
**POST** `/bookings/1/start`

**Headers:** `Authorization: Bearer {user_token}`

### 7. Stop Charging Session
**POST** `/bookings/1/stop`

**Headers:** `Authorization: Bearer {user_token}`

```json
{
    "actual_energy_consumed": 45.5
}
```

### 8. Cancel Booking
**POST** `/bookings/1/cancel`

**Headers:** `Authorization: Bearer {user_token}`

---

## 👤 User Management Endpoints (All Authenticated)

### 1. Get User Profile
**GET** `/user/profile`

**Headers:** `Authorization: Bearer {user_token}`

### 2. Update Profile
**PUT** `/user/profile`

**Headers:** `Authorization: Bearer {user_token}`

```json
{
    "name": "Updated Name",
    "phone": "+1987654321"
}
```

### 3. Change Password
**POST** `/user/change-password`

**Headers:** `Authorization: Bearer {user_token}`

```json
{
    "current_password": "password123",
    "new_password": "newpassword123",
    "new_password_confirmation": "newpassword123"
}
```

### 4. Get Booking Statistics
**GET** `/user/booking-stats`

**Headers:** `Authorization: Bearer {user_token}`

### 5. Get Vehicle Preferences
**GET** `/user/vehicle-preferences`

**Headers:** `Authorization: Bearer {user_token}`

### 6. Add Vehicle Preference
**POST** `/user/vehicle-preferences`

**Headers:** `Authorization: Bearer {user_token}`

```json
{
    "vehicle_make": "Tesla",
    "vehicle_model": "Model 3",
    "vehicle_year": 2023,
    "battery_capacity": 75,
    "connector_type": "Type 2",
    "max_charging_power": 150,
    "is_primary": true
}
```

---

## 🔔 Notification Endpoints (All Authenticated)

### 1. Get Notifications
**GET** `/notifications`

**Headers:** `Authorization: Bearer {user_token}`

**Optional Query Parameters:**
- `read=false` - Filter by read status
- `type=booking` - Filter by notification type

### 2. Get Unread Count
**GET** `/notifications/unread-count`

**Headers:** `Authorization: Bearer {user_token}`

### 3. Mark All as Read
**POST** `/notifications/mark-all-read`

**Headers:** `Authorization: Bearer {user_token}`

### 4. Mark Notification as Read
**POST** `/notifications/1/mark-read`

**Headers:** `Authorization: Bearer {user_token}`

---

## 👨‍💼 Admin Endpoints (Admin Only)

### 1. Admin Dashboard
**GET** `/admin/dashboard`

**Headers:** `Authorization: Bearer {admin_token}`

### 2. Get All Users
**GET** `/admin/users`

**Headers:** `Authorization: Bearer {admin_token}`

**Optional Query Parameters:**
- `search=john` - Search users
- `verified=true` - Filter by verification status

### 3. Get User Details
**GET** `/admin/users/2`

**Headers:** `Authorization: Bearer {admin_token}`

### 4. Update User Status
**PUT** `/admin/users/2/status`

**Headers:** `Authorization: Bearer {admin_token}`

```json
{
    "status": "suspended"
}
```

### 5. Get All Stations (Admin View)
**GET** `/admin/stations`

**Headers:** `Authorization: Bearer {admin_token}`

### 6. Get All Bookings (Admin View)
**GET** `/admin/bookings`

**Headers:** `Authorization: Bearer {admin_token}`

### 7. Cancel Booking (Admin)
**POST** `/admin/bookings/1/cancel`

**Headers:** `Authorization: Bearer {admin_token}`

```json
{
    "reason": "Station maintenance required"
}
```

### 8. Revenue Analytics
**GET** `/admin/analytics/revenue?period=month`

**Headers:** `Authorization: Bearer {admin_token}`

### 9. Usage Analytics
**GET** `/admin/analytics/usage`

**Headers:** `Authorization: Bearer {admin_token}`

---

## 🧪 Testing Workflow in Postman

### Step 1: Environment Setup
1. Create a new Environment in Postman called "EV Charging API"
2. Add these variables:
   - `base_url`: `http://127.0.0.1:8000/api/v1`
   - `user_token`: (will be set after login)
   - `admin_token`: (will be set after admin login)

### Step 2: Authentication Flow
1. **Login as User**: POST `/auth/login` with john@example.com
2. **Save Token**: Copy the token from response and set it in environment variable `user_token`
3. **Login as Admin**: POST `/auth/login` with admin@evc.com
4. **Save Admin Token**: Copy the token and set it in `admin_token`

### Step 3: Test User Endpoints
1. Get user profile: GET `/user/profile`
2. Update profile: PUT `/user/profile`
3. Get bookings: GET `/bookings`
4. Create booking: POST `/bookings`
5. Start charging: POST `/bookings/{id}/start`

### Step 4: Test Admin Endpoints
1. Get dashboard: GET `/admin/dashboard`
2. View all users: GET `/admin/users`
3. View analytics: GET `/admin/analytics/revenue`

### Step 5: Test Station & Booking Flow
1. Get stations: GET `/stations`
2. Check availability: GET `/stations/{id}/availability`
3. Create booking: POST `/bookings`
4. Start session: POST `/bookings/{id}/start`
5. Stop session: POST `/bookings/{id}/stop`

---

## 📝 Common Response Formats

### Success Response
```json
{
    "success": true,
    "data": {...},
    "message": "Operation successful"
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error description",
    "errors": {
        "field": ["validation error"]
    }
}
```

### Paginated Response
```json
{
    "success": true,
    "data": {
        "data": [...],
        "current_page": 1,
        "last_page": 3,
        "per_page": 15,
        "total": 42
    }
}
```

---

## ⚡ Quick Test Checklist

- [ ] User registration works
- [ ] User login returns token
- [ ] Token authentication works for protected routes
- [ ] Admin login returns admin token
- [ ] Station listing works
- [ ] Booking creation works
- [ ] Charging session start/stop works
- [ ] Admin dashboard accessible
- [ ] User management works for admin
- [ ] Analytics endpoints work
- [ ] Error handling returns proper error codes
- [ ] Validation messages are clear

## 🐛 Troubleshooting

### Common Issues:
1. **401 Unauthorized**: Check if token is included in Authorization header
2. **403 Forbidden**: Ensure admin user for admin endpoints
3. **422 Validation Error**: Check request body format and required fields
4. **404 Not Found**: Verify the endpoint URL and method
5. **500 Server Error**: Check Laravel logs in `storage/logs/laravel.log`

### Debug Commands:
```bash
# Check if server is running
php artisan serve

# Clear cache if needed
php artisan cache:clear
php artisan config:clear

# View logs
tail -f storage/logs/laravel.log
```

---

🎉 **Happy Testing!** Your EV Charging API is ready for comprehensive testing in Postman!