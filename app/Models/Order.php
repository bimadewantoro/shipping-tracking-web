<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'order_number',
        'biteship_order_id',
        'status',
        'courier_code',
        'courier_service',
        'waybill_id',
        'tracking_id',
        'sender_name',
        'sender_phone',
        'sender_address',
        'sender_postal_code',
        'sender_area_id',
        'sender_latitude',
        'sender_longitude',
        'receiver_name',
        'receiver_phone',
        'receiver_address',
        'receiver_postal_code',
        'receiver_area_id',
        'receiver_latitude',
        'receiver_longitude',
        'package_type',
        'package_weight',
        'package_length',
        'package_width',
        'package_height',
        'package_description',
        'package_value',
        'shipping_cost',
        'insurance_cost',
        'total_cost',
        'notes',
        'biteship_response',
        'scheduled_at',
        'delivered_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'sender_latitude' => 'decimal:8',
            'sender_longitude' => 'decimal:8',
            'receiver_latitude' => 'decimal:8',
            'receiver_longitude' => 'decimal:8',
            'package_weight' => 'integer',
            'package_length' => 'integer',
            'package_width' => 'integer',
            'package_height' => 'integer',
            'package_value' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'insurance_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'biteship_response' => 'array',
            'scheduled_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate unique order number
     */
    public static function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (self::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    /**
     * Check if order can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            OrderStatus::PENDING,
            OrderStatus::CONFIRMED,
            OrderStatus::SCHEDULED,
        ]);
    }

    /**
     * Check if order is trackable
     */
    public function isTrackable(): bool
    {
        return !empty($this->waybill_id) && $this->status->isActive();
    }

    /**
     * Get full sender address
     */
    public function getFullSenderAddressAttribute(): string
    {
        return $this->sender_address . ', ' . $this->sender_postal_code;
    }

    /**
     * Get full receiver address
     */
    public function getFullReceiverAddressAttribute(): string
    {
        return $this->receiver_address . ', ' . $this->receiver_postal_code;
    }

    /**
     * Scope for active orders
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            OrderStatus::PENDING,
            OrderStatus::CONFIRMED,
            OrderStatus::SCHEDULED,
            OrderStatus::ALLOCATED,
            OrderStatus::PICKING_UP,
            OrderStatus::PICKED,
            OrderStatus::DROPPING_OFF,
            OrderStatus::ON_HOLD,
            OrderStatus::RETURN_IN_TRANSIT,
        ]);
    }

    /**
     * Scope for completed orders
     */
    public function scopeCompleted($query)
    {
        return $query->whereIn('status', [
            OrderStatus::DELIVERED,
            OrderStatus::RETURNED,
        ]);
    }

    /**
     * Scope for cancelled orders
     */
    public function scopeCancelled($query)
    {
        return $query->whereIn('status', [
            OrderStatus::CANCELLED,
            OrderStatus::REJECTED,
            OrderStatus::DISPOSED,
        ]);
    }
}
