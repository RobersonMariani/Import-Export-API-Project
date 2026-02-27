<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int                          $id
 * @property string                       $auditable_type
 * @property string                       $auditable_id
 * @property string                       $event
 * @property int|null                     $user_id
 * @property array<array-key, mixed>|null $old_values
 * @property array<array-key, mixed>|null $new_values
 * @property string|null                  $ip_address
 * @property string|null                  $user_agent
 * @property string|null                  $correlation_id
 * @property Carbon                       $created_at
 * @property-read Model $auditable
 * @property-read User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereAuditableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereAuditableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereCorrelationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereNewValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereOldValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereUserId($value)
 * @mixin \Eloquent
 */
	class AuditLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @use HasFactory<ExportFactory>
 * @property string                       $id
 * @property int                          $user_id
 * @property string                       $status
 * @property string|null                  $file_path
 * @property array<array-key, mixed>|null $filters
 * @property int                          $total_records
 * @property bool                         $compressed
 * @property Carbon|null                  $expires_at
 * @property Carbon|null                  $started_at
 * @property Carbon|null                  $finished_at
 * @property Carbon|null                  $created_at
 * @property Carbon|null                  $updated_at
 * @property-read User $user
 * @method static \Database\Factories\ExportFactory                    factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Export newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Export newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Export query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Export whereCompressed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Export whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Export whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Export whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Export whereFilters($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Export whereFinishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Export whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Export whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Export whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Export whereTotalRecords($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Export whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Export whereUserId($value)
 * @mixin \Eloquent
 */
	class Export extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string                    $id
 * @property int                       $user_id
 * @property string                    $status
 * @property int                       $progress
 * @property int                       $total_records
 * @property int                       $success_count
 * @property int                       $failure_count
 * @property string                    $file_path
 * @property string                    $original_filename
 * @property array<string, mixed>|null $metadata
 * @property Carbon|null               $started_at
 * @property Carbon|null               $finished_at
 * @property Carbon|null               $created_at
 * @property Carbon|null               $updated_at
 * @property-read Collection<int, ImportFailure> $failures
 * @property-read int|null $failures_count
 * @property-read User $user
 * @method static \Database\Factories\ImportFactory                    factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereFailureCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereFinishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereOriginalFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereProgress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereSuccessCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereTotalRecords($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereUserId($value)
 * @mixin \Eloquent
 */
	class Import extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int                     $id
 * @property string                  $import_id
 * @property int                     $line_number
 * @property array<array-key, mixed> $payload
 * @property string                  $error_message
 * @property Carbon|null             $created_at
 * @property Carbon|null             $updated_at
 * @property-read Import $import
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImportFailure newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImportFailure newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImportFailure query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImportFailure whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImportFailure whereErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImportFailure whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImportFailure whereImportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImportFailure whereLineNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImportFailure wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImportFailure whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class ImportFailure extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int         $id
 * @property string      $name
 * @property string      $email
 * @property Carbon|null $email_verified_at
 * @property string      $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $phone
 * @property string|null $address
 * @property string|null $city
 * @property string|null $state
 * @property string|null $zip_code
 * @property Carbon|null $birth_date
 * @property string      $role
 * @property Carbon|null $deleted_at
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\UserFactory                    factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBirthDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereZipCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutTrashed()
 * @mixin \Eloquent
 */
	class User extends \Eloquent implements \PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject {}
}

