<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\CancelOrderRequest;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Models\Order;
use App\Services\BiteshipService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    protected OrderService $orderService;
    protected BiteshipService $biteshipService;

    public function __construct(OrderService $orderService, BiteshipService $biteshipService)
    {
        $this->orderService = $orderService;
        $this->biteshipService = $biteshipService;
    }

    /**
     * Display a listing of orders
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $perPage = min($request->integer('per_page', 15), 100);
            $search = $request->string('search');
            $status = $request->string('status');
            $isAdmin = $user->isAdmin();

            $orders = $this->orderService->getPaginatedOrders(
                $user,
                $perPage,
                $search,
                $status,
                $isAdmin
            );

            return response()->json([
                'status' => 'success',
                'data' => $orders,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get orders list', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve orders',
            ], 500);
        }
    }

    /**
     * Store a newly created order
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $order = $this->orderService->createOrder($user, $request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Order created successfully',
                'data' => [
                    'order' => $order->load('user:id,name,email'),
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create order', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create order: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified order
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        try {
            $user = $request->user();

            // Check if user can access this order
            if (!$user->isAdmin() && $order->user_id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are not authorized to view this order',
                ], 403);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'order' => $order->load('user:id,name,email'),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get order details', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve order details',
            ], 500);
        }
    }

    /**
     * Create Biteship order for existing order
     */
    public function createBiteshipOrder(Request $request, Order $order): JsonResponse
    {
        try {
            $user = $request->user();

            // Check if user can access this order
            if (!$user->isAdmin() && $order->user_id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are not authorized to modify this order',
                ], 403);
            }

            // Check if order already has Biteship order
            if ($order->biteship_order_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order has already been confirmed',
                ], 400);
            }

            $updatedOrder = $this->orderService->confirmOrder($order);

            return response()->json([
                'status' => 'success',
                'message' => 'Order confirmed and Biteship order created successfully',
                'data' => [
                    'order' => $updatedOrder->load('user:id,name,email'),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to confirm order', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to confirm order: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update order status from Biteship
     */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        try {
            $user = $request->user();

            // Check if user can access this order
            if (!$user->isAdmin() && $order->user_id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are not authorized to modify this order',
                ], 403);
            }

            $updatedOrder = $this->orderService->updateOrderStatus($order);

            return response()->json([
                'status' => 'success',
                'message' => 'Order status updated successfully',
                'data' => [
                    'order' => $updatedOrder->load('user:id,name,email'),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update order status', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update order status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel order
     */
    public function cancel(CancelOrderRequest $request, Order $order): JsonResponse
    {
        try {
            $user = $request->user();

            // Check if user can access this order
            if (!$user->isAdmin() && $order->user_id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are not authorized to cancel this order',
                ], 403);
            }

            $reason = $request->input('reason');
            $cancelledOrder = $this->orderService->cancelOrder($order, $reason);

            return response()->json([
                'status' => 'success',
                'message' => 'Order cancelled successfully',
                'data' => [
                    'order' => $cancelledOrder->load('user:id,name,email'),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to cancel order', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            // Return 422 for business logic errors (like cannot cancel)
            if (str_contains($e->getMessage(), 'cannot be cancelled') || str_contains($e->getMessage(), 'current status')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order cannot be cancelled',
                    'errors' => ['order' => [$e->getMessage()]],
                ], 422);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to cancel order: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Track order
     */
    public function track(Request $request, Order $order): JsonResponse
    {
        try {
            $user = $request->user();

            // Check if user can access this order
            if (!$user->isAdmin() && $order->user_id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are not authorized to track this order',
                ], 403);
            }

            $trackingData = $this->orderService->trackOrder($order);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'order' => $order->fresh()->load('user:id,name,email'),
                    'tracking' => $trackingData,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to track order', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            // Return 422 for business logic errors (like no waybill)
            if (
                str_contains($e->getMessage(), 'waybill') ||
                str_contains($e->getMessage(), 'tracking') ||
                str_contains($e->getMessage(), 'trackable') ||
                str_contains($e->getMessage(), 'Insufficient')
            ) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order tracking not available',
                    'errors' => ['order' => [$e->getMessage()]],
                ], 422);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to track order: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get order statistics for dashboard
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $isAdmin = $user->isAdmin();

            $statistics = $this->orderService->getOrderStatistics($user, $isAdmin);

            return response()->json([
                'status' => 'success',
                'data' => $statistics,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get order statistics', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve order statistics',
            ], 500);
        }
    }

    /**
     * Public tracking endpoint (for customers without authentication)
     */
    public function publicTracking(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'waybill_id' => 'required|string',
                'courier_code' => 'required|string',
            ]);

            $waybillId = $request->input('waybill_id');
            $courierCode = $request->input('courier_code');

            $trackingData = $this->biteshipService->getPublicTracking($waybillId, $courierCode);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'tracking' => $trackingData,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get public tracking', [
                'waybill_id' => $request->input('waybill_id'),
                'courier_code' => $request->input('courier_code'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve tracking information: ' . $e->getMessage(),
            ], 500);
        }
    }
}
