# Base Specialized: Outbound Webhooks

Provides zero-dependency, generic outbound HTTP webhook dispatch capabilities.

## Responsibility
- Synchronous POST JSON delivery.
- No delivery history, persistence, or queuing.
- Secure failure mapping protecting against payload/PII leaks in logs.

## Endpoint & Security
- **SSRF**: This package does **not** guarantee SSRF protection. Webhook endpoints are structurally validated, but blocking private network egress (e.g. `127.0.0.1`) requires explicit infrastructure/network policies.
- **HTTP/HTTPS**: HTTPS is strongly expected for production. HTTP is structurally allowed to support local development environments where deployment policies permit.
- **Credentials**: Endpoints MUST NOT contain embedded credentials (e.g., `user:pass@`). Note that query-string secrets are inherently unsafe as URLs are often logged. Custom headers via `WebhookHeaders` is the required approach for authentication.
- **Redirects**: Redirects are explicitly disabled by the transport to prevent secondary SSRF routing bypasses.

## Dispatch Semantics
The dispatcher completes exactly ONE synchronous delivery attempt. It does not retry or queue.
Any `200 <= status <= 299` is considered a success. All other statuses throw a `WebhookDispatchFailed` exception which strips the response body.

## Dependencies
Zero Base dependencies.
