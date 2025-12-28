# Simple Shopping Cart System

A Laravel-based e-commerce shopping cart application with automated background job processing for inventory management and sales reporting.

## Overview

This application implements a complete shopping cart system with real-time inventory management, automated low stock notifications, and daily sales reporting. The system uses Laravel's event-driven architecture, queue system, and scheduled tasks to handle background operations efficiently.

## Time Taken
This task was taking approximately **6 hours** to complete.

## Cart System

### Core Functionality

The shopping cart system is built around a session-based cart that persists cart items in the database using the `UserProduct` model. Key features include:

#### Cart Management (`App\Support\Cart`)
- **Add Products**: Add products to cart with quantity validation against available stock
- **Update Quantities**: Increment/decrement product quantities with stock limit enforcement
- **Remove Items**: Remove products from cart
- **Cart Totals**: Calculate total cart value and item count
- **Stock Validation**: Ensures cart quantities never exceed available stock

#### Checkout Process (`App\Actions\Cart\Checkout`)
The checkout process handles order creation atomically using database transactions:

1. **Validation**: Ensures cart is not empty before proceeding
2. **Order Creation**: Creates a new order with a unique reference number (`ORD-YYYYMMDDHHMMSS`)
3. **Order Products**: Creates order product records for each cart item
4. **Stock Management**: 
   - Decrements product stock quantities
   - Automatically adjusts quantities if requested amount exceeds available stock
   - Triggers low stock detection via Laravel Observer pattern
5. **Cart Clearing**: Removes all items from cart after successful order creation
6. **Transaction Safety**: All operations wrapped in database transaction for data integrity

### Cart Features

- **Real-time Stock Validation**: Cart quantities are validated against product stock in real-time
- **Persistent Storage**: Cart items are stored in database, persisting across sessions
- **Live Updates**: Uses Livewire for real-time cart updates without page refresh
- **Stock Limit Enforcement**: Prevents adding more items than available stock

## Background Jobs & Automation

The application implements two automated background job systems for inventory management and business intelligence:

### 1. Low Stock Notification System

**Architecture**: Event → Listener → Job → Notification

#### Components:

- **ProductObserver** (`App\Observers\ProductObserver`)
  - Monitors Product model updates
  - Automatically detects when `stock_quantity` changes and falls to 10 units or below
  - Fires `ProductStockLow` event when low stock condition is detected

- **ProductStockLow Event** (`App\Events\ProductStockLow`)
  - Carries the Product model instance
  - Dispatched automatically by the observer

- **SendLowStockNotification Listener** (`App\Listeners\SendLowStockNotification`)
  - Listens for `ProductStockLow` events
  - Implements `ShouldQueue` for asynchronous processing
  - Dispatches `SendLowStockEmailJob` to the queue

- **SendLowStockEmailJob** (`App\Jobs\SendLowStockEmailJob`)
  - Queued job that sends email notification
  - Retrieves admin user (`admin@example.com`)
  - Sends `LowStockNotification` via Laravel's notification system

- **LowStockNotification** (`App\Notifications\LowStockNotification`)
  - Email notification with product details
  - Includes product name, current stock quantity, and action link
  - Queued for asynchronous delivery

#### Flow:
```
Product Stock Updated → Observer Detects Low Stock → Event Fired → 
Listener Queues Job → Job Processes → Notification Sent via Email
```

#### Benefits:
- **Automatic Detection**: Works for any stock update (checkout, admin panel, imports, etc.)
- **Asynchronous Processing**: Uses Laravel queues to avoid blocking user requests
- **Separation of Concerns**: Business logic separated into observer, event, listener, job, and notification
- **Scalable**: Can handle multiple low stock events without performance impact

### 2. Daily Sales Report System

**Architecture**: Scheduled Job → Data Aggregation → Notification

#### Components:

- **Scheduled Task** (`routes/console.php`)
  - Configured to run daily at 8:00 PM UTC
  - Uses Laravel's task scheduler
  - Dispatches `SendDailySalesReportJob` to queue

- **SendDailySalesReportJob** (`App\Jobs\SendDailySalesReportJob`)
  - Queued job that aggregates daily sales data
  - Queries all `OrderProduct` records from orders created today
  - Groups sales by product and calculates:
    - Total quantity sold per product
    - Total revenue per product (in cents)
    - Number of orders per product
  - Calculates overall metrics:
    - Total orders for the day
    - Total items sold
    - Total revenue
  - Sends `DailySalesReportNotification` to admin user

- **DailySalesReportNotification** (`App\Notifications\DailySalesReportNotification`)
  - Email notification with comprehensive sales report
  - Includes:
    - Date of report
    - Summary statistics (total orders, items sold, revenue)
    - Detailed breakdown by product
    - Action link to view orders in application
  - Queued for asynchronous delivery

#### Flow:
```
Scheduler Triggers (Daily 8 PM UTC) → Job Queued → 
Data Aggregation → Notification Sent via Email
```

#### Report Contents:
- **Summary**: Total orders, items sold, and revenue for the day
- **Product Breakdown**: Per-product statistics including:
  - Product name
  - Units sold
  - Revenue generated
  - Number of orders containing the product
- **Action Link**: Direct link to view orders in the application

## Technical Architecture

### Queue System
- **Driver**: Database (configurable via `QUEUE_CONNECTION` environment variable)
- **Jobs**: All background jobs implement `ShouldQueue` for asynchronous processing
- **Notifications**: Email notifications are queued for efficient delivery

### Event System
- **Observer Pattern**: `ProductObserver` monitors Product model changes
- **Event-Driven**: Low stock detection uses Laravel's event system
- **Decoupled**: Components communicate through events, maintaining loose coupling

### Scheduled Tasks
- **Laravel Scheduler**: Uses `routes/console.php` for scheduled job registration
- **Cron Integration**: Requires cron job: `* * * * * cd /path-to-project && php artisan schedule:run`

## Setup & Configuration

### Prerequisites
- PHP 8.2+
- Laravel 12+
- Database (MySQL/SQLite)
- Composer
- Node.js & NPM

### Installation

1. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

2. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database Setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

4. **Build Assets**
   ```bash
   npm run build
   ```

### Queue Worker

Start the queue worker to process background jobs:
```bash
php artisan queue:work
```

### Task Scheduler

Add to crontab for scheduled tasks:
```cron
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### Mail Configuration

Configure mail settings in `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Admin User

The system expects an admin user with email `admin@example.com`. This user is created by the database seeder and receives:
- Low stock notifications
- Daily sales reports

## Email Preview (Mailbook)

The application includes Mailbook integration for previewing email notifications:

- **Access**: Navigate to `/mailbook` in your browser
- **Daily Sales Report**: Preview shows real-time data from today's sales
- **Low Stock Notification**: Can be previewed by triggering low stock condition

## Key Features Summary

✅ **Shopping Cart**
- Add/remove products
- Quantity management with stock validation
- Persistent cart storage
- Real-time updates via Livewire

✅ **Checkout Process**
- Atomic order creation
- Automatic stock decrement
- Transaction safety
- Order reference generation

✅ **Low Stock Alerts**
- Automatic detection via Observer pattern
- Asynchronous email notifications
- Event-driven architecture
- Threshold: 10 units or less

✅ **Daily Sales Reports**
- Automated daily email reports
- Comprehensive sales analytics
- Product-level breakdown
- Scheduled execution (8 PM UTC daily)

## Technologies Used

- **Laravel 12**: PHP framework
- **Livewire**: Real-time UI updates
- **Laravel Queue**: Background job processing
- **Laravel Events**: Event-driven architecture
- **Laravel Observer**: Model event monitoring
- **Laravel Notifications**: Email notifications
- **Laravel Scheduler**: Scheduled task management
- **Mailbook**: Email preview tool

## Architecture Highlights

- **Separation of Concerns**: Business logic separated into actions, jobs, observers, and notifications
- **Event-Driven**: Low stock detection uses Laravel's event system for decoupled components
- **Asynchronous Processing**: All email notifications processed via queues
- **Database Transactions**: Checkout process ensures data integrity
- **Observer Pattern**: Automatic low stock detection without modifying checkout code
- **Scheduled Tasks**: Automated daily reports without manual intervention

