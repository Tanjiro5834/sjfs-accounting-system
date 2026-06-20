-- 1. CAMPUSES
CREATE TABLE campuses (
    id          TINYINT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    description VARCHAR(255),
    is_active   TINYINT DEFAULT 1
);

-- 2. USERS (moved up — sources/payables depend on this)
CREATE TABLE users (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(100) NOT NULL,
    email        VARCHAR(100) NOT NULL UNIQUE,
    password     VARCHAR(255) NOT NULL,
    role         ENUM('admin','accountant','auditor') NOT NULL,
    campus_id    TINYINT,
    is_active    TINYINT DEFAULT 1,
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (campus_id) REFERENCES campuses(id)
);

-- 3. BANK ACCOUNTS
CREATE TABLE bank_accounts (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    account_name    VARCHAR(100) NOT NULL,
    bank_name       VARCHAR(100) NOT NULL,
    account_number  VARCHAR(50),
    opening_balance DECIMAL(15,2) DEFAULT 0.00,
    campus_id       TINYINT,
    is_active       TINYINT DEFAULT 1,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (campus_id) REFERENCES campuses(id)
);

-- 4. COLLECTION TYPES
CREATE TABLE collection_types (
    id        TINYINT AUTO_INCREMENT PRIMARY KEY,
    code      VARCHAR(10) NOT NULL,
    name      VARCHAR(100) NOT NULL,
    is_active TINYINT DEFAULT 1
);

-- 5. SOURCES
CREATE TABLE sources (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    campus_id           TINYINT NOT NULL,
    collection_type_id  TINYINT NOT NULL,
    bank_account_id     INT NOT NULL,
    amount              DECIMAL(15,2) NOT NULL,
    transaction_date    DATE NOT NULL,
    remarks             VARCHAR(255),
    created_by          INT NOT NULL,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (campus_id) REFERENCES campuses(id),
    FOREIGN KEY (collection_type_id) REFERENCES collection_types(id),
    FOREIGN KEY (bank_account_id) REFERENCES bank_accounts(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- 6. PAYABLES
CREATE TABLE payables (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    payee            VARCHAR(255) NOT NULL,
    check_number     VARCHAR(50),
    bank_account_id  INT NOT NULL,
    amount           DECIMAL(15,2) NOT NULL,
    transaction_date DATE NOT NULL,
    remarks          VARCHAR(255),
    created_by       INT NOT NULL,
    created_at       DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bank_account_id) REFERENCES bank_accounts(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- 7. AUDIT LOG
CREATE TABLE audit_log (
    id          BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    action      VARCHAR(50) NOT NULL,
    module      VARCHAR(50) NOT NULL,
    record_id   INT NOT NULL,
    old_value   JSON,
    new_value   JSON,
    ip_address  VARCHAR(45),
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);