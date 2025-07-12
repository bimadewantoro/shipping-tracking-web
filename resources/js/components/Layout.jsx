import React from "react";
import { Link, useLocation } from "react-router-dom";
import { useAuth } from "../contexts/AuthContext";

const Layout = ({ children }) => {
    const { user, logout } = useAuth();
    const location = useLocation();

    const handleLogout = async () => {
        await logout();
    };

    const isActive = (path) => {
        return location.pathname === path
            ? "bg-blue-100 text-blue-700"
            : "text-gray-600 hover:text-gray-900";
    };

    return (
        <div className="min-h-screen bg-gray-50">
            {/* Navigation */}
            <nav className="bg-white shadow-sm border-b">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between h-16">
                        <div className="flex items-center">
                            <Link
                                to="/"
                                className="flex-shrink-0 flex items-center"
                            >
                                <h1 className="text-xl font-bold text-gray-900">
                                    Shipping Tracker
                                </h1>
                            </Link>

                            {user && (
                                <div className="ml-10 flex items-baseline space-x-4">
                                    <Link
                                        to="/dashboard"
                                        className={`px-3 py-2 rounded-md text-sm font-medium ${isActive(
                                            "/dashboard"
                                        )}`}
                                    >
                                        Dashboard
                                    </Link>
                                    <Link
                                        to="/orders"
                                        className={`px-3 py-2 rounded-md text-sm font-medium ${isActive(
                                            "/orders"
                                        )}`}
                                    >
                                        Orders
                                    </Link>
                                    {user.role === "admin" && (
                                        <Link
                                            to="/users"
                                            className={`px-3 py-2 rounded-md text-sm font-medium ${isActive(
                                                "/users"
                                            )}`}
                                        >
                                            Users
                                        </Link>
                                    )}
                                </div>
                            )}
                        </div>

                        <div className="flex items-center space-x-4">
                            {!user ? (
                                <>
                                    <Link
                                        to="/track"
                                        className="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
                                    >
                                        Track Package
                                    </Link>
                                    <Link
                                        to="/login"
                                        className="text-blue-600 hover:text-blue-500 px-3 py-2 rounded-md text-sm font-medium"
                                    >
                                        Login
                                    </Link>
                                    <Link
                                        to="/register"
                                        className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium"
                                    >
                                        Register
                                    </Link>
                                </>
                            ) : (
                                <div className="flex items-center space-x-4">
                                    <span className="text-sm text-gray-700">
                                        Welcome, {user.name}
                                    </span>
                                    <Link
                                        to="/profile"
                                        className="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
                                    >
                                        Profile
                                    </Link>
                                    <button
                                        onClick={handleLogout}
                                        className="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
                                    >
                                        Logout
                                    </button>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </nav>

            {/* Main Content */}
            <main className="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
                {children}
            </main>
        </div>
    );
};

export default Layout;
