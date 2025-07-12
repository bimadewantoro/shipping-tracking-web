import React, { useState, useEffect } from "react";
import { useAuth } from "../contexts/AuthContext";
import userService from "../services/userService";

const Profile = () => {
    const { user, logout } = useAuth();
    const [loading, setLoading] = useState(false);
    const [profileData, setProfileData] = useState({
        name: "",
        email: "",
        phone: "",
    });
    const [passwordData, setPasswordData] = useState({
        current_password: "",
        password: "",
        password_confirmation: "",
    });

    useEffect(() => {
        if (user) {
            setProfileData({
                name: user.name || "",
                email: user.email || "",
                phone: user.phone || "",
            });
        }
    }, [user]);

    const handleProfileChange = (e) => {
        const { name, value } = e.target;
        setProfileData((prev) => ({
            ...prev,
            [name]: value,
        }));
    };

    const handlePasswordChange = (e) => {
        const { name, value } = e.target;
        setPasswordData((prev) => ({
            ...prev,
            [name]: value,
        }));
    };

    const handleUpdateProfile = async (e) => {
        e.preventDefault();
        setLoading(true);

        try {
            const response = await userService.updateProfile(profileData);
            if (response.status === "success") {
                alert("Profile updated successfully!");
                // Update the user context if needed
                window.location.reload();
            }
        } catch (error) {
            console.error("Failed to update profile:", error);
            alert("Failed to update profile");
        } finally {
            setLoading(false);
        }
    };

    const handleUpdatePassword = async (e) => {
        e.preventDefault();

        if (passwordData.password !== passwordData.password_confirmation) {
            alert("Passwords do not match");
            return;
        }

        setLoading(true);

        try {
            const response = await userService.updatePassword(passwordData);
            if (response.status === "success") {
                alert("Password updated successfully! Please log in again.");
                setPasswordData({
                    current_password: "",
                    password: "",
                    password_confirmation: "",
                });
                // Force logout and redirect to login
                await logout();
                window.location.href = "/login";
            }
        } catch (error) {
            console.error("Failed to update password:", error);
            const errorMessage =
                error.response?.data?.message || "Failed to update password";
            alert(errorMessage);
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="px-4 py-6">
            {/* Header */}
            <div className="mb-6">
                <h1 className="text-2xl font-bold text-gray-900">
                    Profile Settings
                </h1>
                <p className="mt-1 text-sm text-gray-600">
                    Manage your account settings and preferences.
                </p>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {/* Profile Information */}
                <div className="bg-white shadow rounded-lg p-6">
                    <h2 className="text-lg font-medium text-gray-900 mb-4">
                        Profile Information
                    </h2>
                    <form onSubmit={handleUpdateProfile}>
                        <div className="space-y-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700">
                                    Full Name *
                                </label>
                                <input
                                    type="text"
                                    name="name"
                                    value={profileData.name}
                                    onChange={handleProfileChange}
                                    required
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700">
                                    Email Address *
                                </label>
                                <input
                                    type="email"
                                    name="email"
                                    value={profileData.email}
                                    onChange={handleProfileChange}
                                    required
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700">
                                    Phone Number
                                </label>
                                <input
                                    type="tel"
                                    name="phone"
                                    value={profileData.phone}
                                    onChange={handleProfileChange}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700">
                                    Role
                                </label>
                                <input
                                    type="text"
                                    value={
                                        user?.role?.charAt(0).toUpperCase() +
                                            user?.role?.slice(1) || ""
                                    }
                                    disabled
                                    className="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm"
                                />
                            </div>
                        </div>
                        <div className="mt-6">
                            <button
                                type="submit"
                                disabled={loading}
                                className="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                            >
                                {loading ? "Updating..." : "Update Profile"}
                            </button>
                        </div>
                    </form>
                </div>

                {/* Password Change */}
                <div className="bg-white shadow rounded-lg p-6">
                    <h2 className="text-lg font-medium text-gray-900 mb-4">
                        Change Password
                    </h2>
                    <form onSubmit={handleUpdatePassword}>
                        <div className="space-y-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700">
                                    Current Password *
                                </label>
                                <input
                                    type="password"
                                    name="current_password"
                                    value={passwordData.current_password}
                                    onChange={handlePasswordChange}
                                    required
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700">
                                    New Password *
                                </label>
                                <input
                                    type="password"
                                    name="password"
                                    value={passwordData.password}
                                    onChange={handlePasswordChange}
                                    required
                                    minLength="8"
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                                <p className="mt-1 text-sm text-gray-500">
                                    Password must be at least 8 characters long.
                                </p>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700">
                                    Confirm New Password *
                                </label>
                                <input
                                    type="password"
                                    name="password_confirmation"
                                    value={passwordData.password_confirmation}
                                    onChange={handlePasswordChange}
                                    required
                                    minLength="8"
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                            </div>
                        </div>
                        <div className="mt-6">
                            <button
                                type="submit"
                                disabled={loading}
                                className="w-full sm:w-auto px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50"
                            >
                                {loading ? "Updating..." : "Change Password"}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {/* Account Information */}
            <div className="mt-6 bg-white shadow rounded-lg p-6">
                <h2 className="text-lg font-medium text-gray-900 mb-4">
                    Account Information
                </h2>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700">
                            Member Since
                        </label>
                        <p className="mt-1 text-sm text-gray-900">
                            {user?.created_at
                                ? new Date(user.created_at).toLocaleDateString()
                                : "N/A"}
                        </p>
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700">
                            Last Updated
                        </label>
                        <p className="mt-1 text-sm text-gray-900">
                            {user?.updated_at
                                ? new Date(user.updated_at).toLocaleDateString()
                                : "N/A"}
                        </p>
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700">
                            User ID
                        </label>
                        <p className="mt-1 text-sm text-gray-900 font-mono">
                            {user?.id || "N/A"}
                        </p>
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700">
                            Email Verified
                        </label>
                        <p className="mt-1 text-sm text-gray-900">
                            {user?.email_verified_at ? (
                                <span className="text-green-600">Verified</span>
                            ) : (
                                <span className="text-red-600">
                                    Not Verified
                                </span>
                            )}
                        </p>
                    </div>
                </div>
            </div>

            {/* Danger Zone */}
            <div className="mt-6 bg-white shadow rounded-lg p-6">
                <h2 className="text-lg font-medium text-red-900 mb-4">
                    Danger Zone
                </h2>
                <div className="border border-red-200 rounded-md p-4">
                    <h3 className="text-sm font-medium text-red-900">
                        Delete Account
                    </h3>
                    <p className="mt-1 text-sm text-red-700">
                        Once you delete your account, there is no going back.
                        Please be certain.
                    </p>
                    <div className="mt-4">
                        <button
                            onClick={() => {
                                if (
                                    confirm(
                                        "Are you sure you want to delete your account? This action cannot be undone."
                                    )
                                ) {
                                    alert(
                                        "Account deletion is not yet implemented. Please contact an administrator."
                                    );
                                }
                            }}
                            className="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm"
                        >
                            Delete Account
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Profile;
