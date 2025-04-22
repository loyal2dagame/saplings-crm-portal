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
            background-color: #DC2626;
            color: #FFFFFF;
        }
        .btn-danger:hover {
            background-color: #B91C1C;
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
            <form method="POST" action="/waitlist/opt-out/{{ $opportunityId }}" style="display: inline;">
                @csrf
                <button type="submit" class="btn-danger">Remove Me From The List</button>
            </form>
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
    </script>
</body>
</html>
