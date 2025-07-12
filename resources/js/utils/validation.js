// Validation utilities for forms
export const validators = {
    required: (value) => {
        if (!value || (typeof value === "string" && !value.trim())) {
            return "This field is required";
        }
        return null;
    },

    email: (value) => {
        if (!value) return null;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            return "Please enter a valid email address";
        }
        return null;
    },

    phone: (value) => {
        if (!value) return null;
        const phoneRegex = /^(\+62|62|0)[0-9]{9,12}$/;
        if (!phoneRegex.test(value.replace(/\s/g, ""))) {
            return "Please enter a valid Indonesian phone number";
        }
        return null;
    },

    minLength: (min) => (value) => {
        if (!value) return null;
        if (value.length < min) {
            return `This field must be at least ${min} characters long`;
        }
        return null;
    },

    maxLength: (max) => (value) => {
        if (!value) return null;
        if (value.length > max) {
            return `This field must not exceed ${max} characters`;
        }
        return null;
    },

    numeric: (value) => {
        if (!value) return null;
        if (isNaN(Number(value)) || Number(value) < 0) {
            return "Please enter a valid positive number";
        }
        return null;
    },

    weight: (value) => {
        if (!value) return null;
        const weight = Number(value);
        if (isNaN(weight) || weight <= 0 || weight > 30000) {
            return "Weight must be between 1g and 30kg (30000g)";
        }
        return null;
    },

    dimensions: (value) => {
        if (!value) return null;
        const dimension = Number(value);
        if (isNaN(dimension) || dimension <= 0 || dimension > 1000) {
            return "Dimension must be between 1cm and 1000cm";
        }
        return null;
    },

    postalCode: (value) => {
        if (!value) return null;
        const postalCodeRegex = /^[0-9]{5}$/;
        if (!postalCodeRegex.test(value)) {
            return "Please enter a valid 5-digit postal code";
        }
        return null;
    },
};

// Validate a single field
export const validateField = (value, validationRules) => {
    if (!Array.isArray(validationRules)) {
        validationRules = [validationRules];
    }

    for (const rule of validationRules) {
        const error = rule(value);
        if (error) {
            return error;
        }
    }

    return null;
};

// Validate entire form
export const validateForm = (formData, validationSchema) => {
    const errors = {};
    let isValid = true;

    Object.keys(validationSchema).forEach((field) => {
        const error = validateField(formData[field], validationSchema[field]);
        if (error) {
            errors[field] = error;
            isValid = false;
        }
    });

    return { isValid, errors };
};

// Format currency for Indonesian Rupiah
export const formatCurrency = (amount) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(amount || 0);
};

// Format date for display
export const formatDate = (dateString, options = {}) => {
    const defaultOptions = {
        year: "numeric",
        month: "long",
        day: "numeric",
        ...options,
    };

    return new Intl.DateTimeFormat("id-ID", defaultOptions).format(
        new Date(dateString)
    );
};

// Format date with time
export const formatDateTime = (dateString) => {
    return new Intl.DateTimeFormat("id-ID", {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    }).format(new Date(dateString));
};

// Debounce function for search
export const debounce = (func, wait) => {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
};
