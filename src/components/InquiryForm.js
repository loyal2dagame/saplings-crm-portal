import React, { useState } from 'react';
import { TextField, Button, Snackbar, Alert, Box } from '@mui/material';

const InquiryForm = ({ onClose }) => {
    const initialFormState = { firstName: '', lastName: '', email: '', inquiry: '' };
    const [formData, setFormData] = useState(initialFormState);
    const [snackbar, setSnackbar] = useState({ open: false, message: '', severity: 'success' });

    const handleChange = (e) => {
        setFormData({ ...formData, [e.target.name]: e.target.value });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            const response = await fetch('/api/process-inquiry', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData),
            });
            const result = await response.json();

            if (response.ok) {
                setSnackbar({ open: true, message: result.message, severity: 'success' });
                setFormData(initialFormState); // Clear form on success
            } else {
                setSnackbar({ open: true, message: result.error, severity: 'error' });
            }
        } catch (error) {
            setSnackbar({ open: true, message: 'An unexpected error occurred.', severity: 'error' });
        }
    };

    const handleReset = () => {
        setFormData(initialFormState); // Clear form contents
        if (onClose) onClose(); // Close the form if onClose is provided
    };

    const handleCloseSnackbar = () => {
        setSnackbar({ ...snackbar, open: false });
    };

    return (
        <div>
            <h1 style={{ textAlign: 'center', marginBottom: '20px' }}>THIS IS A TEST</h1>
            <form onSubmit={handleSubmit}>
                <TextField
                    label="First Name"
                    name="firstName"
                    value={formData.firstName}
                    onChange={handleChange}
                    fullWidth
                    margin="normal"
                />
                <TextField
                    label="Last Name"
                    name="lastName"
                    value={formData.lastName}
                    onChange={handleChange}
                    fullWidth
                    margin="normal"
                />
                <TextField
                    label="Email"
                    name="email"
                    value={formData.email}
                    onChange={handleChange}
                    fullWidth
                    margin="normal"
                />
                <TextField
                    label="Inquiry"
                    name="inquiry"
                    value={formData.inquiry}
                    onChange={handleChange}
                    fullWidth
                    multiline
                    rows={4}
                    margin="normal"
                />
                <Box display="flex" justifyContent="space-between" mt={2}>
                    <Button variant="outlined" color="secondary" onClick={handleReset}>
                        Clear Form
                    </Button>
                    <Button type="submit" variant="contained" color="primary">
                        Submit
                    </Button>
                </Box>

                <Snackbar
                    open={snackbar.open}
                    autoHideDuration={6000}
                    onClose={handleCloseSnackbar}
                    anchorOrigin={{ vertical: 'bottom', horizontal: 'center' }}
                >
                    <Alert onClose={handleCloseSnackbar} severity={snackbar.severity} sx={{ width: '100%' }}>
                        {snackbar.message}
                    </Alert>
                </Snackbar>
            </form>
        </div>
    );
};

export default InquiryForm;
