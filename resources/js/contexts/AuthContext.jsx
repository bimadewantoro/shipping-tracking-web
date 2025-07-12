import React, { createContext, useContext, useState, useEffect } from "react";
import api from "../services/api";
import authService from "../services/authService";

const AuthContext = createContext();

export const useAuth = () => {
    const context = useContext(AuthContext);
    if (!context) {
        throw new Error("useAuth must be used within an AuthProvider");
    }
    return context;
};

export const AuthProvider = ({ children }) => {
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(true);
    const [token, setToken] = useState(localStorage.getItem("token"));

    useEffect(() => {
        if (token) {
            api.defaults.headers.common["Authorization"] = `Bearer ${token}`;
            fetchUser();
        } else {
            setLoading(false);
        }
    }, [token]);

    const fetchUser = async () => {
        try {
            const response = await authService.me();
            if (response.status === "success") {
                setUser(response.data.user);
            }
        } catch (error) {
            console.error("Failed to fetch user:", error);
            logout();
        } finally {
            setLoading(false);
        }
    };

    const login = async (credentials) => {
        const response = await authService.login(credentials);
        if (response.status === "success") {
            const { token, user } = response.data;
            setToken(token);
            setUser(user);
            localStorage.setItem("token", token);
            api.defaults.headers.common["Authorization"] = `Bearer ${token}`;
            return { success: true };
        }
        return { success: false, message: response.message };
    };

    const register = async (userData) => {
        const response = await authService.register(userData);
        if (response.status === "success") {
            const { token, user } = response.data;
            setToken(token);
            setUser(user);
            localStorage.setItem("token", token);
            api.defaults.headers.common["Authorization"] = `Bearer ${token}`;
            return { success: true };
        }
        return { success: false, message: response.message };
    };

    const logout = async () => {
        try {
            if (token) {
                await authService.logout();
            }
        } catch (error) {
            console.error("Logout error:", error);
        } finally {
            setToken(null);
            setUser(null);
            localStorage.removeItem("token");
            delete api.defaults.headers.common["Authorization"];
        }
    };

    const isAdmin = () => {
        return user?.role === "admin";
    };

    const value = {
        user,
        token,
        loading,
        login,
        register,
        logout,
        isAdmin,
    };

    return (
        <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
    );
};
