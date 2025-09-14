# Centralized Company Search & Reports System

A Laravel-based application that provides unified search functionality across multiple country-specific company databases (Singapore & Mexico) with dynamic report availability and cart functionality.

## 📋 Table of Contents

- [Overview](#overview)
- [Architecture](#architecture)
- [Features](#features)
- [Database Schema](#database-schema)
- [Installation](#installation)
- [Configuration](#configuration)
- [API Endpoints](#api-endpoints)
- [Performance Optimizations](#performance-optimizations)
- [Testing](#testing)
- [Deployment](#deployment)

## 🎯 Overview

This project implements a centralized **Company Search and Company Details system** that integrates with **two country-specific company databases** and supports:

- **Unified search** across multiple databases
- **Company details** display with available reports
- **Cart functionality** with dynamic pricing per country
- **High-performance search** optimized for millions of records

## 🏗️ Architecture

### Multi-Database Design

The application uses **multiple MySQL database connections** to separate country-specific data:

```
├── Default Database (mysql)
│   └── Laravel's internal tables (users, sessions, etc.)
├── Singapore Database (companies_house_sg)
│   ├── companies table
│   └── reports table
└── Mexico Database (companies_house_mx)
    ├── companies table
    ├── states table
    ├── reports table
    └── report_state table
```

### Country-Specific Logic

#### Singapore (SG) Logic
- **Simple relationship**: All companies have access to all reports
- **Direct pricing**: Pricing comes directly from the `reports` table
- **No state dependency**: Companies are not tied to specific states

#### Mexico (MX) Logic
- **State-based reports**: Each company belongs to a state
- **Dynamic pricing**: Reports and pricing depend on the company's state
- **Complex relationship**: `report_state` table links `state_id + report_id + amount`

## 🚀 Features

### 1. Unified Company Search
- **Cross-database search**: Search across Singapore and Mexico databases simultaneously
- **Partial matching**: Supports substring matching (e.g., "tech" matches "technology")
- **Relevance scoring**: Results ranked by exact match, starts with, contains, etc.
- **Performance optimized**: Raw SQL queries with caching for fast response times

### 2. Company Details & Reports
- **Country-specific logic**: Different report availability rules per country
- **Dynamic pricing**: Pricing varies by country and state (Mexico)
- **Comprehensive information**: Company details, registration numbers, addresses

### 3. High-Performance Search
- **Raw SQL optimization**: Direct database queries for maximum performance
- **Caching layer**: Redis/Memory caching for search results
- **Index optimization**: Database indexes for fast lookups
- **Scalable design**: Handles millions of records efficiently

## 📊 Performance Benchmarks

- **Small datasets** (< 1,000 records): < 50ms response time
- **Medium datasets** (1,000-100,000 records): < 200ms response time
- **Large datasets** (> 100,000 records): < 500ms response time
- **Cached results**: < 10ms response time

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request