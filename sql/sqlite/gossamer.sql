-- Table of providers (id => name)
CREATE TABLE gossamer_providers (
    id INTEGER NOT NULL PRIMARY KEY,
    name TEXT UNIQUE
);

-- Public keys for each provider
CREATE TABLE gossamer_provider_publickeys (
    id INTEGER NOT NULL PRIMARY KEY,
    provider INTEGER REFERENCES gossamer_providers (id),
    publickey CHAR(64), -- Hex-encoded,
    ledgerhash TEXT,
    revokehash TEXT NULL,
    limited BOOLEAN DEFAULT FALSE,
    purpose TEXT NULL,
    metadata TEXT,
    revoked BOOLEAN DEFAULT FALSE
);

-- Packages (Plugins / Themes)
CREATE TABLE gossamer_packages (
    id INTEGER NOT NULL PRIMARY KEY,
    provider INTEGER REFERENCES gossamer_providers (id),
    name TEXT,
    metadata TEXT
);
CREATE UNIQUE INDEX gossamer_package_name_index
    ON gossamer_packages (provider, name);

-- Versioned releases for each package
CREATE TABLE gossamer_package_releases (
    id INTEGER NOT NULL PRIMARY KEY,
    package INTEGER REFERENCES gossamer_packages (id),
    publickey INTEGER REFERENCES gossamer_provider_publickeys (id),
    version TEXT,
    signature TEXT,
    ledgerhash TEXT,
    revokehash TEXT NULL,
    revoked BOOLEAN DEFAULT FALSE,
    metadata TEXT
);
CREATE UNIQUE INDEX gossamer_release_version_index
    ON gossamer_package_releases (package, publickey, version);

-- Gossamer metadata table
CREATE TABLE gossamer_meta (
    version TEXT DEFAULT '1.0.0',
    lasthash TEXT
);

CREATE TABLE gossamer_package_release_attestations (
    id INTEGER NOT NULL PRIMARY KEY,
    release_id INTEGER REFERENCES gossamer_package_releases (id),
    attestor INTEGER REFERENCES gossamer_providers (id),
    attestation TEXT, -- 'reproduced', 'spot-check', 'code-review', 'sec-audit'
    ledgerhash TEXT,
    metadata TEXT
);
