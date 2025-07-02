# 📦 Inventory Management System

This is a **web-based Inventory Management System** built using **PHP**, **MySQL**, and **JavaScript**. It allows users to manage inventory, track stock levels, and process sales.

---

## 📋 Prerequisites

Before setting up the project, make sure the following are installed:

- [XAMPP](https://www.apachefriends.org/index.html) – Local server environment (Apache & MySQL)
- [Git](https://git-scm.com/downloads) – To clone the repository
- A modern web browser (Chrome, Firefox, Edge, etc.)

---

## 🚀 Installation & Setup

### Step 1: Clone the Repository

1. Open Command Prompt or Terminal.
2. Navigate to your `htdocs` directory (usually `C:\xampp\htdocs` on Windows):

   ```bash
   cd C:\xampp\htdocs
   ```

3. Clone the repository:

   ```bash
   git clone https://github.com/Nilanjan940/inventory-management-system.git
   ```


---

### Step 2: Import the Database

1. Start **XAMPP** and ensure **Apache** and **MySQL** are running.
2. Open [phpMyAdmin](http://localhost/phpmyadmin) in your browser.
3. Create a **new database** named:

   ```
   inventorymanagementsystem
   ```

4. Select the database, then go to the **Import** tab.
5. Choose the file `inventorymanagementsystem.sql` located inside the project folder.
6. Click **Go** to import the data.

---

### Step 3: Configure Database Connection

1. Open the project folder:

   ```
   C:\xampp\htdocs\inventory-management-system
   ```

2. Locate and edit `config.php` or `db.php`.

3. Ensure the database credentials are as follows:

   ```php
   <?php
   $servername = "localhost";
   $username = "root";
   $password = "";
   $dbname = "inventorymanagementsystem";
   ?>
   ```

---

### Step 4: Launch the Application

1. Make sure XAMPP is running.
2. Open your browser and go to:

   ```
   http://localhost/inventory-management-system/Login.php
   ```

---

### Step 5: Login Credentials

Use the default credentials:

- **Username:** `nilanjan`  
- **Password:** `1234`

---

## 🛠 Troubleshooting

- **Page Not Loading?**
  - Ensure XAMPP is running.
  - Verify both **Apache** and **MySQL** services are active.

- **Error: #1046 - No database selected**
  - This means you tried importing the SQL file without first creating the database. Be sure to **create the database before importing**.

---


