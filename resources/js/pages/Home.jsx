import React from "react";
import { Link } from "react-router-dom";
import { useAuth } from "../contexts/AuthContext";

const Home = () => {
    const { user } = useAuth();

    return (
        <div className="px-4 py-16 mx-auto sm:max-w-xl md:max-w-full lg:max-w-screen-xl md:px-24 lg:px-8 lg:py-20">
            <div className="max-w-xl sm:mx-auto lg:max-w-2xl">
                <div className="flex flex-col mb-16 sm:text-center sm:mb-0">
                    <div className="mb-6 sm:mx-auto">
                        <div className="flex items-center justify-center w-12 h-12 rounded-full bg-blue-50">
                            <svg
                                className="w-6 h-6 text-blue-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"
                                />
                            </svg>
                        </div>
                    </div>
                    <div className="max-w-xl mb-10 md:mx-auto sm:text-center lg:max-w-2xl md:mb-12">
                        <h2 className="max-w-lg mb-6 font-sans text-3xl font-bold leading-none tracking-tight text-gray-900 sm:text-4xl md:mx-auto">
                            <span className="relative inline-block">
                                <span className="relative">Ship</span>
                            </span>{" "}
                            and track your packages with ease
                        </h2>
                        <p className="text-base text-gray-700 md:text-lg">
                            A comprehensive shipping management system that
                            helps you create, track, and manage your shipments
                            with real-time updates and seamless Biteship
                            integration.
                        </p>
                    </div>
                    <div className="flex flex-col items-center sm:flex-row sm:justify-center space-y-4 sm:space-y-0 sm:space-x-4">
                        {user ? (
                            <>
                                <Link
                                    to="/dashboard"
                                    className="inline-flex items-center justify-center w-full h-12 px-6 font-medium tracking-wide text-white transition duration-200 rounded shadow-md bg-blue-600 hover:bg-blue-700 focus:shadow-outline focus:outline-none sm:w-auto"
                                >
                                    Go to Dashboard
                                </Link>
                                <Link
                                    to="/orders/create"
                                    className="inline-flex items-center justify-center w-full h-12 px-6 font-medium tracking-wide text-blue-600 transition duration-200 rounded shadow-md bg-white border border-blue-600 hover:bg-blue-50 focus:shadow-outline focus:outline-none sm:w-auto"
                                >
                                    Create Order
                                </Link>
                            </>
                        ) : (
                            <>
                                <Link
                                    to="/register"
                                    className="inline-flex items-center justify-center w-full h-12 px-6 font-medium tracking-wide text-white transition duration-200 rounded shadow-md bg-blue-600 hover:bg-blue-700 focus:shadow-outline focus:outline-none sm:w-auto"
                                >
                                    Get Started
                                </Link>
                                <Link
                                    to="/track"
                                    className="inline-flex items-center justify-center w-full h-12 px-6 font-medium tracking-wide text-blue-600 transition duration-200 rounded shadow-md bg-white border border-blue-600 hover:bg-blue-50 focus:shadow-outline focus:outline-none sm:w-auto"
                                >
                                    Track Package
                                </Link>
                            </>
                        )}
                    </div>
                </div>
            </div>

            {/* Features Section */}
            <div className="mt-20">
                <div className="grid gap-8 row-gap-5 md:row-gap-8 lg:grid-cols-3">
                    <div className="p-5 duration-300 transform bg-white border-2 border-dashed rounded shadow-sm border-gray-300 hover:border-blue-500 hover:shadow-lg">
                        <div className="flex items-center mb-2">
                            <div className="flex items-center justify-center w-10 h-10 mr-2 rounded-full bg-blue-100">
                                <svg
                                    className="w-6 h-6 text-blue-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                    />
                                </svg>
                            </div>
                            <h6 className="font-semibold leading-5">
                                Order Management
                            </h6>
                        </div>
                        <p className="text-sm text-gray-700">
                            Create and manage shipping orders with detailed
                            tracking information and status updates.
                        </p>
                    </div>
                    <div className="p-5 duration-300 transform bg-white border-2 border-dashed rounded shadow-sm border-gray-300 hover:border-blue-500 hover:shadow-lg">
                        <div className="flex items-center mb-2">
                            <div className="flex items-center justify-center w-10 h-10 mr-2 rounded-full bg-green-100">
                                <svg
                                    className="w-6 h-6 text-green-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                    />
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                    />
                                </svg>
                            </div>
                            <h6 className="font-semibold leading-5">
                                Real-time Tracking
                            </h6>
                        </div>
                        <p className="text-sm text-gray-700">
                            Track your packages in real-time with updates from
                            multiple courier services through Biteship API.
                        </p>
                    </div>
                    <div className="p-5 duration-300 transform bg-white border-2 border-dashed rounded shadow-sm border-gray-300 hover:border-blue-500 hover:shadow-lg">
                        <div className="flex items-center mb-2">
                            <div className="flex items-center justify-center w-10 h-10 mr-2 rounded-full bg-purple-100">
                                <svg
                                    className="w-6 h-6 text-purple-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"
                                    />
                                </svg>
                            </div>
                            <h6 className="font-semibold leading-5">
                                User Management
                            </h6>
                        </div>
                        <p className="text-sm text-gray-700">
                            Role-based access control with admin and user roles
                            for secure and organized access management.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Home;
