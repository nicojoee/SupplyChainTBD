-- =====================================================
-- Supply Chain TBD - Complete Database Schema
-- Generated: 2024-12-24
-- Database: Microsoft SQL Server (SSMS Compatible)
-- =====================================================

-- =====================================================
-- CREATE DATABASE
-- =====================================================
-- Uncomment the following if running on master database
-- Make sure you have permission to create databases

USE [master];
GO

-- Drop database if exists (CAREFUL: This will delete all data!)
IF EXISTS (SELECT name FROM sys.databases WHERE name = N'SupplyChainTBD')
BEGIN
    ALTER DATABASE [SupplyChainTBD] SET SINGLE_USER WITH ROLLBACK IMMEDIATE;
    DROP DATABASE [SupplyChainTBD];
END
GO

-- Create new database
CREATE DATABASE [SupplyChainTBD]
COLLATE Latin1_General_CI_AS;
GO

-- Use the new database
USE [SupplyChainTBD];
GO

-- =====================================================
-- DROP EXISTING TABLES (if any)
-- =====================================================
IF OBJECT_ID('broadcast_messages', 'U') IS NOT NULL DROP TABLE broadcast_messages;
IF OBJECT_ID('messages', 'U') IS NOT NULL DROP TABLE messages;
IF OBJECT_ID('conversations', 'U') IS NOT NULL DROP TABLE conversations;
IF OBJECT_ID('order_items', 'U') IS NOT NULL DROP TABLE order_items;
IF OBJECT_ID('orders', 'U') IS NOT NULL DROP TABLE orders;
IF OBJECT_ID('distributor_stocks', 'U') IS NOT NULL DROP TABLE distributor_stocks;
IF OBJECT_ID('factory_products', 'U') IS NOT NULL DROP TABLE factory_products;
IF OBJECT_ID('supplier_products', 'U') IS NOT NULL DROP TABLE supplier_products;
IF OBJECT_ID('products', 'U') IS NOT NULL DROP TABLE products;
IF OBJECT_ID('couriers', 'U') IS NOT NULL DROP TABLE couriers;
IF OBJECT_ID('distributors', 'U') IS NOT NULL DROP TABLE distributors;
IF OBJECT_ID('factories', 'U') IS NOT NULL DROP TABLE factories;
IF OBJECT_ID('suppliers', 'U') IS NOT NULL DROP TABLE suppliers;
IF OBJECT_ID('sessions', 'U') IS NOT NULL DROP TABLE sessions;
IF OBJECT_ID('password_reset_tokens', 'U') IS NOT NULL DROP TABLE password_reset_tokens;
IF OBJECT_ID('cache_locks', 'U') IS NOT NULL DROP TABLE cache_locks;
IF OBJECT_ID('cache', 'U') IS NOT NULL DROP TABLE cache;
IF OBJECT_ID('failed_jobs', 'U') IS NOT NULL DROP TABLE failed_jobs;
IF OBJECT_ID('job_batches', 'U') IS NOT NULL DROP TABLE job_batches;
IF OBJECT_ID('jobs', 'U') IS NOT NULL DROP TABLE jobs;
IF OBJECT_ID('users', 'U') IS NOT NULL DROP TABLE users;
IF OBJECT_ID('migrations', 'U') IS NOT NULL DROP TABLE migrations;
GO

-- =====================================================
-- TABLE: users
-- =====================================================
CREATE TABLE [users] (
    [id] BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    [google_id] NVARCHAR(255) NULL,
    [name] NVARCHAR(255) NOT NULL,
    [email] NVARCHAR(255) NOT NULL UNIQUE,
    [avatar] NVARCHAR(255) NULL,
    [role] NVARCHAR(50) NOT NULL DEFAULT 'supplier' CHECK ([role] IN ('superadmin', 'supplier', 'factory', 'distributor', 'courier')),
    [email_verified_at] DATETIME2 NULL,
    [password] NVARCHAR(255) NULL,
    [remember_token] NVARCHAR(100) NULL,
    [created_at] DATETIME2 NULL,
    [updated_at] DATETIME2 NULL
);
GO

-- =====================================================
-- TABLE: password_reset_tokens
-- =====================================================
CREATE TABLE [password_reset_tokens] (
    [email] NVARCHAR(255) NOT NULL PRIMARY KEY,
    [token] NVARCHAR(255) NOT NULL,
    [created_at] DATETIME2 NULL
);
GO

-- =====================================================
-- TABLE: sessions
-- =====================================================
CREATE TABLE [sessions] (
    [id] NVARCHAR(255) NOT NULL PRIMARY KEY,
    [user_id] BIGINT NULL,
    [ip_address] NVARCHAR(45) NULL,
    [user_agent] NVARCHAR(MAX) NULL,
    [payload] NVARCHAR(MAX) NOT NULL,
    [last_activity] INT NOT NULL
);
CREATE INDEX [sessions_user_id_index] ON [sessions] ([user_id]);
CREATE INDEX [sessions_last_activity_index] ON [sessions] ([last_activity]);
GO

-- =====================================================
-- TABLE: cache
-- =====================================================
CREATE TABLE [cache] (
    [key] NVARCHAR(255) NOT NULL PRIMARY KEY,
    [value] NVARCHAR(MAX) NOT NULL,
    [expiration] INT NOT NULL
);
GO

CREATE TABLE [cache_locks] (
    [key] NVARCHAR(255) NOT NULL PRIMARY KEY,
    [owner] NVARCHAR(255) NOT NULL,
    [expiration] INT NOT NULL
);
GO

-- =====================================================
-- TABLE: jobs (queue)
-- =====================================================
CREATE TABLE [jobs] (
    [id] BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    [queue] NVARCHAR(255) NOT NULL,
    [payload] NVARCHAR(MAX) NOT NULL,
    [attempts] TINYINT NOT NULL,
    [reserved_at] INT NULL,
    [available_at] INT NOT NULL,
    [created_at] INT NOT NULL
);
CREATE INDEX [jobs_queue_index] ON [jobs] ([queue]);
GO

CREATE TABLE [job_batches] (
    [id] NVARCHAR(255) NOT NULL PRIMARY KEY,
    [name] NVARCHAR(255) NOT NULL,
    [total_jobs] INT NOT NULL,
    [pending_jobs] INT NOT NULL,
    [failed_jobs] INT NOT NULL,
    [failed_job_ids] NVARCHAR(MAX) NOT NULL,
    [options] NVARCHAR(MAX) NULL,
    [cancelled_at] INT NULL,
    [created_at] INT NOT NULL,
    [finished_at] INT NULL
);
GO

CREATE TABLE [failed_jobs] (
    [id] BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    [uuid] NVARCHAR(255) NOT NULL UNIQUE,
    [connection] NVARCHAR(MAX) NOT NULL,
    [queue] NVARCHAR(MAX) NOT NULL,
    [payload] NVARCHAR(MAX) NOT NULL,
    [exception] NVARCHAR(MAX) NOT NULL,
    [failed_at] DATETIME2 NOT NULL DEFAULT GETDATE()
);
GO

-- =====================================================
-- TABLE: suppliers
-- =====================================================
CREATE TABLE [suppliers] (
    [id] BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    [user_id] BIGINT NOT NULL,
    [name] NVARCHAR(255) NOT NULL,
    [description] NVARCHAR(MAX) NULL,
    [address] NVARCHAR(255) NOT NULL,
    [latitude] DECIMAL(10, 8) NOT NULL,
    [longitude] DECIMAL(11, 8) NOT NULL,
    [phone] NVARCHAR(255) NULL,
    [email] NVARCHAR(255) NULL,
    [created_at] DATETIME2 NULL,
    [updated_at] DATETIME2 NULL,
    CONSTRAINT [FK_suppliers_user] FOREIGN KEY ([user_id]) REFERENCES [users]([id]) ON DELETE CASCADE
);
GO

-- =====================================================
-- TABLE: factories
-- =====================================================
CREATE TABLE [factories] (
    [id] BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    [user_id] BIGINT NOT NULL,
    [name] NVARCHAR(255) NOT NULL,
    [description] NVARCHAR(MAX) NULL,
    [address] NVARCHAR(255) NOT NULL,
    [latitude] DECIMAL(10, 8) NOT NULL,
    [longitude] DECIMAL(11, 8) NOT NULL,
    [phone] NVARCHAR(255) NULL,
    [email] NVARCHAR(255) NULL,
    [production_capacity] INT NOT NULL DEFAULT 0,
    [created_at] DATETIME2 NULL,
    [updated_at] DATETIME2 NULL,
    CONSTRAINT [FK_factories_user] FOREIGN KEY ([user_id]) REFERENCES [users]([id]) ON DELETE CASCADE
);
GO

-- =====================================================
-- TABLE: distributors
-- =====================================================
CREATE TABLE [distributors] (
    [id] BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    [user_id] BIGINT NOT NULL,
    [name] NVARCHAR(255) NOT NULL,
    [description] NVARCHAR(MAX) NULL,
    [address] NVARCHAR(255) NOT NULL,
    [latitude] DECIMAL(10, 8) NOT NULL,
    [longitude] DECIMAL(11, 8) NOT NULL,
    [phone] NVARCHAR(255) NULL,
    [email] NVARCHAR(255) NULL,
    [warehouse_capacity] INT NOT NULL DEFAULT 0,
    [created_at] DATETIME2 NULL,
    [updated_at] DATETIME2 NULL,
    CONSTRAINT [FK_distributors_user] FOREIGN KEY ([user_id]) REFERENCES [users]([id]) ON DELETE CASCADE
);
GO

-- =====================================================
-- TABLE: couriers
-- =====================================================
CREATE TABLE [couriers] (
    [id] BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    [user_id] BIGINT NOT NULL,
    [name] NVARCHAR(255) NOT NULL,
    [vehicle_type] NVARCHAR(255) NULL,
    [vehicle_capacity] INT NULL,
    [license_plate] NVARCHAR(255) NULL,
    [phone] NVARCHAR(255) NULL,
    [current_latitude] DECIMAL(10, 8) NULL,
    [current_longitude] DECIMAL(11, 8) NULL,
    [is_gps_active] BIT NOT NULL DEFAULT 1,
    [location_updated_at] DATETIME2 NULL,
    [status] NVARCHAR(50) NOT NULL DEFAULT 'offline' CHECK ([status] IN ('available', 'busy', 'offline', 'idle')),
    [created_at] DATETIME2 NULL,
    [updated_at] DATETIME2 NULL,
    CONSTRAINT [FK_couriers_user] FOREIGN KEY ([user_id]) REFERENCES [users]([id]) ON DELETE CASCADE
);
GO

-- =====================================================
-- TABLE: products
-- =====================================================
CREATE TABLE [products] (
    [id] BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    [name] NVARCHAR(255) NOT NULL,
    [description] NVARCHAR(MAX) NULL,
    [category] NVARCHAR(255) NULL,
    [unit] NVARCHAR(255) NOT NULL DEFAULT 'pcs',
    [base_price] DECIMAL(12, 2) NOT NULL DEFAULT 0,
    [created_at] DATETIME2 NULL,
    [updated_at] DATETIME2 NULL
);
GO

-- =====================================================
-- TABLE: supplier_products
-- =====================================================
CREATE TABLE [supplier_products] (
    [id] BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    [supplier_id] BIGINT NOT NULL,
    [product_id] BIGINT NOT NULL,
    [price] DECIMAL(12, 2) NOT NULL,
    [stock_quantity] DECIMAL(12, 2) NOT NULL DEFAULT 0,
    [created_at] DATETIME2 NULL,
    [updated_at] DATETIME2 NULL,
    CONSTRAINT [UQ_supplier_products] UNIQUE ([supplier_id], [product_id]),
    CONSTRAINT [FK_supplier_products_supplier] FOREIGN KEY ([supplier_id]) REFERENCES [suppliers]([id]) ON DELETE CASCADE,
    CONSTRAINT [FK_supplier_products_product] FOREIGN KEY ([product_id]) REFERENCES [products]([id]) ON DELETE CASCADE
);
GO

-- =====================================================
-- TABLE: factory_products
-- =====================================================
CREATE TABLE [factory_products] (
    [id] BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    [factory_id] BIGINT NOT NULL,
    [product_id] BIGINT NOT NULL,
    [price] DECIMAL(12, 2) NOT NULL,
    [production_quantity] DECIMAL(12, 2) NOT NULL DEFAULT 0,
    [created_at] DATETIME2 NULL,
    [updated_at] DATETIME2 NULL,
    CONSTRAINT [UQ_factory_products] UNIQUE ([factory_id], [product_id]),
    CONSTRAINT [FK_factory_products_factory] FOREIGN KEY ([factory_id]) REFERENCES [factories]([id]) ON DELETE CASCADE,
    CONSTRAINT [FK_factory_products_product] FOREIGN KEY ([product_id]) REFERENCES [products]([id]) ON DELETE CASCADE
);
GO

-- =====================================================
-- TABLE: distributor_stocks
-- =====================================================
CREATE TABLE [distributor_stocks] (
    [id] BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    [distributor_id] BIGINT NOT NULL,
    [product_id] BIGINT NOT NULL,
    [quantity] DECIMAL(12, 2) NOT NULL DEFAULT 0,
    [min_stock_level] INT NOT NULL DEFAULT 0,
    [created_at] DATETIME2 NULL,
    [updated_at] DATETIME2 NULL,
    CONSTRAINT [UQ_distributor_stocks] UNIQUE ([distributor_id], [product_id]),
    CONSTRAINT [FK_distributor_stocks_distributor] FOREIGN KEY ([distributor_id]) REFERENCES [distributors]([id]) ON DELETE CASCADE,
    CONSTRAINT [FK_distributor_stocks_product] FOREIGN KEY ([product_id]) REFERENCES [products]([id]) ON DELETE CASCADE
);
GO

-- =====================================================
-- TABLE: orders
-- =====================================================
CREATE TABLE [orders] (
    [id] BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    [order_number] NVARCHAR(255) NOT NULL UNIQUE,
    [buyer_type] NVARCHAR(50) NOT NULL CHECK ([buyer_type] IN ('factory', 'distributor')),
    [buyer_id] BIGINT NOT NULL,
    [seller_type] NVARCHAR(50) NOT NULL CHECK ([seller_type] IN ('supplier', 'factory')),
    [seller_id] BIGINT NOT NULL,
    [status] NVARCHAR(50) NOT NULL DEFAULT 'pending' CHECK ([status] IN ('pending', 'confirmed', 'pickup', 'processing', 'shipped', 'delivered', 'cancelled')),
    [total_amount] DECIMAL(14, 2) NOT NULL DEFAULT 0,
    [total_quantity] DECIMAL(12, 2) NOT NULL DEFAULT 0,
    [delivered_quantity] DECIMAL(12, 2) NOT NULL DEFAULT 0,
    [courier_id] BIGINT NULL,
    [courier_accepted_at] DATETIME2 NULL,
    [notes] NVARCHAR(MAX) NULL,
    [created_at] DATETIME2 NULL,
    [updated_at] DATETIME2 NULL,
    CONSTRAINT [FK_orders_courier] FOREIGN KEY ([courier_id]) REFERENCES [couriers]([id]) ON DELETE SET NULL
);
CREATE INDEX [orders_buyer_index] ON [orders] ([buyer_type], [buyer_id]);
CREATE INDEX [orders_seller_index] ON [orders] ([seller_type], [seller_id]);
GO

-- =====================================================
-- TABLE: order_items
-- =====================================================
CREATE TABLE [order_items] (
    [id] BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    [order_id] BIGINT NOT NULL,
    [product_id] BIGINT NOT NULL,
    [quantity] DECIMAL(12, 2) NOT NULL,
    [unit_price] DECIMAL(12, 2) NOT NULL,
    [subtotal] DECIMAL(14, 2) NOT NULL,
    [created_at] DATETIME2 NULL,
    [updated_at] DATETIME2 NULL,
    CONSTRAINT [FK_order_items_order] FOREIGN KEY ([order_id]) REFERENCES [orders]([id]) ON DELETE CASCADE,
    CONSTRAINT [FK_order_items_product] FOREIGN KEY ([product_id]) REFERENCES [products]([id]) ON DELETE CASCADE
);
GO

-- =====================================================
-- TABLE: conversations (chat)
-- =====================================================
CREATE TABLE [conversations] (
    [id] BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    [user_one] BIGINT NOT NULL,
    [user_two] BIGINT NOT NULL,
    [last_message_at] DATETIME2 NULL,
    [created_at] DATETIME2 NULL,
    [updated_at] DATETIME2 NULL,
    CONSTRAINT [UQ_conversations] UNIQUE ([user_one], [user_two]),
    CONSTRAINT [FK_conversations_user_one] FOREIGN KEY ([user_one]) REFERENCES [users]([id]) ON DELETE NO ACTION,
    CONSTRAINT [FK_conversations_user_two] FOREIGN KEY ([user_two]) REFERENCES [users]([id]) ON DELETE NO ACTION
);
GO

-- =====================================================
-- TABLE: messages (chat)
-- =====================================================
CREATE TABLE [messages] (
    [id] BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    [conversation_id] BIGINT NOT NULL,
    [sender_id] BIGINT NOT NULL,
    [message] NVARCHAR(MAX) NULL,
    [image_path] NVARCHAR(MAX) NULL,
    [is_read] BIT NOT NULL DEFAULT 0,
    [is_deleted] BIT NOT NULL DEFAULT 0,
    [created_at] DATETIME2 NULL,
    [updated_at] DATETIME2 NULL,
    CONSTRAINT [FK_messages_conversation] FOREIGN KEY ([conversation_id]) REFERENCES [conversations]([id]) ON DELETE CASCADE,
    CONSTRAINT [FK_messages_sender] FOREIGN KEY ([sender_id]) REFERENCES [users]([id]) ON DELETE NO ACTION
);
CREATE INDEX [messages_conversation_index] ON [messages] ([conversation_id], [created_at]);
GO

-- =====================================================
-- TABLE: broadcast_messages
-- =====================================================
CREATE TABLE [broadcast_messages] (
    [id] BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    [sender_id] BIGINT NOT NULL,
    [message] NVARCHAR(MAX) NULL,
    [image_path] NVARCHAR(MAX) NULL,
    [file_path] NVARCHAR(MAX) NULL,
    [file_name] NVARCHAR(255) NULL,
    [file_type] NVARCHAR(255) NULL,
    [created_at] DATETIME2 NULL,
    [updated_at] DATETIME2 NULL,
    CONSTRAINT [FK_broadcast_messages_sender] FOREIGN KEY ([sender_id]) REFERENCES [users]([id]) ON DELETE CASCADE
);
CREATE INDEX [broadcast_messages_created_at_index] ON [broadcast_messages] ([created_at]);
GO

-- =====================================================
-- TABLE: migrations (Laravel)
-- =====================================================
CREATE TABLE [migrations] (
    [id] INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    [migration] NVARCHAR(255) NOT NULL,
    [batch] INT NOT NULL
);
GO

-- =====================================================
-- SEED DATA: Default Products
-- =====================================================
SET IDENTITY_INSERT [products] ON;
INSERT INTO [products] ([id], [name], [description], [category], [unit], [base_price], [created_at], [updated_at]) VALUES
(1, N'Raw Palm Oil', N'Unrefined palm oil from palm fruit', N'Raw Material', N'ton', 500000.00, GETDATE(), GETDATE()),
(2, N'Refined Cooking Oil', N'Processed cooking oil ready for distribution', N'Finished Product', N'ton', 750000.00, GETDATE(), GETDATE()),
(3, N'Palm Kernel', N'Palm kernel for oil extraction', N'Raw Material', N'ton', 400000.00, GETDATE(), GETDATE()),
(4, N'Vegetable Oil Blend', N'Mixed vegetable cooking oil', N'Finished Product', N'ton', 680000.00, GETDATE(), GETDATE()),
(5, N'Premium Cooking Oil', N'High-quality refined cooking oil', N'Finished Product', N'ton', 850000.00, GETDATE(), GETDATE());
SET IDENTITY_INSERT [products] OFF;
GO

-- =====================================================
-- END OF SCHEMA
-- =====================================================
PRINT 'Database schema created successfully!';
GO
