# ERP

An ERP system built with Laravel.

## Requirements

- PHP **8.2** or higher
- Composer
- MySQL or MariaDB

## Installation

1. **Download the Project**  
   Download the project as a ZIP file and extract it, or clone it using:
   ```bash
   git clone https://github.com/username/erp.git
   cd erp
   ```

2. **Set Up the `.env` File**  
   Copy the `.env.example` file and rename it to `.env`.  
   Update the following lines with your database information:
   ```env
   DB_DATABASE=your_database_name
   DB_USERNAME=your_database_user
   DB_PASSWORD=your_database_password
   ```

3. **Install Dependencies**
   ```bash
   composer install
   ```

4. **Run Migrations**
   ```bash
   php artisan migrate
   ```

5. **Serve the Application**
   ```bash
   php artisan serve
   ```

The application should now be accessible at [http://127.0.0.1:8000](http://127.0.0.1:8000) ✅
