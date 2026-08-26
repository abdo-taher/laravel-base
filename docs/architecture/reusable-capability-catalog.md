# Reusable Capability Catalog

This catalog defines the machine-readable target dependencies for Project Factory resolution.

## 1. Media
- **Name**: `media.attachments`
- **Layer**: Platform
- **Status**: Implemented
- **Priority**: P0
- **Provides**: `media.attachments`
- **Required capabilities**: `files.storage`
- **Optional capabilities**: none
- **Persistence**: package-owned
- **Framework dependency**: Laravel Eloquent (for polymorphic relations)
- **Reason**: Reusable upload-reference + polymorphic attachment lifecycle. Essential for standard Product modules.

## 2. Verification (OTP)
- **Name**: `verification.codes`
- **Layer**: Platform
- **Status**: Implemented
- **Priority**: P1
- **Provides**: `verification.codes`
- **Required capabilities**: none
- **Optional capabilities**: none
- **Persistence**: package-owned
- **Framework dependency**: none
- **Reason**: Target-agnostic OTP lifecycle (generation, limits, expiration).

## 3. Device Management
- **Name**: `devices.manage`
- **Layer**: Platform
- **Status**: Implemented
- **Priority**: P1
- **Provides**: `devices.manage`
- **Required capabilities**: `identity.current-principal`
- **Optional capabilities**: none
- **Persistence**: package-owned
- **Framework dependency**: Laravel Eloquent
- **Reason**: Manages device push tokens, invalidation, and platform tracking tied to Identity Principals.
