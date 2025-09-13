# Centralized Company Search & Reports Page

## 📌 Objective
This project implements a centralized **Company Search and Company Details system** in **Laravel + Blade + TailwindCSS**.  
It integrates with **two country-specific company databases (Singapore & Mexico)** and supports:

- Unified search across multiple databases
- Company details display with available reports
- Cart functionality with dynamic pricing per country

---

## 📂 Databases & Logic

### 1. Singapore (SG) - `companies_house_sg`
- **Tables:** `companies`, `reports`
- **Logic:**
  - All companies have all reports available.
  - Direct relationship (no foreign key).
  - Pricing comes from the `reports` table.

### 2. Mexico (MX) - `companies_house_mx`
- **Tables:** `companies (state_id)`, `states`, `reports`, `report_state`
- **Logic:**
  - Each company belongs to a state.
  - Reports depend on the company’s state.
  - `report_state` links `state_id + report_id + amount`.
  - Pricing comes from `report_state.amount`.

---

## ✅ Features & Requirements

### 1. Unified Company Search
- Search across **all databases**.
- Search by **company name** (exact or partial match, full-text = bonus).
- Results display:
  - Company name
  - Country
  - Other info (registration number, etc.)

### 2. Company Details Page
- Displays:
  - Basic Information (name, slug, identifiers, address, etc.)
  - Reports Section (country-specific logic):
    - **SG**: All reports directly linked to company
    - **MX**: Reports defined in `report_state` for the company’s `state_id`

### 3. Cart Functionality
- Add reports to a cart from company details page.
- Handle **mixed reports across countries**.
- Apply **pricing rules per country**.
- Cart displays:
  - Selected reports
  - Total price (calculated dynamically)

---

## 📦 Deliverables
- **GitHub Repository** containing Laravel codebase.
- **Hosted App Link** (any free hosting service).
- **README** with:
  - Setup instructions
  - Explanation of approach

---

## 📝 Evaluation Criteria
- **Correctness** – Data logic matches schemas.
- **Code Quality** – Clean, modular, Laravel best practices.
- **Database Handling** – Efficient multi-DB querying.
- **Frontend UX** – Intuitive search, details & cart flow (TailwindCSS best practices).
- **Scalability** – Easy to add new countries in future.

---

## 🚀 Setup Instructions

### 1. Clone Repository
```bash
git clone <your-repo-url>
cd centralized-company-search
