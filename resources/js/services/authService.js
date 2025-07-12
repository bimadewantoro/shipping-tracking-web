import api from "./api";

const authService = {
    // Login
    login: async (credentials) => {
        const response = await api.post("/auth/login", credentials);
        return response.data;
    },

    // Register
    register: async (userData) => {
        const response = await api.post("/auth/register", userData);
        return response.data;
    },

    // Logout
    logout: async () => {
        const response = await api.post("/auth/logout");
        return response.data;
    },

    // Get current user
    me: async () => {
        const response = await api.get("/auth/me");
        return response.data;
    },
};

export default authService;
