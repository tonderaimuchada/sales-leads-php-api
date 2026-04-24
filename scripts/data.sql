-- Default admin user (password: admin123)
INSERT INTO users (username, password, email, role)
VALUES ('admin', '$2a$10$N.zmdr9k7uOCQb376NoUnuTJ8iAt6Z5EHsM8lE9lBaLYJbDEaRHAe', 'admin@leads.com', 'ADMIN')
ON CONFLICT (username) DO NOTHING;


-- Sample leads
INSERT INTO leads (fullName, emailAddress, phoneNumber, companyName, status, dateCreated)
VALUES
  ('Alice Johnson', 'alice@comm.com', '+27-0101', 'Instacom', 'NEW', CURRENT_TIMESTAMP),
  ('Bob Smith', 'bob@pay.io', '+27-0102', 'InstaPay', 'CONTACTED', CURRENT_TIMESTAMP),
  ('Carol White', 'carol@radio.net', '+27-0103', 'InstaRadio', 'QUALIFIED', CURRENT_TIMESTAMP)
ON CONFLICT (emailAddress) DO NOTHING;
