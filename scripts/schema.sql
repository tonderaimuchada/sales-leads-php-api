
CREATE TABLE IF NOT EXISTS users (
    id BIGSERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    emailAddress VARCHAR(100) UNIQUE NOT NULL,
    role VARCHAR(20) DEFAULT 'user',
    dateCreated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE IF NOT EXISTS leads
(
    id INTEGER PRIMARY KEY GENERATED ALWAYS AS IDENTITY
    fullName VARCHAR(255) COLLATE pg_catalog."default" NOT NULL,
    emailAddress VARCHAR(150) UNIQUE NOT NULL,
    phoneNumber VARCHAR(30),
    companyName VARCHAR(255),
    status VARCHAR(50) DEFAULT 'NEW',
    dateCreated date NOT NULL,
    dateModified date
)

TABLESPACE pg_default;

ALTER TABLE IF EXISTS leads
    OWNER to postgres;


CREATE INDEX IF NOT EXISTS idx_leads_status ON leads(status);
CREATE INDEX IF NOT EXISTS idx_leads_email ON leads(emailAddress);