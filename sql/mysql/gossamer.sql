CREATE TABLE gossamer_meta (
    version TEXT DEFAULT '1.0.0',
    lasthash TEXT
);

-- Table of providers (id => name)
CREATE TABLE gossamer_providers (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name TEXT UNIQUE,
    PRIMARY KEY (id)
);

-- Public keys for each provider
CREATE TABLE gossamer_provider_publickeys (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    provider BIGINT REFERENCES gossamer_providers (id),
    publickey CHAR(64), -- Hex-encoded,
    ledgerhash TEXT,
    revokehash TEXT NULL,
    limited BOOLEAN DEFAULT FALSE,
    purpose TEXT NULL,
    metadata TEXT,
    revoked BOOLEAN DEFAULT FALSE,
    PRIMARY KEY (id)
);

-- Packages (Plugins / Themes)
CREATE TABLE gossamer_packages (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    provider BIGINT REFERENCES gossamer_providers (id),
    name TEXT,
    metadata TEXT,
    PRIMARY KEY (id)
);
CREATE UNIQUE INDEX gossamer_package_name_index
    ON gossamer_packages (provider, name);

-- Versioned releases for each package
CREATE TABLE gossamer_package_releases (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    package BIGINT REFERENCES gossamer_packages (id),
    publickey BIGINT REFERENCES gossamer_provider_publickeys (id),
    version TEXT,
    artifact TEXT NULL,
    signature TEXT,
    ledgerhash TEXT,
    revokehash TEXT NULL,
    revoked BOOLEAN DEFAULT FALSE,
    metadata TEXT,
    PRIMARY KEY (id)
);
CREATE UNIQUE INDEX gossamer_release_version_index
    ON gossamer_package_releases (package, publickey, version, artifact);

-- Gossamer metadata table
CREATE TABLE gossamer_meta (
   version TEXT DEFAULT '1.0.0',
   lasthash TEXT
);

CREATE TABLE gossamer_package_release_attestations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    release_id BIGINT REFERENCES gossamer_package_releases (id),
    attestor BIGINT REFERENCES gossamer_providers (id),
    attestation TEXT, -- 'reproduced', 'spot-check', 'code-review', 'sec-audit'
    ledgerhash TEXT,
    metadata TEXT,
    PRIMARY KEY (id)
);
