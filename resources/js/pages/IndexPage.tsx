import React, { useState, useEffect, useRef } from 'react';
import { Button, Typography, Box, TextField, MenuItem, IconButton, Snackbar, Alert, AlertColor } from '@mui/material';
import DeleteIcon from '@mui/icons-material/Delete';
import SaplingsLogo from '/public/images/Saplings_Logo_Linear_For_White.svg';

export default function IndexPage() {
    const [showInquireForm, setShowInquireForm] = useState(false);
    const [showWaitlistForm, setShowWaitlistForm] = useState(false);

    // State for inquiry form
    const [inquiryForm, setInquiryForm] = useState({
        firstName: '',
        lastName: '',
        email: '',
        inquiry: '',
        location: '',
    });

    const [snackbar, setSnackbar] = useState<{ open: boolean; message: string; severity: AlertColor }>({
        open: false,
        message: '',
        severity: 'success',
    });

    const handleSnackbarClose = () => {
        setSnackbar({ ...snackbar, open: false });
    };

    const handleSubmitInquiry = async (e: React.FormEvent) => {
        e.preventDefault(); // Prevent default form submission behavior

        try {
            const response = await fetch('/process-inquiry', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify(inquiryForm),
            });

            const data = await response.json();
            if (response.ok) {
                setSnackbar({ open: true, message: 'Inquiry submitted successfully!', severity: 'success' });
                setInquiryForm({ firstName: '', lastName: '', email: '', inquiry: '', location: '' }); // Clear form
                setShowInquireForm(false); // Close form
            } else {
                setSnackbar({ open: true, message: data.error || 'Failed to submit inquiry.', severity: 'error' });
            }
        } catch (error) {
            setSnackbar({ open: true, message: 'An error occurred while submitting the inquiry.', severity: 'error' });
        }
    };

    // State for waitlist form
    const [waitlistForm, setWaitlistForm] = useState({
        firstName: 'John', // Pre-filled for testing
        lastName: 'Doe', // Pre-filled for testing
        relationship: 'Father', // Pre-filled for testing
        email: '', // Leave empty for unique input
        phone: '1234567890', // Pre-filled for testing
        comment: 'This is a test comment.', // Pre-filled for testing
        location: 'Mill Street', // Pre-filled for testing
        hearAboutUs: 'Internet Search', // Pre-filled for testing,
    });

    const [children, setChildren] = useState([
        {
            id: 1,
            firstName: 'ChildFirstName', // Pre-filled for testing
            lastName: 'ChildLastName', // Pre-filled for testing
            dob: '2020-01-01', // Pre-filled for testing
            gender: 'Male', // Pre-filled for testing
            startDate: '2023-12-01', // Pre-filled for testing
        },
    ]);

    const [additionalInfo, setAdditionalInfo] = useState(''); // State for additional input field

    const addChild = () => {
        setChildren([...children, { id: Date.now(), firstName: '', lastName: '', dob: '', gender: '', startDate: '' }]);
    };

    const removeChild = (id: number) => {
        setChildren(children.filter((child) => child.id !== id));
    };

    const handleSubmitWaitlist = async (e: React.FormEvent) => {
        e.preventDefault(); // Prevent default form submission behavior

        try {
            // Determine the group ID based on the selected location
            let groupId = null;
            if (waitlistForm.location === 'Mill Street') {
                groupId = 6;
            } else if (waitlistForm.location === 'Third Street') {
                groupId = 5;
            }

            // Prepare the payload
            const payload = {
                ...waitlistForm,
                children,
                groupId, // Include the group ID in the payload
                additionalInfo, // Include additional info in the payload
            };

            const response = await fetch('/process-waitlist', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json();
            if (response.ok) {
                setSnackbar({ open: true, message: 'Waitlist submitted successfully!', severity: 'success' });
                setWaitlistForm({
                    firstName: '',
                    lastName: '',
                    relationship: '',
                    email: '',
                    phone: '',
                    comment: '',
                    location: '',
                    hearAboutUs: '',
                }); // Clear form
                setChildren([{ id: 1, firstName: '', lastName: '', dob: '', gender: '', startDate: '' }]); // Reset children
                setAdditionalInfo(''); // Clear additional info
                setShowWaitlistForm(false); // Close form
            } else {
                setSnackbar({ open: true, message: data.error || 'Failed to submit waitlist.', severity: 'error' });
            }
        } catch (error) {
            setSnackbar({ open: true, message: 'An error occurred while submitting the waitlist.', severity: 'error' });
        }
    };

    const inquiryButtonRef = useRef<HTMLButtonElement | null>(null); // Reference for inquiry button
    const waitlistButtonRef = useRef<HTMLButtonElement | null>(null); // Reference for waitlist button

    const handleShowInquireForm = () => {
        setShowWaitlistForm(false);
        setShowInquireForm(true);
        setTimeout(() => inquiryButtonRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' }), 0); // Scroll to button
    };

    const handleShowWaitlistForm = () => {
        setShowInquireForm(false);
        setShowWaitlistForm(true);
        setTimeout(() => waitlistButtonRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' }), 0); // Scroll to button
    };

    useEffect(() => {
        const favicon = document.querySelector("link[rel='icon']");
        if (favicon) {
            favicon.setAttribute('href', '/public/images/favicon.png');
        }
    }, []);

    useEffect(() => {
        document.title = "Inquiry & Waitlist Portal"; // Ensure the title is set correctly
    }, []);

    return (
        <Box
            sx={{
                backgroundColor: '#DCF3FB',
                minHeight: '100vh',
                display: 'flex',
                flexDirection: 'column',
                alignItems: 'center',
                justifyContent: 'flex-start',
                padding: 4,
                color: '#000',
            }}
        >
            <Box
                sx={{
                    display: 'flex',
                    justifyContent: 'center',
                    marginBottom: 4,
                }}
            >
                <img src={SaplingsLogo} alt="Saplings Logo" style={{ height: '150px' }} />
            </Box>
            <Box
                sx={{
                    backgroundColor: '#fff',
                    padding: 3,
                    borderRadius: 2,
                    boxShadow: '0 4px 6px rgba(0, 0, 0, 0.1)',
                    textAlign: 'center',
                    maxWidth: 600,
                }}
            >
                <Typography
                    variant="h5"
                    fontWeight="bold"
                    sx={{ color: '#333', marginBottom: 2 }}
                >
                    Inquiry and Waitlist Portal
                </Typography>
                <Typography
                    variant="body1"
                    sx={{ color: '#555', lineHeight: 1.6, marginBottom: 4 }}
                >
                    Our programs are currently operating with extended waitlists.
                </Typography>
                <Box
                    sx={{
                        display: 'flex',
                        flexDirection: 'column',
                        gap: 2,
                    }}
                >
                    <Box
                        sx={{
                            backgroundColor: '#FFEBEE',
                            padding: 2,
                            borderRadius: 2,
                            boxShadow: '0 2px 4px rgba(0, 0, 0, 0.1)',
                            textAlign: 'center',
                        }}
                    >
                        <Typography variant="h6" fontWeight="bold" sx={{ color: '#D32F2F' }}>
                            Infant
                        </Typography>
                        <Typography variant="body1" sx={{ color: '#555' }}>
                            Wait Time: 1+ years
                        </Typography>
                    </Box>
                    <Box
                        sx={{
                            backgroundColor: '#E3F2FD',
                            padding: 2,
                            borderRadius: 2,
                            boxShadow: '0 2px 4px rgba(0, 0, 0, 0.1)',
                            textAlign: 'center',
                        }}
                    >
                        <Typography variant="h6" fontWeight="bold" sx={{ color: '#1976D2' }}>
                            Toddler
                        </Typography>
                        <Typography variant="body1" sx={{ color: '#555' }}>
                            Wait Time: 1+ years
                        </Typography>
                    </Box>
                    <Box
                        sx={{
                            backgroundColor: '#E8F5E9',
                            padding: 2,
                            borderRadius: 2,
                            boxShadow: '0 2px 4px rgba(0, 0, 0, 0.1)',
                            textAlign: 'center',
                        }}
                    >
                        <Typography variant="h6" fontWeight="bold" sx={{ color: '#388E3C' }}>
                            Preschool
                        </Typography>
                        <Typography variant="body1" sx={{ color: '#555' }}>
                            Wait Time: 1+ years
                        </Typography>
                    </Box>
                </Box>
            </Box>
            <Box sx={{ display: 'flex', gap: 2, marginTop: 4 }}>
                <Button
                    ref={inquiryButtonRef} // Attach reference to inquiry button
                    variant="contained"
                    onClick={handleShowInquireForm}
                    sx={{ scrollMarginTop: '80px' }} // Add padding above when scrolled into view
                >
                    Make An Inquiry
                </Button>
                <Button
                    ref={waitlistButtonRef} // Attach reference to waitlist button
                    variant="contained"
                    onClick={handleShowWaitlistForm}
                    sx={{ scrollMarginTop: '80px' }} // Add padding above when scrolled into view
                >
                    Join Our Waitlist
                </Button>
            </Box>

            {showInquireForm && (
                <Box
                    component="form"
                    onSubmit={handleSubmitInquiry}
                    sx={{
                        mt: 4,
                        width: '100%',
                        maxWidth: 500,
                        backgroundColor: '#fff',
                        padding: 3,
                        borderRadius: 2,
                        boxShadow: '0 4px 6px rgba(0, 0, 0, 0.1)',
                    }}
                >
                    <Typography variant="h6">Inquiry Form</Typography>
                    <TextField
                        fullWidth
                        label="First Name"
                        margin="normal"
                        value={inquiryForm.firstName}
                        onChange={(e) => setInquiryForm({ ...inquiryForm, firstName: e.target.value })}
                    />
                    <TextField
                        fullWidth
                        label="Last Name"
                        margin="normal"
                        value={inquiryForm.lastName}
                        onChange={(e) => setInquiryForm({ ...inquiryForm, lastName: e.target.value })}
                    />
                    <TextField
                        fullWidth
                        label="Email"
                        margin="normal"
                        value={inquiryForm.email}
                        onChange={(e) => setInquiryForm({ ...inquiryForm, email: e.target.value })}
                    />
                    <TextField
                        select
                        fullWidth
                        label="Choose Location"
                        margin="normal"
                        value={inquiryForm.location || ''}
                        onChange={(e) => setInquiryForm({ ...inquiryForm, location: e.target.value })}
                    >
                        <MenuItem value="Mill Street">Mill Street</MenuItem>
                        <MenuItem value="Third Street">Third Street</MenuItem>
                    </TextField>
                    <TextField
                        fullWidth
                        label="Inquiry"
                        margin="normal"
                        multiline
                        rows={4}
                        inputProps={{ maxLength: 1500 }}
                        value={inquiryForm.inquiry}
                        onChange={(e) => setInquiryForm({ ...inquiryForm, inquiry: e.target.value })}
                        helperText={`${inquiryForm.inquiry.length}/1500 characters`}
                    />
                    <Box sx={{ display: 'flex', gap: 2, mt: 2 }}>
                        <Button variant="contained" type="submit">
                            Submit
                        </Button>
                        <Button
                            variant="outlined"
                            onClick={() => {
                                setInquiryForm({ firstName: '', lastName: '', email: '', inquiry: '', location: '' }); // Clear form
                                setShowInquireForm(false); // Close form
                            }}
                        >
                            Reset
                        </Button>
                    </Box>
                </Box>
            )}

            {showWaitlistForm && (
                <Box
                    component="form"
                    onSubmit={handleSubmitWaitlist}
                    sx={{
                        mt: 4,
                        width: '100%',
                        maxWidth: 500,
                        backgroundColor: '#fff',
                        padding: 3,
                        borderRadius: 2,
                        boxShadow: '0 4px 6px rgba(0, 0, 0, 0.1)',
                    }}
                >
                    <Typography variant="h6">Waitlist Form</Typography>
                    <TextField
                        fullWidth
                        label="First Name"
                        margin="normal"
                        value={waitlistForm.firstName}
                        onChange={(e) => setWaitlistForm({ ...waitlistForm, firstName: e.target.value })}
                    />
                    <TextField
                        fullWidth
                        label="Last Name"
                        margin="normal"
                        value={waitlistForm.lastName}
                        onChange={(e) => setWaitlistForm({ ...waitlistForm, lastName: e.target.value })}
                    />
                    <TextField
                        select
                        fullWidth
                        label="Relationship"
                        margin="normal"
                        value={waitlistForm.relationship}
                        onChange={(e) => setWaitlistForm({ ...waitlistForm, relationship: e.target.value })}
                    >
                        {['Mother', 'Father', 'Grandmother', 'Grandfather', 'Guardian', 'Joint Custody', 'Other'].map((option) => (
                            <MenuItem key={option} value={option}>
                                {option}
                            </MenuItem>
                        ))}
                    </TextField>
                    <TextField
                        fullWidth
                        label="Email"
                        margin="normal"
                        value={waitlistForm.email}
                        onChange={(e) => setWaitlistForm({ ...waitlistForm, email: e.target.value })}
                    />
                    <TextField
                        fullWidth
                        label="Phone"
                        margin="normal"
                        value={waitlistForm.phone}
                        onChange={(e) => setWaitlistForm({ ...waitlistForm, phone: e.target.value })}
                    />
                    <Typography variant="subtitle1" sx={{ mt: 2 }}>
                        Child Information
                    </Typography>
                    {children.map((child, index) => (
                        <Box key={child.id} sx={{ mt: 2, border: '1px solid #ccc', p: 2, borderRadius: 2 }}>
                            <TextField
                                fullWidth
                                label="Child First Name"
                                margin="normal"
                                value={child.firstName}
                                onChange={(e) =>
                                    setChildren(
                                        children.map((c) =>
                                            c.id === child.id ? { ...c, firstName: e.target.value } : c
                                        )
                                    )
                                }
                            />
                            <TextField
                                fullWidth
                                label="Child Last Name"
                                margin="normal"
                                value={child.lastName}
                                onChange={(e) =>
                                    setChildren(
                                        children.map((c) =>
                                            c.id === child.id ? { ...c, lastName: e.target.value } : c
                                        )
                                    )
                                }
                            />
                            <TextField
                                fullWidth
                                label="DOB"
                                margin="normal"
                                type="date"
                                InputLabelProps={{ shrink: true }}
                                value={child.dob}
                                onChange={(e) =>
                                    setChildren(
                                        children.map((c) =>
                                            c.id === child.id ? { ...c, dob: e.target.value } : c
                                        )
                                    )
                                }
                            />
                            <TextField
                                select
                                fullWidth
                                label="Gender"
                                margin="normal"
                                value={child.gender}
                                onChange={(e) =>
                                    setChildren(
                                        children.map((c) =>
                                            c.id === child.id ? { ...c, gender: e.target.value } : c
                                        )
                                    )
                                }
                            >
                                {['Male', 'Female', 'Other', 'Unknown'].map((option) => (
                                    <MenuItem key={option} value={option}>
                                        {option}
                                    </MenuItem>
                                ))}
                            </TextField>
                            <TextField
                                fullWidth
                                label="Requested Start Date"
                                margin="normal"
                                type="date"
                                InputLabelProps={{ shrink: true }}
                                value={child.startDate}
                                onChange={(e) =>
                                    setChildren(
                                        children.map((c) =>
                                            c.id === child.id ? { ...c, startDate: e.target.value } : c
                                        )
                                    )
                                }
                            />
                            {index > 0 && (
                                <IconButton
                                    onClick={() => removeChild(child.id)}
                                    sx={{ mt: 1, color: 'red' }}
                                >
                                    <DeleteIcon />
                                </IconButton>
                            )}
                        </Box>
                    ))}
                    <Button variant="outlined" onClick={addChild} sx={{ mt: 2 }}>
                        Add Child
                    </Button>
                    <TextField
                        select
                        fullWidth
                        label="Choose Location"
                        margin="normal"
                        sx={{ mt: 2 }}
                        value={waitlistForm.location}
                        onChange={(e) => setWaitlistForm({ ...waitlistForm, location: e.target.value })}
                    >
                        {['Mill Street', 'Third Street'].map((option) => (
                            <MenuItem key={option} value={option}>
                                {option}
                            </MenuItem>
                        ))}
                    </TextField>
                    <TextField
                        select
                        fullWidth
                        label="How did you hear about us?"
                        margin="normal"
                        value={waitlistForm.hearAboutUs}
                        onChange={(e) => {
                            setWaitlistForm({ ...waitlistForm, hearAboutUs: e.target.value });
                            if (e.target.value !== 'Referral from Community Partner' && e.target.value !== 'Other') {
                                setAdditionalInfo(''); // Clear additional info if not needed
                            }
                        }}
                    >
                        <MenuItem value="Referral from Another Parent">Referral from Another Parent</MenuItem>
                        <MenuItem value="Referral from a Staff Member">Referral from a Staff Member</MenuItem>
                        <MenuItem value="Referral from Community Partner">Referral from Community Partner</MenuItem>
                        <MenuItem value="Internet Search">Internet Search</MenuItem>
                        <MenuItem value="Road Sign">Road Sign</MenuItem>
                        <MenuItem value="Other">Other</MenuItem>
                    </TextField>
                    {(waitlistForm.hearAboutUs === 'Referral from Community Partner' || waitlistForm.hearAboutUs === 'Other') && (
                        <TextField
                            fullWidth
                            label={
                                waitlistForm.hearAboutUs === 'Referral from Community Partner'
                                    ? 'Please Specify Community Partner'
                                    : 'Please Specify Other'
                            }
                            margin="normal"
                            value={additionalInfo}
                            onChange={(e) => setAdditionalInfo(e.target.value)}
                        />
                    )}
                    <TextField
                        fullWidth
                        label="Comment"
                        margin="normal"
                        multiline
                        rows={4}
                        inputProps={{ maxLength: 500 }}
                        value={waitlistForm.comment}
                        onChange={(e) => setWaitlistForm({ ...waitlistForm, comment: e.target.value })}
                        helperText={`${waitlistForm.comment.length}/500 characters`}
                    />
                    <Box sx={{ display: 'flex', gap: 2, mt: 2 }}>
                        <Button variant="contained" type="submit">
                            Submit
                        </Button>
                        <Button
                            variant="outlined"
                            onClick={() => {
                                setWaitlistForm({
                                    firstName: '',
                                    lastName: '',
                                    relationship: '',
                                    email: '',
                                    phone: '',
                                    comment: '',
                                    location: '',
                                    hearAboutUs: '',
                                }); // Clear form
                                setChildren([{ id: 1, firstName: '', lastName: '', dob: '', gender: '', startDate: '' }]); // Reset children
                                setAdditionalInfo(''); // Clear additional info
                                setShowWaitlistForm(false); // Close form
                            }}
                        >
                            Reset
                        </Button>
                    </Box>
                </Box>
            )}

            <Snackbar
                open={snackbar.open}
                autoHideDuration={6000}
                onClose={handleSnackbarClose}
                anchorOrigin={{ vertical: 'bottom', horizontal: 'center' }}
            >
                <Alert onClose={handleSnackbarClose} severity={snackbar.severity} sx={{ width: '100%' }}>
                    {snackbar.message}
                </Alert>
            </Snackbar>
        </Box>
    );
}
