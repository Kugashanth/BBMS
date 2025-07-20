# Blood Bank Management System

A comprehensive web-based blood bank management system designed to efficiently manage blood inventory across hospitals in Sri Lanka. This system facilitates blood donation tracking, inventory management, and enables hospitals to request blood units while maintaining real-time stock updates.

## 🩸 Features

### Core Functionality
- Real-time Blood Stock Tracking** - View available blood units by blood group
- Blood Camp Management** - Display and filter upcoming blood donation camps
- Advanced Search System** - Find blood availability by blood group, province, and district
- User Authentication** - Secure login system for admins and hospitals
- Responsive Design** - Mobile-friendly interface with modern UI

### User Roles
- Admin: Complete system management, user account control, and blood bank operations
- Hospitals: Blood unit requests, stock level updates, and donor record management

### Interactive Features
- Dynamic filtering for blood camps (Today, Tomorrow, This Week, etc.)
- Province-based district loading for precise location searches
- AJAX-powered real-time updates without page refresh
- Dark mode toggle for better user experience
- Smooth animations and transitions

## 🚀 Technologies Used

- Backend: PHP 7.4+, MySQL
- Frontend: HTML5, CSS3, JavaScript (ES6)
- Libraries: 
  - jQuery 3.6.0
  - AOS (Animate On Scroll) 2.3.4
  - Font Awesome 6.4.2
- Database: MySQL with prepared statements for security

## 📋 Prerequisites

Before running this application, ensure you have:

- Web Server: Apache or Nginx
- PHP: Version 7.4 or higher
- MySQL: Version 5.7 or higher
- Web Browser: Chrome, Firefox, Safari, or Edge (latest versions)

## ⚙️ Installation

1. Clone the repository
   ```bash
   git clone https://github.com/yourusername/blood-bank-management.git
   cd blood-bank-management
   ```

2. Database Setup
   - Create a MySQL database named `blood_bank`
   - Import the database schema (ensure you have the SQL file)
   - Update database configuration in `config/db.php`

3. Configure Database Connection
   ```php
   // config/db.php
   $servername = "localhost";
   $username = "root";
   $password = "";
   $dbname = "1_blood_bank";
   ```

4. Set File Permissions
   ```bash
   chmod 755 /path/to/project
   chmod 644 *.php
   ```

5. Start Web Server
   - For XAMPP/WAMP: Place files in `htdocs` folder
   - For LAMP: Place files in `/var/www/html`
   - Access via `http://localhost/blood-bank-management`

## 🗃️ Database Schema

### Required Tables
- `admin` - Admin user accounts
- `hospitals` - Hospital user accounts  
- `blood_inventory` - Blood stock data
- `blood_camps` - Upcoming donation camps
- Additional tables for donors, requests, etc.

### Key Fields
```sql
-- Example blood_inventory structure
CREATE TABLE blood_inventory (
    id INT PRIMARY KEY AUTO_INCREMENT,
    blood_group VARCHAR(5),
    units INT,
    hospital_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## 🔐 Security Features

- **Password Hashing**: Uses PHP's `password_hash()` and `password_verify()`
- **SQL Injection Prevention**: Prepared statements throughout
- **Session Management**: Secure session handling for user authentication
- **Input Validation**: Client and server-side validation
- **XSS Protection**: Proper output escaping

## 📱 Usage

### For Administrators
1. Login with admin credentials
2. Manage blood inventory across all hospitals
3. View system-wide statistics and reports
4. Manage user accounts and permissions

### For Hospitals
1. Register/Login to the system
2. Update blood stock levels
3. Request blood units from other hospitals
4. Manage donor records and appointments

### For Public Users
1. View available blood stock
2. Find upcoming blood donation camps
3. Search for blood availability by location
4. Access system information

## 🌍 Sri Lankan Context

The system is specifically designed for Sri Lanka with:
- **Provincial Structure**: All 9 provinces included
- **District Mapping**: Complete district list for each province
- **Blood Group Support**: All major blood groups (A+, A-, B+, B-, O+, O-, AB+, AB-)
- **Location-based Search**: Province and district filtering

## 📂 Project Structure

```
blood-bank-management/
├── config/
│   └── db.php              # Database configuration
├── css/
│   └── login.css           # Login page styles
├── img/                    # Image assets
├── js/                     # JavaScript files
├── index.php               # Main homepage
├── login.php               # User authentication
├── about.php               # About page
├── navbar.php              # Navigation component
├── sidebar.php             # Sidebar component
├── footer.php              # Footer component
├── fetch_camps.php         # AJAX endpoint for camps
├── fetch_blood.php         # AJAX endpoint for blood search
└── README.md               # This file
```

## 🔧 Configuration

### Environment Variables
Create a `.env` file for sensitive configuration:
```
DB_HOST=localhost
DB_USERNAME=your_username
DB_PASSWORD=your_password
DB_NAME=blood_bank
```

### Customization Options
- Update logo in `img/logo.png`
- Modify color scheme in CSS files
- Adjust blood group types in dropdown menus
- Add new provinces/districts as needed

## 🐛 Troubleshooting

### Common Issues
1. Database Connection Error
   - Check database credentials in `config/db.php`
   - Ensure MySQL service is running

2. Session Issues
   - Verify session configuration in PHP
   - Check file permissions for session storage

3. AJAX Not Working
   - Check browser console for JavaScript errors
   - Verify file paths for AJAX endpoints

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Development Guidelines
- Follow PHP coding standards (PSR-12)
- Use meaningful variable and function names
- Add comments for complex logic
- Test thoroughly before submitting PR


## 🙏 Acknowledgments

- Sri Lankan healthcare system for requirements and feedback
- Contributors and testers
- Open source libraries and frameworks used
- Medical professionals who provided domain expertise

## 📈 Future Enhancements

- [ ] Mobile application development
- [ ] SMS/Email notification system
- [ ] Advanced reporting and analytics
- [ ] Integration with hospital management systems
- [ ] Multi-language support (Sinhala, Tamil)
- [ ] Blood donor management system
- [ ] Appointment scheduling system
- [ ] Emergency blood request alerts

---

**Made with ❤️ for the Sri Lankan healthcare community**
