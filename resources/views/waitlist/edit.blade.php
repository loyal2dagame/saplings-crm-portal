<!DOCTYPE html>
<html>
<head>
    <title>Update Waitlist Information</title>
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #DCF2FB; /* Blue background */
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            min-height: 100vh;
        }
        .logo-container {
            margin-top: 20px;
            text-align: center;
        }
        .logo-container img {
            max-width: 150px;
        }
        .container {
            width: 100%;
            max-width: 500px; /* Narrower form width */
            background-color: #FFFFFF; /* White form background */
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            box-sizing: border-box;
            margin-top: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            font-weight: 500;
            margin-bottom: 8px;
            color: #374151;
            font-family: 'Roboto', sans-serif; /* Ensure consistent font across all elements */
        }
        input, select, textarea {
            width: 95%; /* Narrower field width */
            padding: 12px;
            border: 1px solid #D1D5DB;
            border-radius: 6px;
            font-size: 16px;
            color: #374151;
            background-color: #F9FAFB;
            box-sizing: border-box;
            transition: border-color 0.2s;
            font-family: 'Roboto', sans-serif; /* Ensure consistent font across all elements */
        }
        input:focus, select:focus, textarea:focus {
            border-color: #2563EB;
            outline: none;
        }
        textarea {
            resize: vertical;
        }
        select[multiple] {
            height: auto;
            max-height: 200px;
            overflow-y: auto;
        }
        button {
            display: inline-flex; /* Ensure button content aligns properly */
            align-items: center;
            justify-content: center;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.2s, width 0.2s;
            font-family: 'Roboto', sans-serif;
            width: auto; /* Revert button width to auto */
        }
        .btn-primary {
            background-color: #007bff; /* Blue color */
            color: #FFFFFF;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-danger {
            background-color: #DC2626; /* Red color */
            color: #FFFFFF;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .btn-danger:hover {
            background-color: #B91C1C; /* Darker red on hover */
        }
        .spinner-border {
            margin-left: 10px; /* Add spacing between text and spinner */
            display: none; /* Hide spinner by default */
        }
        .loading .spinner-border {
            display: inline-block; /* Show spinner when loading */
        }
        .loading #button-text {
            visibility: hidden; /* Hide button text when loading */
        }
        .child-section {
            margin-top: 30px; /* Increase padding above the child information box */
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #D1D5DB;
            border-radius: 6px;
            background-color: #F3F4F6;
        }
        .child-section h3 {
            margin-top: 0;
            font-size: 18px;
            color: #374151;
            font-family: 'Roboto', sans-serif; /* Ensure consistent font across all elements */
        }
        .action-buttons {
            text-align: center;
            margin: 20px 0;
        }
        .action-buttons p {
            margin: 10px 0;
            font-size: 16px;
            font-weight: 500;
            color: #6B7280;
            font-family: 'Roboto', sans-serif; /* Ensure consistent font across all elements */
        }
        h1 {
            font-family: 'Roboto', sans-serif; /* Ensure consistent font across all elements */
        }
        .field-title {
            margin-top: 15px; /* Add padding above the title */
        }
        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
        .spinner-border {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10; /* Ensure spinner is above other elements */
        }

        /* Dialog Overlay */
        .dialog-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        /* Dialog Box */
        .dialog-box {
            background-color: #FFFFFF;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 90%;
            max-width: 400px;
            text-align: center;
        }

        .dialog-box h2 {
            margin-top: 0;
            font-size: 24px;
            color: #374151;
        }

        .dialog-box p {
            font-size: 16px;
            color: #6B7280;
            margin-bottom: 20px;
        }

        /* Child List */
        .child-list {
            list-style: none;
            padding: 0;
            margin: 0 0 20px 0;
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #D1D5DB;
            border-radius: 6px;
            background-color: #F9FAFB;
        }

        /* Child Row */
        .child-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #E5E7EB;
            cursor: pointer;
            transition: background-color 0.2s, color 0.2s;
        }

        .child-row:last-child {
            border-bottom: none;
        }

        .child-row:hover {
            background-color: #E5E7EB;
        }

        .child-row.selected {
            background-color: #2563EB; /* Blue background */
            color: #FFFFFF;
        }

        /* Child Name */
        .child-name {
            font-size: 16px;
            color: inherit;
            text-align: left;
            flex-grow: 1;
        }

        /* Selection Indicator */
        .selection-indicator {
            width: 20px;
            height: 20px;
            border: 2px solid #D1D5DB;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s, border-color 0.2s;
        }

        .child-row.selected .selection-indicator {
            background-color: #DC2626; /* Red background */
            border-color: #DC2626; /* Red border */
        }

        .child-row.selected .selection-indicator::before {
            content: '✖'; /* Red X */
            color: #FFFFFF;
            font-size: 14px;
        }

        /* Dialog Actions */
        .btn-secondary {
            background-color: #6B7280;
            color: #FFFFFF;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-secondary:hover {
            background-color: #4B5563;
        }

        .btn-danger {
            background-color: #DC2626; /* Red color */
            color: #FFFFFF;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-danger:hover {
            background-color: #B91C1C; /* Darker red on hover */
        }

        /* Snackbar Notification */
        #snackbar {
            visibility: hidden;
            min-width: 300px;
            background-color: #4CAF50;
            color: #fff;
            text-align: center;
            border-radius: 8px;
            padding: 16px;
            position: fixed;
            z-index: 1000;
            left: 50%;
            bottom: 20px;
            transform: translateX(-50%);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            font-size: 16px;
            font-weight: 500;
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
        }
    </style>
</head>
<body>
    <div class="logo-container">
        <img src="{{ asset('images/Saplings_Logo_Linear_For_White.svg') }}" alt="Saplings Logo" style="max-width: none; width: 357px; height: 150px;"> <!-- Ensure logo is on blue background -->
    </div>
    <div class="container">
        <h1 style="text-align: center; margin-bottom: 20px;">Update Your Waitlist Information</h1>
        <div class="action-buttons">
            <p>Or</p>
            <button type="button" class="btn-danger" id="open-removal-dialog">Remove Me From The List</button>
        </div>

        <!-- Waitlist Removal Dialog -->
        <div id="removal-dialog" class="dialog-overlay" style="display: none;">
            <div class="dialog-box">
                <h2>Waitlist Removal</h2>
                <p>Select the children that you would like to remove.</p>
                <ul class="child-list">
                    @foreach ($children as $index => $child)
                        <li data-id="{{ $child['opportunity_id'] }}" class="child-row">
                            <span class="child-name">{{ $child['first_name'] }} {{ $child['last_name'] }}</span>
                            <span class="selection-indicator"></span>
                        </li>
                    @endforeach
                </ul>
                <div class="dialog-actions">
                    <button type="button" class="btn-secondary" id="close-removal-dialog">Cancel</button>
                    <button type="button" class="btn-danger" id="confirm-removal">Remove</button>
                </div>
            </div>
        </div>

        <!-- Confirmation Dialog -->
        <div id="confirmation-dialog" class="dialog-overlay" style="display: none;">
            <div class="dialog-box">
                <h2>Confirm Removal</h2>
                <p>Are you sure you want to remove the selected children from the waitlist?</p>
                <div class="dialog-actions">
                    <button type="button" class="btn-secondary" id="cancel-confirmation">Cancel</button>
                    <button type="button" class="btn-danger" id="confirm-removal-final">Yes, Remove</button>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('waitlist.update', ['opportunityId' => $opportunityId]) }}" id="update-waitlist-form">
            @csrf
            <input type="hidden" name="opportunity_id" value="{{ $opportunityId }}">

            <div>
                <label class="field-title" for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name" value="{{ $formData['first_name'] ?? '' }}" required>
            </div>

            <div>
                <label class="field-title" for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" value="{{ $formData['last_name'] ?? '' }}" required>
            </div>

            <div>
                <label class="field-title" for="relationship">Relationship</label>
                <select id="relationship" name="relationship" required>
                    <option value="" disabled {{ empty($formData['relationship']) ? 'selected' : '' }}>Select One</option>
                    @foreach(['Mother', 'Father', 'Grandmother', 'Grandfather', 'Guardian', 'Joint Custody', 'Other'] as $option)
                        <option value="{{ $option }}" {{ (isset($formData['relationship']) && $formData['relationship'] === $option) ? 'selected' : '' }}>
                            {{ $option }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="field-title" for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ $formData['email'] ?? '' }}" readonly style="background-color: #e9ecef; cursor: not-allowed;">
            </div>

            <div>
                <label class="field-title" for="phone">Phone</label>
                <input 
                    type="tel" 
                    id="phone" 
                    name="phone" 
                    value="{{ $formData['phone'] ?? '' }}" 
                    required 
                    placeholder="(123) 456-7890"
                />
            </div>

            <div>
                <label class="field-title" for="comment">Comment</label>
                <textarea id="comment" name="comment">{{ $formData['comment'] ?? '' }}</textarea>
            </div>

            <div>
                <label class="field-title" for="location">Location</label>
                <select id="location" name="location">
                    <option value="Mill Street" {{ in_array('Mill Street', $formData['location']) ? 'selected' : '' }}>Mill Street, Acton</option>
                    <option value="Third Street" {{ in_array('Third Street', $formData['location']) ? 'selected' : '' }}>Third Street, Orangeville</option>
                </select>
            </div>

            @foreach ($children as $index => $child)
                <div class="child-section">
                    <h3>Child Information ({{ $index + 1 }})</h3>
                    <input type="hidden" name="children[{{ $index }}][opportunity_id]" value="{{ $child['opportunity_id'] }}">
                    <div>
                        <label class="field-title" for="children_{{ $index }}_first_name">Child First Name</label>
                        <input type="text" id="children_{{ $index }}_first_name" name="children[{{ $index }}][first_name]" value="{{ $child['first_name'] }}" required>
                    </div>
                    <div>
                        <label class="field-title" for="children_{{ $index }}_last_name">Child Last Name</label>
                        <input type="text" id="children_{{ $index }}_last_name" name="children[{{ $index }}][last_name]" value="{{ $child['last_name'] }}" required>
                    </div>
                    <div>
                        <label class="field-title" for="children_{{ $index }}_dob">Child DOB</label>
                        <input type="date" id="children_{{ $index }}_dob" name="children[{{ $index }}][dob]" value="{{ $child['dob'] }}" required>
                    </div>
                    <div>
                        <label class="field-title" for="children_{{ $index }}_start_date">Requested Start Date</label>
                        <input type="date" id="children_{{ $index }}_start_date" name="children[{{ $index }}][start_date]" value="{{ $child['start_date'] }}" required>
                    </div>
                </div>
            @endforeach

            <button type="submit" class="btn-primary" id="update-waitlist-button" style="min-width: 150px;">
                <span id="button-text">Update Waitlist</span>
            </button>
        </form>
    </div>
    <script>
        let isSubmitting = false;

        document.getElementById('update-waitlist-form').addEventListener('submit', function (e) {
            if (isSubmitting) {
                e.preventDefault(); // Prevent multiple submissions
                return;
            }

            const button = document.getElementById('update-waitlist-button');
            const buttonText = document.getElementById('button-text');

            isSubmitting = true; // Set submission state
            button.disabled = true; // Disable the button
            buttonText.textContent = 'Updating...'; // Change button text
        });

        document.getElementById('phone').addEventListener('input', function (e) {
            let input = e.target.value.replace(/\D/g, ''); // Remove all non-digit characters
            let formatted = '';

            if (input.length > 0) {
                formatted = '(' + input.substring(0, 3); // Add opening bracket for area code
            }
            if (input.length > 3) {
                formatted += ') ' + input.substring(3, 6); // Add closing bracket and space
            }
            if (input.length > 6) {
                formatted += '-' + input.substring(6, 10); // Add hyphen after the next three digits
            }

            e.target.value = formatted; // Update the input field with the formatted value
        });

        document.querySelector('form').addEventListener('submit', function (e) {
            const phoneInput = document.getElementById('phone');
            if (phoneInput && !phoneInput.value.startsWith('+')) {
                phoneInput.value = '+1' + phoneInput.value.trim(); // Prepend +1 if no country code exists
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            const removalDialog = document.getElementById('removal-dialog');
            const confirmationDialog = document.getElementById('confirmation-dialog');
            const openDialogButton = document.getElementById('open-removal-dialog');
            const closeDialogButton = document.getElementById('close-removal-dialog');
            const confirmRemovalButton = document.getElementById('confirm-removal');
            const cancelConfirmationButton = document.getElementById('cancel-confirmation');
            const confirmRemovalFinalButton = document.getElementById('confirm-removal-final');
            const childRows = document.querySelectorAll('.child-row');
            const snackbar = document.getElementById('snackbar'); // Ensure snackbar is initialized after DOM is loaded

            if (!snackbar) {
                console.error('Snackbar element not found in the DOM.');
                return;
            }

            let selectedChildren = [];

            openDialogButton.addEventListener('click', () => {
                removalDialog.style.display = 'flex';
            });

            closeDialogButton.addEventListener('click', () => {
                removalDialog.style.display = 'none';
            });

            childRows.forEach(row => {
                row.addEventListener('click', () => {
                    row.classList.toggle('selected'); // Toggle selection
                });
            });

            confirmRemovalButton.addEventListener('click', () => {
                selectedChildren = Array.from(document.querySelectorAll('.child-row.selected'))
                    .map(row => row.getAttribute('data-id'));

                if (selectedChildren.length === 0) {
                    alert('Please select at least one child to remove.');
                    return;
                }

                // Open the confirmation dialog
                removalDialog.style.display = 'none';
                confirmationDialog.style.display = 'flex';
            });

            cancelConfirmationButton.addEventListener('click', () => {
                confirmationDialog.style.display = 'none';
                removalDialog.style.display = 'flex'; // Reopen the removal dialog
            });

            confirmRemovalFinalButton.addEventListener('click', async () => {
                if (selectedChildren.length === 0) {
                    // Show snackbar for no selection
                    snackbar.textContent = 'No children selected for removal.';
                    snackbar.style.backgroundColor = '#DC2626'; // Red background
                    snackbar.style.visibility = 'visible'; // Ensure visibility
                    snackbar.style.opacity = '1'; // Ensure opacity
                    snackbar.classList.add('show');
                    setTimeout(() => {
                        snackbar.classList.remove('show');
                        snackbar.style.opacity = '0'; // Reset opacity
                        snackbar.style.visibility = 'hidden'; // Reset visibility
                    }, 5000); // Hide after 5 seconds
                    return;
                }

                try {
                    const response = await fetch('/waitlist/opt-out', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}' // Include CSRF token for Laravel
                        },
                        body: JSON.stringify({ opportunityIds: selectedChildren })
                    });

                    const result = await response.json();
                    if (response.ok) {
                        // Show success snackbar
                        snackbar.textContent = 'The selected children have been successfully removed from the waitlist.';
                        snackbar.style.backgroundColor = '#4CAF50'; // Green background
                        snackbar.style.visibility = 'visible'; // Ensure visibility
                        snackbar.style.opacity = '1'; // Ensure opacity
                        snackbar.classList.add('show');
                        setTimeout(() => {
                            snackbar.classList.remove('show');
                            snackbar.style.opacity = '0'; // Reset opacity
                            snackbar.style.visibility = 'hidden'; // Reset visibility
                        }, 5000); // Hide after 5 seconds

                        // Close the confirmation dialog
                        confirmationDialog.style.display = 'none';
                    } else {
                        // Show error snackbar
                        snackbar.textContent = `Error: ${result.message}`;
                        snackbar.style.backgroundColor = '#DC2626'; // Red background
                        snackbar.style.visibility = 'visible'; // Ensure visibility
                        snackbar.style.opacity = '1'; // Ensure opacity
                        snackbar.classList.add('show');
                        setTimeout(() => {
                            snackbar.classList.remove('show');
                            snackbar.style.opacity = '0'; // Reset opacity
                            snackbar.style.visibility = 'hidden'; // Reset visibility
                        }, 5000); // Hide after 5 seconds
                    }
                } catch (error) {
                    console.error('Error during opt-out request:', error);
                    // Show error snackbar
                    snackbar.textContent = 'An error occurred while processing your request.';
                    snackbar.style.backgroundColor = '#DC2626'; // Red background
                    snackbar.style.visibility = 'visible'; // Ensure visibility
                    snackbar.style.opacity = '1'; // Ensure opacity
                    snackbar.classList.add('show');
                    setTimeout(() => {
                        snackbar.classList.remove('show');
                        snackbar.style.opacity = '0'; // Reset opacity
                        snackbar.style.visibility = 'hidden'; // Reset visibility
                    }, 5000); // Hide after 5 seconds
                }
            });
        });

        // Ensure snackbar visibility resets properly
        document.addEventListener('DOMContentLoaded', () => {
            const snackbar = document.getElementById('snackbar');
            snackbar.addEventListener('transitionend', () => {
                if (!snackbar.classList.contains('show')) {
                    snackbar.style.visibility = 'hidden';
                }
            });
        });
    </script>

    <!-- Snackbar Notification -->
    <div id="snackbar"></div>
</body>
</html>
