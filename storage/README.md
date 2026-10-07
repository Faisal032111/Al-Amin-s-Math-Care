# Al Amin's Math Care — Storage Directory

This directory contains runtime-generated data.
It must NOT be publicly accessible.

## Structure

- `logs/`        — Error logs, security logs, DB error logs
- `leads/`       — Fallback lead storage (JSON) when DB is unavailable
- `rate_limits/` — IP-based rate limit tracking files

## Security Note

The root `.htaccess` blocks browser access to `/storage/`.
Ensure this directory has permission 755 on Linux.
