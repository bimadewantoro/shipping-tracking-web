import api from "./api";

const userService = {
    // Get all users (admin only)
    getUsers: async (params = {}) => {
        const response = await api.get("/users", { params });
        return response.data;
    },

    // Create user (admin only)
    createUser: async (userData) => {
        const response = await api.post("/users", userData);
        return response.data;
    },

    // Get user details (admin only)
    getUser: async (userId) => {
        const response = await api.get(`/users/${userId}`);
        return response.data;
    },

    // Update user (admin only)
    updateUser: async (userId, userData) => {
        const response = await api.put(`/users/${userId}`, userData);
        return response.data;
    },

    // Delete user (admin only)
    deleteUser: async (userId) => {
        const response = await api.delete(`/users/${userId}`);
        return response.data;
    },

    // Get own profile
    getProfile: async () => {
        const response = await api.get("/users/profile");
        return response.data;
    },

    // Update own profile
    updateProfile: async (profileData) => {
        const response = await api.put("/users/profile", profileData);
        return response.data;
    },

    // Update password
    updatePassword: async (passwordData) => {
        const response = await api.patch(
            "/users/profile/password",
            passwordData
        );
        return response.data;
    },
};

export default userService;
