# Security Policy

## Baseline Rules

- Secrets never belong in Git.
- Authentication fails closed.
- Authorization fails closed.
- Tenant isolation fails closed.
- Encryption failures must never silently downgrade.
- Production changes require explicit approval.
- Sensitive values must never appear in logs.
- Dependencies must be security-audited.
- Generated projects must use secure defaults.

## Repository Restrictions

Do not commit:

- .env files
- credentials
- private keys
- certificates containing private material
- database dumps
- local SQLite databases
- runtime logs
- caches
