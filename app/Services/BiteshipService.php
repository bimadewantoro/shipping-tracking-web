<?php

namespace App\Services;

use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BiteshipService
{
    protected string $apiKey;
    protected string $apiUrl;
    protected int $timeout;

    public function __construct()
    {
        $this->apiKey = config('api.biteship.api_key');
        $this->apiUrl = config('api.biteship.api_url');
        $this->timeout = config('api.biteship.timeout', 30);

        if (empty($this->apiKey)) {
            throw new Exception('Biteship API key is not configured');
        }
    }

    /**
     * Make authenticated request to Biteship API
     */
    protected function makeRequest(string $method, string $endpoint, array $data = []): Response
    {
        $url = $this->apiUrl . $endpoint;

        Log::info('Making Biteship API request', [
            'method' => $method,
            'url' => $url,
            'data' => $data,
        ]);

        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'authorization' => $this->apiKey,
                'content-type' => 'application/json',
            ])
            ->$method($url, $data);

        Log::info('Biteship API response', [
            'status' => $response->status(),
            'response' => $response->json(),
        ]);

        return $response;
    }

    // Order Creation and Management methods only

    /**
     * Create a new order
     */
    public function createOrder(array $data): array
    {
        try {
            $response = $this->makeRequest('post', '/orders', $data);

            if (!$response->successful()) {
                throw new Exception('Failed to create order: ' . $response->body());
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('Error creating order', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Get order details
     */
    public function getOrder(string $orderId): array
    {
        try {
            $response = $this->makeRequest('get', "/orders/{$orderId}");

            if (!$response->successful()) {
                throw new Exception('Failed to get order details: ' . $response->body());
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('Error getting order details', [
                'error' => $e->getMessage(),
                'order_id' => $orderId,
            ]);
            throw $e;
        }
    }

    /**
     * Update order
     */
    public function updateOrder(string $orderId, array $data): array
    {
        try {
            $response = $this->makeRequest('post', "/orders/{$orderId}", $data);

            if (!$response->successful()) {
                throw new Exception('Failed to update order: ' . $response->body());
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('Error updating order', [
                'error' => $e->getMessage(),
                'order_id' => $orderId,
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Cancel order
     */
    public function cancelOrder(string $orderId, array $data = []): array
    {
        try {
            $response = $this->makeRequest('post', "/orders/{$orderId}/cancel", $data);

            if (!$response->successful()) {
                throw new Exception('Failed to cancel order: ' . $response->body());
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('Error cancelling order', [
                'error' => $e->getMessage(),
                'order_id' => $orderId,
            ]);
            throw $e;
        }
    }

    /**
     * Track package by Biteship order ID
     */
    public function trackByOrderId(string $orderId): array
    {
        try {
            $response = $this->makeRequest('get', "/trackings/{$orderId}");

            if (!$response->successful()) {
                throw new Exception('Failed to track package: ' . $response->body());
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('Error tracking package by order ID', [
                'error' => $e->getMessage(),
                'order_id' => $orderId,
            ]);
            throw $e;
        }
    }

    /**
     * Public tracking (for customer-facing tracking)
     */
    public function getPublicTracking(string $waybillId, string $courierCode): array
    {
        try {
            $response = $this->makeRequest('get', "/trackings/{$waybillId}/couriers/{$courierCode}");

            if (!$response->successful()) {
                throw new Exception('Failed to get public tracking: ' . $response->body());
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('Error getting public tracking', [
                'error' => $e->getMessage(),
                'waybill_id' => $waybillId,
                'courier_code' => $courierCode,
            ]);
            throw $e;
        }
    }

    /**
     * Handle API errors and provide meaningful messages
     */
    public function handleApiError(int $statusCode, array $errorResponse): string
    {
        $errorCode = $errorResponse['error']['code'] ?? null;
        $errorMessage = $errorResponse['error']['message'] ?? 'Unknown error';

        return match ($errorCode) {
            '40000001' => 'Authentication failed. Please check your API key.',
            '40101001' => 'Authorization failed. Insufficient permissions.',
            '40101002' => 'No account found with associated key.',
            '40101003' => 'Cannot process authorization.',
            '40301001' => 'No match token for this key.',
            '40301002' => 'User information not found.',
            default => "API Error ({$statusCode}): {$errorMessage}",
        };
    }
}
