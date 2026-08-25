# Security Foundation

## Ownership

- **Owner:** Base Platform
- **Classification:** base-owned
- **Category:** Foundation
- **Security classification:** internal

## Purpose

Provides minimal, reusable technical security primitives explicitly excluding Identity (Auth), AccessControl (Permissions), and Observability (Audit/Logs).

It prevents duplication of common technical security behaviors like sensitive-value masking, constant-time comparison, and secure token generation.

## Primitives

### 1. SensitiveValue

An immutable wrapper for handling scalar secrets (passwords, API keys, tokens). It deliberately blocks `__toString()`, `var_dump()`, and JSON serialization. When a payload absolutely needs the secret string, it must be explicitly extracted using `$secret->reveal()`.

### 2. SecureCompare

Provides `SecureCompare::equals($a, $b)`, a constant-time comparison wrapper around `hash_equals` to prevent timing attacks.

### 3. SecureTokenGenerator

Generates cryptographically secure random bytes/hex strings via `random_bytes()`, guaranteeing secure entropy for nonces, reset codes, and API tokens. Automatically returns a `SensitiveValue`.

## Deferred Security Concepts

The following are purposefully NOT implemented here:
- **Hashing/Passwords:** Handled by Identity.
- **Encryption:** No generalized use-case yet.
- **Rate-Limiting & Security Headers:** Handled by specific HTTP infrastructure/presentation layers.

## Dependencies

- None. Security has zero internal foundation dependencies.

## Testing

```bash
composer test -- --filter=Security
```
