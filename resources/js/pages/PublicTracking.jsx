import React, { useState } from "react";
import orderService from "../services/orderService";

const PublicTracking = () => {
    const [formData, setFormData] = useState({
        waybill_id: "",
        courier_code: "jne",
    });
    const [trackingData, setTrackingData] = useState(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState("");

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData((prev) => ({
            ...prev,
            [name]: value,
        }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setError("");
        setTrackingData(null);

        try {
            const response = await orderService.publicTracking(
                formData.waybill_id,
                formData.courier_code
            );

            if (response.status === "success") {
                setTrackingData(response.data);
            } else {
                setError(response.message || "Failed to track package");
            }
        } catch (err) {
            console.error("Failed to track package:", err);
            setError(
                err.response?.data?.message ||
                    "Failed to track package. Please check your tracking details."
            );
        } finally {
            setLoading(false);
        }
    };

    const getStatusColor = (status) => {
        const lowerStatus = status?.toLowerCase() || "";
        if (
            lowerStatus.includes("delivered") ||
            lowerStatus.includes("selesai")
        ) {
            return "text-green-600 bg-green-100";
        }
        if (
            lowerStatus.includes("on the way") ||
            lowerStatus.includes("dalam perjalanan")
        ) {
            return "text-blue-600 bg-blue-100";
        }
        if (
            lowerStatus.includes("picked up") ||
            lowerStatus.includes("diambil")
        ) {
            return "text-yellow-600 bg-yellow-100";
        }
        return "text-gray-600 bg-gray-100";
    };

    return (
        <div className="min-h-screen bg-gray-50 py-12">
            <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                {/* Header */}
                <div className="text-center mb-8">
                    <h1 className="text-3xl font-bold text-gray-900">
                        Track Your Package
                    </h1>
                    <p className="mt-2 text-lg text-gray-600">
                        Enter your waybill number and courier to track your
                        shipment
                    </p>
                </div>

                {/* Tracking Form */}
                <div className="bg-white shadow rounded-lg p-6 mb-8">
                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label className="block text-sm font-medium text-gray-700">
                                    Waybill *
                                </label>
                                <input
                                    type="text"
                                    name="waybill_id"
                                    value={formData.waybill_id}
                                    onChange={handleChange}
                                    required
                                    placeholder="Enter your tracking number"
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700">
                                    Courier *
                                </label>
                                <select
                                    name="courier_code"
                                    value={formData.courier_code}
                                    onChange={handleChange}
                                    required
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                    <option value="jne">JNE</option>
                                    <option value="pos">POS Indonesia</option>
                                    <option value="tiki">TIKI</option>
                                    <option value="jnt">J&T Express</option>
                                    <option value="sicepat">SiCepat</option>
                                    <option value="anteraja">AnterAja</option>
                                    <option value="ninja">Ninja Xpress</option>
                                    <option value="lion">Lion Parcel</option>
                                    <option value="pcp">PCP Express</option>
                                </select>
                            </div>
                        </div>
                        <div className="flex justify-center">
                            <button
                                type="submit"
                                disabled={loading}
                                className="px-8 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                {loading ? (
                                    <div className="flex items-center">
                                        <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                                        Tracking...
                                    </div>
                                ) : (
                                    "Track Package"
                                )}
                            </button>
                        </div>
                    </form>
                </div>

                {/* Error Message */}
                {error && (
                    <div className="bg-red-50 border border-red-200 rounded-md p-4 mb-8">
                        <div className="flex">
                            <div className="flex-shrink-0">
                                <svg
                                    className="h-5 w-5 text-red-400"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fillRule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clipRule="evenodd"
                                    />
                                </svg>
                            </div>
                            <div className="ml-3">
                                <p className="text-sm text-red-800">{error}</p>
                            </div>
                        </div>
                    </div>
                )}

                {/* Tracking Results */}
                {trackingData && (
                    <div className="bg-white shadow rounded-lg p-6">
                        <h2 className="text-xl font-bold text-gray-900 mb-6">
                            Tracking Results
                        </h2>

                        {/* Package Information */}
                        <div className="mb-6 p-4 bg-gray-50 rounded-lg">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p className="text-sm font-medium text-gray-700">
                                        Waybill Number
                                    </p>
                                    <p className="text-lg font-semibold text-gray-900">
                                        {trackingData.waybill_id}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-sm font-medium text-gray-700">
                                        Courier
                                    </p>
                                    <p className="text-lg font-semibold text-gray-900">
                                        {trackingData.courier?.name ||
                                            formData.courier_code.toUpperCase()}
                                    </p>
                                </div>
                                {trackingData.status && (
                                    <div>
                                        <p className="text-sm font-medium text-gray-700">
                                            Current Status
                                        </p>
                                        <span
                                            className={`inline-flex px-3 py-1 text-sm font-semibold rounded-full ${getStatusColor(
                                                trackingData.status
                                            )}`}
                                        >
                                            {trackingData.status}
                                        </span>
                                    </div>
                                )}
                                {trackingData.estimated_delivery && (
                                    <div>
                                        <p className="text-sm font-medium text-gray-700">
                                            Estimated Delivery
                                        </p>
                                        <p className="text-lg font-semibold text-gray-900">
                                            {new Date(
                                                trackingData.estimated_delivery
                                            ).toLocaleDateString()}
                                        </p>
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Tracking History */}
                        {trackingData.history &&
                            trackingData.history.length > 0 && (
                                <div>
                                    <h3 className="text-lg font-medium text-gray-900 mb-4">
                                        Tracking History
                                    </h3>
                                    <div className="space-y-4">
                                        {trackingData.history.map(
                                            (event, index) => (
                                                <div
                                                    key={index}
                                                    className="relative flex items-start space-x-3"
                                                >
                                                    <div className="flex-shrink-0">
                                                        <div
                                                            className={`h-8 w-8 rounded-full flex items-center justify-center ${
                                                                index === 0
                                                                    ? "bg-blue-500"
                                                                    : "bg-gray-400"
                                                            }`}
                                                        >
                                                            <div className="h-2 w-2 bg-white rounded-full"></div>
                                                        </div>
                                                    </div>
                                                    <div className="min-w-0 flex-1">
                                                        <div className="text-sm">
                                                            <p className="font-medium text-gray-900">
                                                                {event.status}
                                                            </p>
                                                            {event.note && (
                                                                <p className="text-gray-500 mt-1">
                                                                    {event.note}
                                                                </p>
                                                            )}
                                                            {event.location && (
                                                                <p className="text-gray-500 mt-1">
                                                                    📍{" "}
                                                                    {
                                                                        event.location
                                                                    }
                                                                </p>
                                                            )}
                                                        </div>
                                                        <div className="text-sm text-gray-500 mt-2">
                                                            {event.date_time
                                                                ? new Date(
                                                                      event.date_time
                                                                  ).toLocaleString()
                                                                : ""}
                                                        </div>
                                                    </div>
                                                </div>
                                            )
                                        )}
                                    </div>
                                </div>
                            )}

                        {/* No History Available */}
                        {trackingData.history &&
                            trackingData.history.length === 0 && (
                                <div className="text-center py-8">
                                    <div className="text-gray-400 text-6xl mb-4">
                                        📦
                                    </div>
                                    <p className="text-gray-500">
                                        No tracking history available yet.
                                    </p>
                                </div>
                            )}
                    </div>
                )}

                {/* Instructions */}
                <div className="mt-8 bg-blue-50 border border-blue-200 rounded-md p-4">
                    <h3 className="text-sm font-medium text-blue-900 mb-2">
                        How to track your package:
                    </h3>
                    <ul className="text-sm text-blue-800 space-y-1">
                        <li>
                            • Enter your waybill/tracking number (e.g.,
                            JNE123456789)
                        </li>
                        <li>• Select the correct courier service</li>
                        <li>
                            • Click "Track Package" to see your shipment status
                        </li>
                        <li>• Bookmark this page for easy tracking</li>
                    </ul>
                </div>
            </div>
        </div>
    );
};

export default PublicTracking;
