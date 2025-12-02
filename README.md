# 🏭 Mini MES – Work Order Tracking System (PHP + MySQL)

A simple **Manufacturing Execution System (MES)**-style web application built using **PHP + MySQL**, designed to simulate basic production floor operations such as work order execution, operator tracking, reject logging, and production reporting.

This project was developed for learning and portfolio purposes — showcasing skills in backend PHP, SQL database design, UI layout, and simple MES workflow logic.

---

## ⭐ Features

### ➤ **Work Order Management**
- Create new Work Orders (WO):
  - WO Number  
  - Product Name  
  - Planned Quantity  
- Track WO status:  
  **NEW → IN_PROGRESS → COMPLETED**

### ➤ **Operator Login System**
- Operator authentication  
- Each operator's name is recorded when:
  - Starting a Work Order  
  - Completing a Work Order  
- Operator actions are shown in the report

### ➤ **Work Order Execution**
- Start WO → records:
  - Start time  
  - Start operator  
- Complete WO → records:
  - Good quantity  
  - Reject quantity  
  - Reject reason (from master list)  
  - Completed time  
  - Completed operator  

### ➤ **Reject Reason Module**
- Admin-style master data table (`reject_reasons`)  
- Selectable dropdown during WO completion  
- Reason displayed in reports

### ➤ **Production Report Dashboard**
Filters & Summary:
- Filter by **Date Range**  
- Filter by **Status**  
- Summary cards:
  - Total Work Orders  
  - Total Planned Quantity  
  - Total Good Output  
  - Total Reject  
  - Overall Efficiency (%) = Good / Planned * 100  
- Detailed table view of all filtered Work Orders

---

## 🧱 Tech Stack

- **PHP (Procedural)** – backend logic  
- **MySQL** – data storage  
- **HTML + CSS** – simple UI (no frameworks)  
- **XAMPP** – local development environment

---

## 📂 Project Structure

/mes_mini
│── index.php # Work Order listing & filters
│── create_wo.php # Create work order
│── start_wo.php # Start WO
│── complete_wo.php # Complete WO + reject reason
│── report.php # Production report dashboard
│── login.php # Operator login
│── logout.php # Logout
│── auth.php # Session guard
│── db.php # MySQL connection
└── screenshots/ # UI screenshots (optional)


---

## 🗄 Database Schema (Summary)

### `work_orders`
| Column | Description |
|--------|-------------|
| wo_number | Work Order ID |
| product_name | Product code/name |
| qty_planned | Planned qty |
| status | NEW / IN_PROGRESS / COMPLETED |
| started_at | Timestamp |
| completed_at | Timestamp |
| start_operator_id | FK → operators.id |
| complete_operator_id | FK → operators.id |
| reject_reason_id | FK → reject_reasons.id |
| total_good | Good output |
| total_reject | Reject qty |

### `operators`
| Column | Description |
|--------|-------------|
| username | Login ID |
| full_name | Operator name |
| password | Demo password (plain text for demo) |
| is_active | 1/0 |

### `reject_reasons`
| Column | Description |
|--------|-------------|
| code | Reject code |
| description | Reject description |
| is_active | 1/0 |

---

## 🎮 Demo Login

Username: op1
Password: password1

Username: op2
Password: password2


---

## 🚀 How to Run Locally

1. Install **XAMPP**
2. Clone repo into:  
   `C:\xampp\htdocs\mes_mini`
3. Create database:

```sql
CREATE DATABASE mes_mini CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

4. Import the tables using SQL provided in this repository (or manually create using .sql script).
5. Start Apache + MySQL from XAMPP.
6. Access in browser: http://localhost/mes_mini/login.php

<img width="1898" height="892" alt="image" src="https://github.com/user-attachments/assets/e1c7f43d-e004-42cc-aba8-478992512000" />

<img width="1892" height="885" alt="image" src="https://github.com/user-attachments/assets/3c6d391e-c31f-42a5-a645-ba3f00b493ac" />

<img width="1896" height="879" alt="image" src="https://github.com/user-attachments/assets/a79d06db-b661-4ef5-84ef-216c8703b84b" />

<img width="1889" height="886" alt="image" src="https://github.com/user-attachments/assets/e112443f-3ba5-4125-95f9-b5a247d5d428" />


---

## 📌 What I Learned From Building This Project

Throughout the development of this Mini MES system, I learned:

- 🔄 How a basic MES workflow operates  
  *(Work Order creation → start → completion → reporting)*  
- 🗄 How to design a small relational database for production tracking  
- 🔐 Handling user authentication and sessions (operator login)  
- 🔎 Building dynamic filtering (status, search, date range)  
- 🧩 Writing backend logic in PHP, including prepared statements  
- 📊 Creating a reusable reporting dashboard with summary metrics  
- 🎨 Designing clean and simple UI pages without using frameworks  

These skills strengthened my understanding of how manufacturing systems track production performance and operator activity in real factories.


---

## 💼 Why This Project Matters

This project demonstrates the ability to:

- 🧠 Understand manufacturing / MES concepts  
- ⚙ Build full-stack CRUD applications  
- 🧾 Track operator actions and production data  
- 🔁 Implement MES-like logic:  
  *status transitions, reject logging, operator traceability*  
- 🧮 Calculate efficiency and display production KPIs  
- 📊 Present data visually for production or engineering teams  

It’s a strong beginner-friendly MES project — suitable for  
**Manufacturing Engineer**, **QA**, **Industrial Systems**, or **Software Developer** portfolios.

