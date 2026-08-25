# Files (Platform)

Provides a generic, framework-independent, domain-neutral file and object storage capability. 

## Responsibility
The Files package acts as the canonical boundary between the application and the underlying storage infrastructure (e.g., local disk, AWS S3). It represents stored file identity, storage location, upload/store, read/retrieve, existence checks, and deletion.

It explicitly **does not** own:
- Product media concepts (images, avatars, collections).
- Ownership boundaries (`user_id`, `tenant_id`).
- Database tracking/persistence of files (unless added via a separate metadata adapter later).

## Persistence Decision
The B4.2 core implementation operates strictly against the underlying storage backend. **No database tables or migrations are required.** The storage infrastructure itself (e.g., S3 keys) acts as the source of truth for existence and retrieval.

## Public API
The primary interfaces reside in `Public\Contracts\`:
- `FileReader`: Provides `read(StorageKey)`, `readStream(StorageKey)`, and `exists(StorageKey)`.
- `FileWriter`: Provides `write(StorageKey, string|resource)` and `delete(StorageKey)`.
- `FileStorage`: A unified combination of Reader and Writer.

## Storage Identity Model
The package uses the `StorageKey` value object to represent the logical identity and path of a file.
- It strictly prevents path traversal (`../`).
- It prevents absolute paths (keys must be logical and relative).
- It prevents null bytes (`\0`).

## Content and Stream Model
To safely support large file operations without exhausting memory:
- `write()` accepts a string payload or a PHP `resource` stream.
- `readStream()` returns a PHP `resource` stream (caller is responsible for closing it if necessary, though PHP often handles this natively on garbage collection).

## Visibility Model
Files can be written with an explicit `FileVisibility` enum (`PRIVATE` or `PUBLIC`). This controls storage-level ACLs (like S3 object visibility). This is **not** AccessControl authorization; it only dictates the physical accessibility of the object.

## Failure Model
All infrastructure exceptions (e.g., Flysystem, PDO) are intercepted and translated into typed, package-owned exceptions:
- `FileNotFound`
- `FileStorageFailed` (Wraps the original `Throwable`).
- `InvalidStorageKey` (Thrown during validation).

## Laravel Adapter Boundary
The infrastructure adapter `LaravelFilesystemAdapter` sits behind the Public contract. It utilizes Laravel's `Illuminate\Contracts\Filesystem\Filesystem` under the hood. No Laravel/Flysystem objects leak into the Public API.

## Deferred Concerns / Technical Debt
- **Metadata**: Basic size/MIME retrieval is deferred pending concrete product requirements.
- **Public URLs**: URL generation is deferred.
- **Checksums**: Deferred.
