import { useState, useCallback } from "react";

export const useApiCall = () => {
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    const execute = useCallback(async (apiCall, options = {}) => {
        const {
            onSuccess,
            onError,
            showSuccessMessage = false,
            showErrorMessage = true,
        } = options;

        setLoading(true);
        setError(null);

        try {
            const result = await apiCall();

            if (showSuccessMessage && result?.message) {
                // You could integrate with a toast system here
                console.log("Success:", result.message);
            }

            if (onSuccess) {
                onSuccess(result);
            }

            return result;
        } catch (err) {
            const errorMessage =
                err.response?.data?.message ||
                err.message ||
                "An unexpected error occurred";

            setError(errorMessage);

            if (showErrorMessage) {
                console.error("API Error:", errorMessage);
            }

            if (onError) {
                onError(err);
            }

            throw err;
        } finally {
            setLoading(false);
        }
    }, []);

    const clearError = useCallback(() => {
        setError(null);
    }, []);

    return {
        loading,
        error,
        execute,
        clearError,
    };
};

export default useApiCall;
