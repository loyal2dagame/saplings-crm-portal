<!DOCTYPE html>
<html>
<head>
    <title>Update Waitlist Information</title>
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <style>
        body {
            font-family: 'Roboto', sans-serif; /* Match font to index page */
            background-color: #F9FAFB;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background-color: #FFFFFF;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 600px; /* Adjust width to match index page */
            width: 100%;
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
            width: 100%;
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
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.2s;
            font-family: 'Roboto', sans-serif; /* Ensure consistent font across all elements */
        }
        .btn-primary {
            background-color: #2563EB;
            color: #FFFFFF;
        }
        .btn-primary:hover {
            background-color: #1D4ED8;
        }
        .btn-danger {
            background-color: #DC2626;
            color: #FFFFFF;
        }
        .btn-danger:hover {
            background-color: #B91C1C;
        }
        .child-section {
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
        .logo {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        .logo img {
            height: 80px;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="{{ asset('images/Saplings_Logo_Linear_For_White.svg') }}" alt="Saplings Logo">
        </div>
        <h1 style="text-align: center; margin-bottom: 20px;">Update Your Waitlist Information</h1>
        <div class="action-buttons">
            <p>Or</p>
            <form method="POST" action="/waitlist/opt-out/{{ $opportunityId }}" style="display: inline;">
                @csrf
                <button type="submit" class="btn-danger">Remove Me From The List</button>
            </form>
        </div>
        <form method="POST" action="{{ route('waitlist.update', ['opportunityId' => $opportunityId]) }}">
            @csrf
            <input type="hidden" name="opportunity_id" value="{{ $opportunityId }}">

            <div>
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name" value="{{ $formData['first_name'] ?? '' }}" required>
            </div>

            <div>
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" value="{{ $formData['last_name'] ?? '' }}" required>
            </div>

            <div>
                <label for="relationship">Relationship</label>
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
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ $formData['email'] ?? '' }}" required>
            </div>

            <div>
                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone" value="{{ $formData['phone'] ?? '' }}" required>
            </div>

            <div>
                <label for="comment">Comment</label>
                <textarea id="comment" name="comment">{{ $formData['comment'] ?? '' }}</textarea>
            </div>

            <div>
                <label for="location">Location</label>
                <select id="location" name="location[]" multiple>
                    @foreach ($formData['location'] as $location)
                        <option value="{{ $location }}" selected>{{ $location }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="hear_about_us">How did you hear about us?</label>
                <input type="text" id="hear_about_us" name="hear_about_us" value="{{ $formData['hear_about_us'] ?? '' }}">
            </div>

            @foreach ($children as $index => $child)
                <div class="child-section">
                    <h3>Child Information ({{ $index + 1 }})</h3>
                    <div>
                        <label for="children_{{ $index }}_first_name">Child First Name</label>
                        <input type="text" id="children_{{ $index }}_first_name" name="children[{{ $index }}][first_name]" value="{{ $child['first_name'] }}" required>
                    </div>

                    <div>
                        <label for="children_{{ $index }}_last_name">Child Last Name</label>
                        <input type="text" id="children_{{ $index }}_last_name" name="children[{{ $index }}][last_name]" value="{{ $child['last_name'] }}" required>
                    </div>

                    <div>
                        <label for="children_{{ $index }}_dob">Child DOB</label>
                        <input type="date" id="children_{{ $index }}_dob" name="children[{{ $index }}][dob]" value="{{ $child['dob'] }}" required>
                    </div>

                    <div>
                        <label for="children_{{ $index }}_start_date">Requested Start Date</label>
                        <input type="date" id="children_{{ $index }}_start_date" name="children[{{ $index }}][start_date]" value="{{ $child['start_date'] }}" required>
                    </div>
                </div>
            @endforeach

            <button type="submit">Update Waitlist</button>
        </form>
    </div>
</body>
</html>
