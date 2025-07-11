<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case SCHEDULED = 'scheduled';
    case ALLOCATED = 'allocated';
    case PICKING_UP = 'picking_up';
    case PICKED = 'picked';
    case CANCELLED = 'cancelled';
    case ON_HOLD = 'on_hold';
    case DROPPING_OFF = 'dropping_off';
    case RETURN_IN_TRANSIT = 'return_in_transit';
    case RETURNED = 'returned';
    case REJECTED = 'rejected';
    case DISPOSED = 'disposed';
    case COURIER_NOT_FOUND = 'courier_not_found';
    case DELIVERED = 'delivered';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::CONFIRMED => 'Confirmed',
            self::SCHEDULED => 'Scheduled',
            self::ALLOCATED => 'Allocated',
            self::PICKING_UP => 'Picking Up',
            self::PICKED => 'Picked',
            self::CANCELLED => 'Cancelled',
            self::ON_HOLD => 'On Hold',
            self::DROPPING_OFF => 'Dropping Off',
            self::RETURN_IN_TRANSIT => 'Return in Transit',
            self::RETURNED => 'Returned',
            self::REJECTED => 'Rejected',
            self::DISPOSED => 'Disposed',
            self::COURIER_NOT_FOUND => 'Courier Not Found',
            self::DELIVERED => 'Delivered',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::CONFIRMED, self::SCHEDULED, self::ALLOCATED => 'info',
            self::PICKING_UP, self::PICKED, self::DROPPING_OFF => 'primary',
            self::DELIVERED => 'success',
            self::CANCELLED, self::REJECTED, self::DISPOSED, self::COURIER_NOT_FOUND => 'danger',
            self::ON_HOLD, self::RETURN_IN_TRANSIT, self::RETURNED => 'secondary',
        };
    }

    public function isActive(): bool
    {
        return !in_array($this, [
            self::CANCELLED,
            self::REJECTED,
            self::DISPOSED,
            self::DELIVERED,
            self::RETURNED,
        ]);
    }

    public function isCompleted(): bool
    {
        return in_array($this, [
            self::DELIVERED,
            self::RETURNED,
        ]);
    }

    public function isCancelled(): bool
    {
        return in_array($this, [
            self::CANCELLED,
            self::REJECTED,
            self::DISPOSED,
        ]);
    }
}
