-- Supabase (PostgreSQL) compatible database schema

CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    otp VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE review_cards (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    membership VARCHAR(50) DEFAULT 'trial',
    expiry_date TIMESTAMP DEFAULT NULL,
    details JSONB DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE INDEX idx_slug ON review_cards (slug);

CREATE TABLE reviews (
    id SERIAL PRIMARY KEY,
    card_id INT NOT NULL,
    customer_name VARCHAR(255) DEFAULT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    body TEXT DEFAULT NULL,
    details JSONB DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_card FOREIGN KEY (card_id) REFERENCES review_cards(id) ON DELETE CASCADE
);
CREATE INDEX idx_card_id ON reviews (card_id);
CREATE INDEX idx_rating ON reviews (rating);

CREATE TABLE feedbacks (
    id SERIAL PRIMARY KEY,
    card_id INT NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 3),
    status VARCHAR(50) DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_card_feedback FOREIGN KEY (card_id) REFERENCES review_cards(id) ON DELETE CASCADE
);
CREATE INDEX idx_feedback_card_id ON feedbacks (card_id);
CREATE INDEX idx_status ON feedbacks (status);

CREATE TABLE platform_settings (
    whatsapp VARCHAR(20) NOT NULL,
    details JSONB DEFAULT NULL
);

CREATE TABLE plans (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2) DEFAULT NULL,
    features JSONB,
    cta VARCHAR(255),
    duration INT DEFAULT 30,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
