<!DOCTYPE html>
<html>
<head>
    <title>Update Waitlist Information</title>
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <style>
        body {
            font-family: 'Instrument Sans', sans-serif;
            background-color: #DCF3FB;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
            margin: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
        }
        input, select, textarea {
            width: calc(100% - 20px);
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }
        textarea {
            resize: none;
        }
        button {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
        }
        .btn-primary {
            background-color: #1976D2;
            color: #fff;
        }
        .btn-primary:hover {
            background-color: #1565C0;
        }
        .btn-danger {
            background-color: #D32F2F;
            color: #fff;
        }
        .btn-danger:hover {
            background-color: #C62828;
        }
        .child-section {
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .child-section h3 {
            margin-top: 0;
        }
        .logo {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        .logo img {
            height: 100px;
        }
        .action-buttons {
            text-align: center;
            margin: 20px 0;
        }
        .action-buttons p {
            margin: 10px 0;
            font-size: 16px;
            font-weight: 600;
            color: #555;
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
                <input type="text" id="first_name" name="first_name" value="{{ $customFields['First Name'] ?? '' }}" required>
            </div>

            <div>
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" value="{{ $customFields['Last Name'] ?? '' }}" required>
            </div>

            <div>
                <label for="relationship">Relationship</label>
                <input type="text" id="relationship" name="relationship" value="{{ $customFields['Relationship'] ?? '' }}" required>
            </div>

            <div>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ $customFields['Email'] ?? '' }}" required>
            </div>

            <div>
                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone" value="{{ $customFields['Phone'] ?? '' }}" required>
            </div>

            <div>
                <label for="comment">Comment</label>
                <textarea id="comment" name="comment">{{ $customFields['Comment'] ?? '' }}</textarea>
            </div>

            <div>
                <label for="location">Location</label>
                <input type="text" id="location" name="location" value="{{ $customFields['Location'] ?? '' }}" required>
            </div>

            <div>
                <label for="hear_about_us">How did you hear about us?</label>
                <input type="text" id="hear_about_us" name="hear_about_us" value="{{ $customFields['How did you hear about us?'] ?? '' }}">
            </div>

            <div class="child-section">
                <h3>Child Information</h3>
                <div>
                    <label for="child_first_name">Child First Name</label>
                    <input type="text" id="child_first_name" name="children[0][first_name]" value="{{ $customFields['Child First Name'] ?? '' }}" required>
                </div>

                <div>
                    <label for="child_last_name">Child Last Name</label>
                    <input type="text" id="child_last_name" name="children[0][last_name]" value="{{ $customFields['Child Last Name'] ?? '' }}" required>
                </div>

                <div>
                    <label for="child_dob">Child DOB</label>
                    <input type="date" id="child_dob" name="children[0][dob]" value="{{ $customFields['Child DOB'] ?? '' }}" required>
                </div>

                <div>
                    <label for="child_gender">Child Gender</label>
                    <input type="text" id="child_gender" name="children[0][gender]" value="{{ $customFields['Child Gender'] ?? '' }}" required>
                </div>

                <div>
                    <label for="child_start_date">Requested Start Date</label>
                    <input type="date" id="child_start_date" name="children[0][start_date]" value="{{ $customFields['Requested Start Date'] ?? '' }}" required>
                </div>
            </div>

            <button type="submit">Update Waitlist</button>
        </form>
    </div>
</body>
</html>
