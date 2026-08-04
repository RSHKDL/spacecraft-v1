-- Dedicated role and database for the test suite.
--
-- Runs once, when the Postgres data directory is first initialised. Recreating
-- the `db_data` volume is what replays it.
--
-- The point is to make the test credential genuinely non-sensitive so it can be
-- committed in .env.test: it owns spacecraft_test and nothing else. A test run
-- cannot reach development data even by accident.

CREATE ROLE spacecraft_test WITH LOGIN PASSWORD 'iamnotasecret';

CREATE DATABASE spacecraft_test OWNER spacecraft_test;

-- PostgreSQL grants CONNECT on every database to PUBLIC by default, which would
-- let the test role open the development database. The owner keeps full access.
REVOKE CONNECT ON DATABASE spacecraft FROM PUBLIC;