import axios from "axios";

const api = axios.create({
    baseURL: "/api",
    headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest",
    },
});

// Request interceptor
api.interceptors.request.use(
    (config) => {
        // Get CSRF token from meta tag
        const token = document.querySelector('meta[name="csrf-token"]');
        if (token) {
            config.headers["X-CSRF-TOKEN"] = token.getAttribute("content");
        }

        // Add Bearer token if available
        const authToken = localStorage.getItem("token");
        if (authToken && !config.headers.Authorization) {
            config.headers.Authorization = `Bearer ${authToken}`;
        }

        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Response interceptor
api.interceptors.response.use(
    (response) => response,
    (error) => {
        // Handle different types of errors
        if (error.response?.status === 401) {
            // Handle unauthorized access
            localStorage.removeItem("token");
            delete api.defaults.headers.common["Authorization"];

            // Only redirect if not already on login page
            if (!window.location.pathname.includes("/login")) {
                window.location.href = "/login";
            }
        } else if (error.response?.status === 403) {
            // Handle forbidden access
            console.error("Access forbidden:", error.response.data.message);
        } else if (error.response?.status === 422) {
            // Handle validation errors
            console.error("Validation error:", error.response.data.errors);
        } else if (error.response?.status >= 500) {
            // Handle server errors
            console.error("Server error:", error.response.data.message);
        }

        return Promise.reject(error);
    }
);

export default api;
