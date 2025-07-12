import React, { useState, useEffect } from "react";
import { useParams, useNavigate, Link } from "react-router-dom";
import orderService from "../services/orderService";

const OrderDetails = () => {
    const { id } = useParams();
    const navigate = useNavigate();
    const [order, setOrder] = useState(null);
    const [loading, setLoading] = useState(true);
    const [actionLoading, setActionLoading] = useState(false);
    const [trackingData, setTrackingData] = useState(null);
    const [showCancelModal, setShowCancelModal] = useState(false);
    const [cancelReason, setCancelReason] = useState("");

    useEffect(() => {
        fetchOrderDetails();
    }, [id]);

    const fetchOrderDetails = async () => {
        try {
            const response = await orderService.getOrder(id);
            if (response.status === "success") {
                setOrder(response.data.order);
                // Set tracking data if it exists in the response
                if (response.data.tracking) {
                    setTrackingData(response.data.tracking);
                }
            }
        } catch (error) {
            console.error("Failed to fetch order details:", error);
        } finally {
            setLoading(false);
        }
    };

    const handleConfirmOrder = async () => {
        setActionLoading(true);
        try {
            const response = await orderService.confirmOrder(id);
            if (response.status === "success") {
                setOrder(response.data.order);
                // Update tracking data if returned
                if (response.data.tracking) {
                    setTrackingData(response.data.tracking);
                }
                alert("Order confirmed successfully!");
            }
        } catch (error) {
            console.error("Failed to confirm order:", error);
            alert("Failed to confirm order");
        } finally {
            setActionLoading(false);
        }
    };

    const handleCancelOrder = async () => {
        setActionLoading(true);
        try {
            const response = await orderService.cancelOrder(id, cancelReason);
            if (response.status === "success") {
                setOrder(response.data.order);
                setShowCancelModal(false);
                setCancelReason("");
                alert("Order cancelled successfully!");
            }
        } catch (error) {
            console.error("Failed to cancel order:", error);
            alert("Failed to cancel order");
        } finally {
            setActionLoading(false);
        }
    };

    const handleTrackOrder = async () => {
        setActionLoading(true);
        try {
            const response = await orderService.trackOrder(id);
            if (response.status === "success") {
                setTrackingData(response.data.tracking || response.data);
            }
        } catch (error) {
            console.error("Failed to track order:", error);
            alert("Failed to track order");
        } finally {
            setActionLoading(false);
        }
    };

    const getStatusColor = (status) => {
        const colors = {
            pending: "text-yellow-600 bg-yellow-100",
            confirmed: "text-blue-600 bg-blue-100",
            processing: "text-indigo-600 bg-indigo-100",
            shipped: "text-purple-600 bg-purple-100",
            delivered: "text-green-600 bg-green-100",
            cancelled: "text-red-600 bg-red-100",
        };
        return colors[status] || "text-gray-600 bg-gray-100";
    };

    const formatCurrency = (amount) => {
        return new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
        }).format(parseFloat(amount || 0));
    };

    const formatDateTime = (dateString) => {
        return new Date(dateString).toLocaleString("id-ID", {
            year: "numeric",
            month: "long",
            day: "numeric",
            hour: "2-digit",
            minute: "2-digit",
            second: "2-digit",
        });
    };

    if (loading) {
        return (
            <div className="flex items-center justify-center min-h-screen">
                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            </div>
        );
    }

    if (!order) {
        return (
            <div className="px-4 py-6">
                <div className="text-center">
                    <h2 className="text-lg font-medium text-gray-900">
                        Order not found
                    </h2>
                    <Link
                        to="/orders"
                        className="mt-2 text-blue-600 hover:text-blue-500"
                    >
                        Back to Orders
                    </Link>
                </div>
            </div>
        );
    }

    return (
        <div className="px-4 py-6">
            {/* Header */}
            <div className="mb-6 flex items-center justify-between">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900">
                        Order #{order.order_number}
                    </h1>
                    <div className="mt-2 flex items-center space-x-4">
                        <span
                            className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(
                                order.status
                            )}`}
                        >
                            {order.status.charAt(0).toUpperCase() +
                                order.status.slice(1)}
                        </span>
                        <span className="text-sm text-gray-500">
                            Created:{" "}
                            {new Date(order.created_at).toLocaleDateString()}
                        </span>
                        {order.waybill_id && (
                            <span className="text-sm text-gray-500">
                                Waybill: {order.waybill_id}
                            </span>
                        )}
                    </div>
                </div>
                <Link
                    to="/orders"
                    className="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                >
                    Back to Orders
                </Link>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {/* Order Information */}
                <div className="space-y-6">
                    {/* Sender Information */}
                    <div className="bg-white shadow rounded-lg p-6">
                        <h2 className="text-lg font-medium text-gray-900 mb-4">
                            Sender Information
                        </h2>
                        <div className="space-y-2">
                            <p>
                                <span className="font-medium">Name:</span>{" "}
                                {order.sender_name}
                            </p>
                            <p>
                                <span className="font-medium">Phone:</span>{" "}
                                {order.sender_phone}
                            </p>
                            <p>
                                <span className="font-medium">Address:</span>{" "}
                                {order.sender_address}
                            </p>
                            <p>
                                <span className="font-medium">
                                    Postal Code:
                                </span>{" "}
                                {order.sender_postal_code}
                            </p>
                        </div>
                    </div>

                    {/* Receiver Information */}
                    <div className="bg-white shadow rounded-lg p-6">
                        <h2 className="text-lg font-medium text-gray-900 mb-4">
                            Receiver Information
                        </h2>
                        <div className="space-y-2">
                            <p>
                                <span className="font-medium">Name:</span>{" "}
                                {order.receiver_name}
                            </p>
                            <p>
                                <span className="font-medium">Phone:</span>{" "}
                                {order.receiver_phone}
                            </p>
                            <p>
                                <span className="font-medium">Address:</span>{" "}
                                {order.receiver_address}
                            </p>
                            <p>
                                <span className="font-medium">
                                    Postal Code:
                                </span>{" "}
                                {order.receiver_postal_code}
                            </p>
                        </div>
                    </div>
                </div>

                {/* Package & Shipping Information */}
                <div className="space-y-6">
                    {/* Package Information */}
                    <div className="bg-white shadow rounded-lg p-6">
                        <h2 className="text-lg font-medium text-gray-900 mb-4">
                            Package Information
                        </h2>
                        <div className="space-y-2">
                            <p>
                                <span className="font-medium">Type:</span>{" "}
                                {order.package_type}
                            </p>
                            <p>
                                <span className="font-medium">Weight:</span>{" "}
                                {order.package_weight}g
                            </p>
                            {order.package_length && (
                                <p>
                                    <span className="font-medium">
                                        Dimensions:
                                    </span>{" "}
                                    {order.package_length} x{" "}
                                    {order.package_width} x{" "}
                                    {order.package_height} cm
                                </p>
                            )}
                            {order.package_description && (
                                <p>
                                    <span className="font-medium">
                                        Description:
                                    </span>{" "}
                                    {order.package_description}
                                </p>
                            )}
                            {order.package_value && (
                                <p>
                                    <span className="font-medium">Value:</span>{" "}
                                    {formatCurrency(order.package_value)}
                                </p>
                            )}
                        </div>
                    </div>

                    {/* Shipping Information */}
                    <div className="bg-white shadow rounded-lg p-6">
                        <h2 className="text-lg font-medium text-gray-900 mb-4">
                            Shipping Information
                        </h2>
                        <div className="space-y-2">
                            <p>
                                <span className="font-medium">Courier:</span>{" "}
                                {order.courier_code.toUpperCase()}
                            </p>
                            <p>
                                <span className="font-medium">Service:</span>{" "}
                                {order.courier_service}
                            </p>
                            {order.tracking_id && (
                                <p>
                                    <span className="font-medium">
                                        Tracking ID:
                                    </span>{" "}
                                    <span className="font-mono text-sm bg-gray-100 px-2 py-1 rounded">
                                        {order.tracking_id}
                                    </span>
                                </p>
                            )}
                            {order.waybill_id && (
                                <p>
                                    <span className="font-medium">
                                        Waybill ID:
                                    </span>{" "}
                                    <span className="font-mono text-sm bg-gray-100 px-2 py-1 rounded">
                                        {order.waybill_id}
                                    </span>
                                </p>
                            )}
                            {order.shipping_cost && (
                                <p>
                                    <span className="font-medium">
                                        Shipping Cost:
                                    </span>{" "}
                                    {formatCurrency(order.shipping_cost)}
                                </p>
                            )}
                            {order.insurance_cost && (
                                <p>
                                    <span className="font-medium">
                                        Insurance:
                                    </span>{" "}
                                    {formatCurrency(order.insurance_cost)}
                                </p>
                            )}
                            {order.total_cost && (
                                <p className="text-lg font-semibold">
                                    <span className="font-medium">
                                        Total Cost:
                                    </span>{" "}
                                    {formatCurrency(order.total_cost)}
                                </p>
                            )}
                            {order.notes && (
                                <p>
                                    <span className="font-medium">Notes:</span>{" "}
                                    <span className="whitespace-pre-wrap">
                                        {order.notes}
                                    </span>
                                </p>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            {/* Actions */}
            <div className="mt-6 bg-white shadow rounded-lg p-6">
                <h2 className="text-lg font-medium text-gray-900 mb-4">
                    Actions
                </h2>
                <div className="flex flex-wrap gap-4">
                    {order.status === "pending" && (
                        <button
                            onClick={handleConfirmOrder}
                            disabled={actionLoading}
                            className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                        >
                            {actionLoading ? "Confirming..." : "Confirm Order"}
                        </button>
                    )}

                    {order.tracking_id && (
                        <button
                            onClick={handleTrackOrder}
                            disabled={actionLoading}
                            className="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50"
                        >
                            {actionLoading ? "Refreshing..." : "Track Order"}
                        </button>
                    )}

                    {order.tracking_id && trackingData?.link && (
                        <a
                            href={trackingData.link}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700"
                        >
                            Track on Biteship
                        </a>
                    )}

                    {["pending", "confirmed"].includes(order.status) && (
                        <button
                            onClick={() => setShowCancelModal(true)}
                            disabled={actionLoading}
                            className="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50"
                        >
                            Cancel Order
                        </button>
                    )}
                </div>
            </div>

            {/* Tracking Information */}
            {trackingData &&
                trackingData.history &&
                trackingData.history.length > 0 && (
                    <div className="mt-6 bg-white shadow rounded-lg p-6">
                        <h2 className="text-lg font-medium text-gray-900 mb-4">
                            Tracking History
                        </h2>
                        <div className="space-y-4">
                            {trackingData.history.map((event, index) => (
                                <div key={index} className="relative pl-8 pb-4">
                                    {/* Timeline dot */}
                                    <div className="absolute left-0 top-1">
                                        <div
                                            className={`w-3 h-3 rounded-full ${
                                                index === 0
                                                    ? "bg-blue-600"
                                                    : "bg-gray-300"
                                            }`}
                                        ></div>
                                    </div>
                                    {/* Timeline line */}
                                    {index <
                                        trackingData.history.length - 1 && (
                                        <div className="absolute left-1.5 top-4 bottom-0 w-0.5 bg-gray-200"></div>
                                    )}

                                    <div className="bg-gray-50 rounded-lg p-4">
                                        <div className="flex items-start justify-between">
                                            <div className="flex-1">
                                                <div className="text-sm font-medium text-gray-900 mb-1">
                                                    <span
                                                        className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(
                                                            event.status
                                                        )}`}
                                                    >
                                                        {event.status
                                                            ?.charAt(0)
                                                            .toUpperCase() +
                                                            event.status?.slice(
                                                                1
                                                            )}
                                                    </span>
                                                </div>
                                                <div className="text-sm text-gray-700 mb-2">
                                                    {event.note}
                                                </div>
                                                {event.service_type && (
                                                    <div className="text-xs text-gray-500 mb-1">
                                                        Service:{" "}
                                                        {event.service_type.toUpperCase()}
                                                    </div>
                                                )}
                                            </div>
                                            <div className="text-xs text-gray-500 ml-4 text-right">
                                                {formatDateTime(
                                                    event.updated_at
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>

                        {/* Additional tracking info */}
                        {trackingData.link && (
                            <div className="mt-4 pt-4 border-t border-gray-200">
                                <a
                                    href={trackingData.link}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="inline-flex items-center text-sm text-blue-600 hover:text-blue-500"
                                >
                                    <svg
                                        className="w-4 h-4 mr-1"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth={2}
                                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                                        />
                                    </svg>
                                    View full tracking details on Biteship
                                </a>
                            </div>
                        )}
                    </div>
                )}

            {/* Cancel Modal */}
            {showCancelModal && (
                <div className="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
                    <div className="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                        <h3 className="text-lg font-medium text-gray-900 mb-4">
                            Cancel Order
                        </h3>
                        <p className="text-sm text-gray-600 mb-4">
                            Are you sure you want to cancel this order? This
                            action cannot be undone.
                        </p>
                        <div className="mb-4">
                            <label className="block text-sm font-medium text-gray-700">
                                Reason for cancellation (optional)
                            </label>
                            <textarea
                                value={cancelReason}
                                onChange={(e) =>
                                    setCancelReason(e.target.value)
                                }
                                rows={3}
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Enter reason for cancellation..."
                            />
                        </div>
                        <div className="flex justify-end space-x-4">
                            <button
                                onClick={() => setShowCancelModal(false)}
                                className="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Keep Order
                            </button>
                            <button
                                onClick={handleCancelOrder}
                                disabled={actionLoading}
                                className="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700 disabled:opacity-50"
                            >
                                {actionLoading
                                    ? "Cancelling..."
                                    : "Cancel Order"}
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

export default OrderDetails;
