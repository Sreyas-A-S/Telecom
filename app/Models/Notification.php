<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     title="Notification",
 *     description="Notification model",
 *     @OA\Xml(
 *         name="Notification"
 *     )
 * )
 */
class Notification extends Model
{
    use HasFactory;

    /**
     * @OA\Property(
     *     title="ID",
     *     description="ID of the notification",
     *     format="int64",
     *     example=1
     * )
     *
     * @var int
     */
    private $id;

    /**
     * @OA\Property(
     *     title="Title",
     *     description="Title of the notification",
     *     example="New Message"
     * )
     *
     * @var string
     */
    private $title;

    /**
     * @OA\Property(
     *     title="Message",
     *     description="Content of the notification message",
     *     example="You have a new message from John Doe."
     * )
     *
     * @var string
     */
    private $message;

    /**
     * @OA\Property(
     *     title="User ID",
     *     description="ID of the user who received the notification",
     *     format="int64",
     *     example=1
     * )
     *
     * @var int
     */
    private $user_id;

    /**
     * @OA\Property(
     *     title="Notification ID",
     *     description="External notification identifier",
     *     example="some-uuid-123"
     * )
     *
     * @var string
     */
    private $notification_id;

    /**
     * @OA\Property(
     *     title="Data",
     *     description="Additional data associated with the notification",
     *     type="object",
     *     example={"key": "value"}
     * )
     *
     * @var object
     */
    private $data;

    /**
     * @OA\Property(
     *     title="Read At",
     *     description="Timestamp when the notification was read",
     *     type="string",
     *     format="date-time",
     *     example="2025-10-27 10:00:00"
     * )
     *
     * @var string
     */
    private $read_at;

    /**
     * @OA\Property(
     *     title="Hidden At",
     *     description="Timestamp when the notification was hidden",
     *     type="string",
     *     format="date-time",
     *     example="2025-10-27 11:00:00"
     * )
     *
     * @var string
     */
    private $hidden_at;

    /**
     * @OA\Property(
     *     title="Created At",
     *     description="Timestamp when the notification was created",
     *     type="string",
     *     format="date-time",
     *     example="2025-10-27 09:00:00"
     * )
     *
     * @var string
     */
    private $created_at;

    /**
     * @OA\Property(
     *     title="Updated At",
     *     description="Timestamp when the notification was last updated",
     *     type="string",
     *     format="date-time",
     *     example="2025-10-27 09:00:00"
     * )
     *
     * @var string
     */
    private $updated_at;

    protected $fillable = [
        'title',
        'message',
        'user_id',
        'notification_id',
        'data',
        'read_at',
        'hidden_at', // Added hidden_at
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'hidden_at' => 'datetime', // Added hidden_at cast
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark the notification as read.
     *
     * @return void
     */
    public function markAsRead()
    {
        if (is_null($this->read_at)) {
            $this->forceFill(['read_at' => $this->freshTimestamp()])->save();
        }
    }

    /**
     * Mark the notification as unread.
     *
     * @return void
     */
    public function markAsUnread()
    {
        if (! is_null($this->read_at)) {
            $this->forceFill(['read_at' => null])->save();
        }
    }
}
