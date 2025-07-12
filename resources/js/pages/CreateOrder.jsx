import React, { useState } from "react";
import { useNavigate } from "react-router-dom";
import orderService from "../services/orderService";

const CreateOrder = () => {
    const navigate = useNavigate();
    const [loading, setLoading] = useState(false);
    const [formData, setFormData] = useState({
        sender_name: "",
        sender_phone: "",
        sender_address: "",
        sender_postal_code: "",
        sender_area_id: "",
        sender_latitude: "",
        sender_longitude: "",
        receiver_name: "",
        receiver_phone: "",
        receiver_address: "",
        receiver_postal_code: "",
        receiver_area_id: "",
        receiver_latitude: "",
        receiver_longitude: "",
        package_type: "package",
        package_weight: "",
        package_length: "",
        package_width: "",
        package_height: "",
        package_description: "",
        package_value: "",
        courier_code: "jne",
        courier_service: "reg",
        insurance_cost: "",
        notes: "",
        auto_create_biteship_order: false,
    });

    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;
        setFormData((prev) => ({
            ...prev,
            [name]: type === "checkbox" ? checked : value,
        }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);

        try {
            const response = await orderService.createOrder(formData);
            if (response.status === "success") {
                navigate("/orders");
            }
        } catch (error) {
            console.error("Failed to create order:", error);
            alert("Failed to create order. Please try again.");
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="px-4 py-6">
            <div className="mb-6">
                <h1 className="text-2xl font-bold text-gray-900">
                    Create New Order
                </h1>
                <p className="mt-1 text-sm text-gray-600">
                    Fill in the details below to create a new shipping order.
                </p>
            </div>

            <form onSubmit={handleSubmit} className="space-y-8">
                {/* Sender Information */}
                <div className="bg-white shadow rounded-lg p-6">
                    <h2 className="text-lg font-medium text-gray-900 mb-4">
                        Sender Information
                    </h2>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label className="block text-sm font-medium text-gray-700">
                                Name *
                            </label>
                            <input
                                type="text"
                                name="sender_name"
                                value={formData.sender_name}
                                onChange={handleChange}
                                required
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700">
                                Phone *
                            </label>
                            <input
                                type="tel"
                                name="sender_phone"
                                value={formData.sender_phone}
                                onChange={handleChange}
                                required
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                        <div className="md:col-span-2">
                            <label className="block text-sm font-medium text-gray-700">
                                Address *
                            </label>
                            <textarea
                                name="sender_address"
                                value={formData.sender_address}
                                onChange={handleChange}
                                required
                                rows={3}
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700">
                                Postal Code *
                            </label>
                            <input
                                type="text"
                                name="sender_postal_code"
                                value={formData.sender_postal_code}
                                onChange={handleChange}
                                required
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700">
                                Area ID
                            </label>
                            <input
                                type="text"
                                name="sender_area_id"
                                value={formData.sender_area_id}
                                onChange={handleChange}
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                    </div>
                </div>

                {/* Receiver Information */}
                <div className="bg-white shadow rounded-lg p-6">
                    <h2 className="text-lg font-medium text-gray-900 mb-4">
                        Receiver Information
                    </h2>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label className="block text-sm font-medium text-gray-700">
                                Name *
                            </label>
                            <input
                                type="text"
                                name="receiver_name"
                                value={formData.receiver_name}
                                onChange={handleChange}
                                required
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700">
                                Phone *
                            </label>
                            <input
                                type="tel"
                                name="receiver_phone"
                                value={formData.receiver_phone}
                                onChange={handleChange}
                                required
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                        <div className="md:col-span-2">
                            <label className="block text-sm font-medium text-gray-700">
                                Address *
                            </label>
                            <textarea
                                name="receiver_address"
                                value={formData.receiver_address}
                                onChange={handleChange}
                                required
                                rows={3}
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700">
                                Postal Code *
                            </label>
                            <input
                                type="text"
                                name="receiver_postal_code"
                                value={formData.receiver_postal_code}
                                onChange={handleChange}
                                required
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700">
                                Area ID
                            </label>
                            <input
                                type="text"
                                name="receiver_area_id"
                                value={formData.receiver_area_id}
                                onChange={handleChange}
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                    </div>
                </div>

                {/* Package Information */}
                <div className="bg-white shadow rounded-lg p-6">
                    <h2 className="text-lg font-medium text-gray-900 mb-4">
                        Package Information
                    </h2>
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <label className="block text-sm font-medium text-gray-700">
                                Package Type
                            </label>
                            <select
                                name="package_type"
                                value={formData.package_type}
                                onChange={handleChange}
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="package">Package</option>
                                <option value="document">Document</option>
                            </select>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700">
                                Weight (grams) *
                            </label>
                            <input
                                type="number"
                                name="package_weight"
                                value={formData.package_weight}
                                onChange={handleChange}
                                required
                                min="1"
                                max="50000"
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700">
                                Length (cm)
                            </label>
                            <input
                                type="number"
                                name="package_length"
                                value={formData.package_length}
                                onChange={handleChange}
                                min="1"
                                max="200"
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700">
                                Width (cm)
                            </label>
                            <input
                                type="number"
                                name="package_width"
                                value={formData.package_width}
                                onChange={handleChange}
                                min="1"
                                max="200"
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700">
                                Height (cm)
                            </label>
                            <input
                                type="number"
                                name="package_height"
                                value={formData.package_height}
                                onChange={handleChange}
                                min="1"
                                max="200"
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                        <div className="md:col-span-2">
                            <label className="block text-sm font-medium text-gray-700">
                                Description
                            </label>
                            <input
                                type="text"
                                name="package_description"
                                value={formData.package_description}
                                onChange={handleChange}
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700">
                                Value (IDR)
                            </label>
                            <input
                                type="number"
                                name="package_value"
                                value={formData.package_value}
                                onChange={handleChange}
                                min="0"
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                    </div>
                </div>

                {/* Shipping Information */}
                <div className="bg-white shadow rounded-lg p-6">
                    <h2 className="text-lg font-medium text-gray-900 mb-4">
                        Shipping Information
                    </h2>
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
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
                            </select>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700">
                                Service *
                            </label>
                            <select
                                name="courier_service"
                                value={formData.courier_service}
                                onChange={handleChange}
                                required
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="reg">Regular</option>
                                <option value="express">Express</option>
                                <option value="ekonomi">Economy</option>
                            </select>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700">
                                Insurance Cost (IDR)
                            </label>
                            <input
                                type="number"
                                name="insurance_cost"
                                value={formData.insurance_cost}
                                onChange={handleChange}
                                min="0"
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                        <div className="md:col-span-3">
                            <label className="block text-sm font-medium text-gray-700">
                                Notes
                            </label>
                            <textarea
                                name="notes"
                                value={formData.notes}
                                onChange={handleChange}
                                rows={3}
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                        <div className="md:col-span-3">
                            <div className="flex items-center">
                                <input
                                    id="auto_create_biteship_order"
                                    name="auto_create_biteship_order"
                                    type="checkbox"
                                    checked={
                                        formData.auto_create_biteship_order
                                    }
                                    onChange={handleChange}
                                    className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                />
                                <label
                                    htmlFor="auto_create_biteship_order"
                                    className="ml-2 block text-sm text-gray-900"
                                >
                                    Automatically create Biteship order
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Submit Button */}
                <div className="flex justify-end space-x-4">
                    <button
                        type="button"
                        onClick={() => navigate("/orders")}
                        className="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        disabled={loading}
                        className="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                    >
                        {loading ? "Creating..." : "Create Order"}
                    </button>
                </div>
            </form>
        </div>
    );
};

export default CreateOrder;
