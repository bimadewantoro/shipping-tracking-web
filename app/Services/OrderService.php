<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    protected BiteshipService $biteshipService;

    public function __construct(BiteshipService $biteshipService)
    {
        $this->biteshipService = $biteshipService;
    }

    /**
     * Get paginated orders for a user or all orders (admin)
     */
    public function getPaginatedOrders(
        User $user,
        int $perPage = 15,
        ?string $search = null,
        ?string $status = null,
        bool $isAdmin = false
    ): LengthAwarePaginator {
        $query = $isAdmin ? Order::query() : $user->orders();

        // Include user relationship for admin
        if ($isAdmin) {
            $query->with('user:id,name,email');
        }

        // Apply search filter
        if (!empty($search)) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('receiver_name', 'like', "%{$search}%")
                    ->orWhere('receiver_phone', 'like', "%{$search}%")
                    ->orWhere('sender_name', 'like', "%{$search}%")
                    ->orWhere('waybill_id', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if (!empty($status)) {
            $query->where('status', $status);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Create a new order
     */
    public function createOrder(User $user, array $data): Order
    {
        return DB::transaction(function () use ($user, $data) {
            try {
                // Generate order number
                $orderNumber = Order::generateOrderNumber();

                // Create order in database first
                $order = Order::create([
                    'user_id' => $user->id,
                    'order_number' => $orderNumber,
                    'status' => OrderStatus::PENDING,
                    'sender_name' => $data['sender_name'],
                    'sender_phone' => $data['sender_phone'],
                    'sender_address' => $data['sender_address'],
                    'sender_postal_code' => $data['sender_postal_code'],
                    'sender_area_id' => $data['sender_area_id'] ?? null,
                    'sender_latitude' => $data['sender_latitude'] ?? null,
                    'sender_longitude' => $data['sender_longitude'] ?? null,
                    'receiver_name' => $data['receiver_name'],
                    'receiver_phone' => $data['receiver_phone'],
                    'receiver_address' => $data['receiver_address'],
                    'receiver_postal_code' => $data['receiver_postal_code'],
                    'receiver_area_id' => $data['receiver_area_id'] ?? null,
                    'receiver_latitude' => $data['receiver_latitude'] ?? null,
                    'receiver_longitude' => $data['receiver_longitude'] ?? null,
                    'package_type' => $data['package_type'] ?? 'package',
                    'package_weight' => $data['package_weight'],
                    'package_length' => $data['package_length'] ?? null,
                    'package_width' => $data['package_width'] ?? null,
                    'package_height' => $data['package_height'] ?? null,
                    'package_description' => $data['package_description'] ?? null,
                    'package_value' => $data['package_value'] ?? null,
                    'courier_code' => $data['courier_code'],
                    'courier_service' => $data['courier_service'],
                    'shipping_cost' => 0,
                    'insurance_cost' => $data['insurance_cost'] ?? 0,
                    'total_cost' => 0,
                    'notes' => $data['notes'] ?? null,
                ]);

                // If auto_create_biteship_order is true, create order in Biteship
                if ($data['auto_create_biteship_order'] ?? false) {
                    $this->createBiteshipOrder($order);
                }

                Log::info('Order created successfully', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'user_id' => $user->id,
                    'total_cost' => $order->total_cost,
                ]);

                return $order;
            } catch (Exception $e) {
                Log::error('Failed to create order', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->id,
                    'data' => $data,
                ]);
                throw $e;
            }
        });
    }

    /**
     * Create order in Biteship
     */
    public function createBiteshipOrder(Order $order): Order
    {
        try {
            // Prepare data for Biteship API
            $biteshipData = [
                'shipper_contact_name' => $order->sender_name,
                'shipper_contact_phone' => $order->sender_phone,
                'shipper_contact_email' => $order->user->email,
                'shipper_organization' => config('app.name'),
                'origin_contact_name' => $order->sender_name,
                'origin_contact_phone' => $order->sender_phone,
                'origin_address' => $order->sender_address,
                'origin_postal_code' => $order->sender_postal_code,
                'destination_contact_name' => $order->receiver_name,
                'destination_contact_phone' => $order->receiver_phone,
                'destination_contact_email' => null,
                'destination_address' => $order->receiver_address,
                'destination_postal_code' => $order->receiver_postal_code,
                'courier_company' => $order->courier_code,
                'courier_type' => $order->courier_service,
                'courier_insurance' => $order->insurance_cost,
                'delivery_type' => 'now',
                'order_note' => $order->notes,
                'metadata' => [
                    'order_number' => $order->order_number,
                    'user_id' => $order->user_id,
                ],
                'items' => [
                    [
                        'name' => $order->package_description ?: 'Package',
                        'weight' => $order->package_weight,
                        'length' => $order->package_length ?: 10,
                        'width' => $order->package_width ?: 10,
                        'height' => $order->package_height ?: 10,
                        'value' => $order->package_value ?: 0,
                    ]
                ]
            ];

            // Add coordinates if available
            if ($order->sender_latitude && $order->sender_longitude) {
                $biteshipData['origin_coordinate'] = [
                    'latitude' => (float) $order->sender_latitude,
                    'longitude' => (float) $order->sender_longitude,
                ];
            }

            if ($order->receiver_latitude && $order->receiver_longitude) {
                $biteshipData['destination_coordinate'] = [
                    'latitude' => (float) $order->receiver_latitude,
                    'longitude' => (float) $order->receiver_longitude,
                ];
            }

            $response = $this->biteshipService->createOrder($biteshipData);

            // Update order with Biteship response
            $order->update([
                'biteship_order_id' => $response['id'],
                'status' => $this->mapBiteshipStatus($response['status']),
                'waybill_id' => $response['courier']['waybill_id'] ?? null,
                'tracking_id' => $response['courier']['tracking_id'] ?? null,
                'shipping_cost' => $response['price'] ?? 0,
                'insurance_cost' => $response['courier']['insurance']['fee'] ?? 0,
                'total_cost' => ($response['price'] ?? 0) + ($response['courier']['insurance']['fee'] ?? 0),
                'biteship_response' => $response,
            ]);

            Log::info('Biteship order created successfully', [
                'order_id' => $order->id,
                'biteship_order_id' => $response['id'],
                'status' => $response['status'],
            ]);

            return $order;
        } catch (Exception $e) {
            Log::error('Failed to create Biteship order', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
            ]);
            throw $e;
        }
    }

    /**
     * Update order status from Biteship
     */
    public function updateOrderStatus(Order $order): Order
    {
        try {
            if (!$order->biteship_order_id) {
                throw new Exception('Order does not have Biteship order ID');
            }

            $response = $this->biteshipService->getOrder($order->biteship_order_id);

            $order->update([
                'status' => $this->mapBiteshipStatus($response['status']),
                'waybill_id' => $response['courier']['waybill_id'] ?? $order->waybill_id,
                'tracking_id' => $response['courier']['tracking_id'] ?? $order->tracking_id,
                'shipping_cost' => $response['price'] ?? $order->shipping_cost,
                'insurance_cost' => $response['courier']['insurance']['fee'] ?? $order->insurance_cost,
                'total_cost' => ($response['price'] ?? $order->shipping_cost) + ($response['courier']['insurance']['fee'] ?? $order->insurance_cost),
                'biteship_response' => $response,
                'delivered_at' => $response['status'] === 'delivered' ? now() : $order->delivered_at,
            ]);

            Log::info('Order status updated from Biteship', [
                'order_id' => $order->id,
                'old_status' => $order->getOriginal('status'),
                'new_status' => $order->status,
            ]);

            return $order;
        } catch (Exception $e) {
            Log::error('Failed to update order status', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
            ]);
            throw $e;
        }
    }

    /**
     * Cancel order
     */
    public function cancelOrder(Order $order, string $reason = null): Order
    {
        return DB::transaction(function () use ($order, $reason) {
            try {
                if (!$order->canBeCancelled()) {
                    throw new Exception('Order cannot be cancelled in current status: ' . $order->status->label());
                }

                // Cancel in Biteship if order exists there
                if ($order->biteship_order_id) {
                    $cancelData = $reason ? ['cancellation_reason' => $reason] : [];
                    $this->biteshipService->cancelOrder($order->biteship_order_id, $cancelData);
                }

                // Update order status
                $order->update([
                    'status' => OrderStatus::CANCELLED,
                    'notes' => $order->notes ? $order->notes . "\n\nCancelled: " . ($reason ?: 'No reason provided') : 'Cancelled: ' . ($reason ?: 'No reason provided'),
                ]);

                Log::info('Order cancelled successfully', [
                    'order_id' => $order->id,
                    'reason' => $reason,
                ]);

                return $order;
            } catch (Exception $e) {
                Log::error('Failed to cancel order', [
                    'error' => $e->getMessage(),
                    'order_id' => $order->id,
                ]);
                throw $e;
            }
        });
    }

    /**
     * Track order
     */
    public function trackOrder(Order $order): array
    {
        try {
            if (!$order->isTrackable()) {
                throw new Exception('Order is not trackable');
            }

            // Try to track by Biteship order ID first
            if ($order->tracking_id) {
                $trackingData = $this->biteshipService->trackByOrderId($order->tracking_id);
            } else {
                throw new Exception('Insufficient tracking information');
            }

            // Update order status if different
            if (isset($trackingData['status'])) {
                $newStatus = $this->mapBiteshipStatus($trackingData['status']);
                if ($order->status !== $newStatus) {
                    $order->update([
                        'status' => $newStatus,
                        'delivered_at' => $trackingData['status'] === 'delivered' ? now() : $order->delivered_at,
                    ]);
                }
            }

            Log::info('Order tracked successfully', [
                'order_id' => $order->id,
                'tracking_status' => $trackingData['status'] ?? 'unknown',
            ]);

            return $trackingData;
        } catch (Exception $e) {
            Log::error('Failed to track order', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
            ]);
            throw $e;
        }
    }

    /**
     * Map Biteship status to internal status
     */
    public function mapBiteshipStatus(string $biteshipStatus): OrderStatus
    {
        return match ($biteshipStatus) {
            'confirmed' => OrderStatus::CONFIRMED,
            'scheduled' => OrderStatus::SCHEDULED,
            'allocated' => OrderStatus::ALLOCATED,
            'picking_up' => OrderStatus::PICKING_UP,
            'picked' => OrderStatus::PICKED,
            'cancelled' => OrderStatus::CANCELLED,
            'on_hold' => OrderStatus::ON_HOLD,
            'dropping_off' => OrderStatus::DROPPING_OFF,
            'return_in_transit' => OrderStatus::RETURN_IN_TRANSIT,
            'returned' => OrderStatus::RETURNED,
            'rejected' => OrderStatus::REJECTED,
            'disposed' => OrderStatus::DISPOSED,
            'courier_not_found' => OrderStatus::COURIER_NOT_FOUND,
            'delivered' => OrderStatus::DELIVERED,
            default => OrderStatus::PENDING,
        };
    }

    /**
     * Get order statistics for dashboard
     */
    public function getOrderStatistics(User $user, bool $isAdmin = false): array
    {
        if ($isAdmin) {
            $totalOrders = Order::count();
            $pendingOrders = Order::where('status', OrderStatus::PENDING)->count();
            $activeOrders = Order::active()->count();
            $completedOrders = Order::completed()->count();
            $cancelledOrders = Order::cancelled()->count();
            $totalShippingCost = Order::sum('total_cost');
        } else {
            $totalOrders = $user->orders()->count();
            $pendingOrders = $user->orders()->where('status', OrderStatus::PENDING)->count();
            $activeOrders = $user->orders()->active()->count();
            $completedOrders = $user->orders()->completed()->count();
            $cancelledOrders = $user->orders()->cancelled()->count();
            $totalShippingCost = $user->orders()->sum('total_cost');
        }

        return [
            'total_orders' => $totalOrders,
            'pending_orders' => $pendingOrders,
            'active_orders' => $activeOrders,
            'completed_orders' => $completedOrders,
            'cancelled_orders' => $cancelledOrders,
            'total_shipping_cost' => $totalShippingCost,
        ];
    }

    /**
     * Confirm order and create Biteship order
     */
    public function confirmOrder(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            try {
                if ($order->status !== OrderStatus::PENDING) {
                    throw new Exception('Order cannot be confirmed in current status: ' . $order->status->label());
                }

                if ($order->biteship_order_id) {
                    throw new Exception('Order has already been confirmed with Biteship');
                }

                // Create order in Biteship and update costs
                $this->createBiteshipOrder($order);

                Log::info('Order confirmed successfully', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'biteship_order_id' => $order->biteship_order_id,
                    'shipping_cost' => $order->shipping_cost,
                    'total_cost' => $order->total_cost,
                ]);

                return $order;
            } catch (Exception $e) {
                Log::error('Failed to confirm order', [
                    'error' => $e->getMessage(),
                    'order_id' => $order->id,
                ]);
                throw $e;
            }
        });
    }
}
