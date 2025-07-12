import api from "./api";

const orderService = {
    // Get all orders with pagination and filters
    getOrders: async (params = {}) => {
        const response = await api.get("/orders", { params });
        return response.data;
    },

    // Get order statistics
    getStatistics: async () => {
        const response = await api.get("/orders/statistics");
        return response.data;
    },

    // Create new order
    createOrder: async (orderData) => {
        const response = await api.post("/orders", orderData);
        return response.data;
    },

    // Get order details
    getOrder: async (orderId) => {
        const response = await api.get(`/orders/${orderId}`);
        return response.data;
    },

    // Confirm order with Biteship
    confirmOrder: async (orderId) => {
        const response = await api.post(`/orders/${orderId}/confirm`);
        return response.data;
    },

    // Cancel order
    cancelOrder: async (orderId, reason = "") => {
        const response = await api.post(`/orders/${orderId}/cancel`, {
            reason,
        });
        return response.data;
    },

    // Track order
    trackOrder: async (orderId) => {
        const response = await api.get(`/orders/${orderId}/track`);
        return response.data;
    },

    // Public tracking
    publicTracking: async (waybillId, courierCode) => {
        const response = await api.get("/public/track", {
            params: { waybill_id: waybillId, courier_code: courierCode },
        });
        return response.data;
    },
};

export default orderService;
