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
            <form method="POST" action="/waitlist/opt-out/{{ $contactId }}" style="display: inline;">
                @csrf
                <button type="submit" class="btn-danger">Remove Me From The List</button>
            </form>
        </div>
        <form method="POST" action="/waitlist/update/{{ $contactId }}">
            @csrf
            <div class="form-group">
                <label for="firstName">First Name:</label>
                <input type="text" id="firstName" name="firstName" value="{{ $data['firstName'] }}" required>
            </div>
            <div class="form-group">
                <label for="lastName">Last Name:</label>
                <input type="text" id="lastName" name="lastName" value="{{ $data['lastName'] }}" required>
            </div>
            <div class="form-group">
                <label for="relationship">Relationship:</label>
                <select id="relationship" name="relationship" required>
                    @foreach (['Mother', 'Father', 'Grandmother', 'Grandfather', 'Guardian', 'Joint Custody', 'Other'] as $option)
                        <option value="{{ $option }}" {{ $option === ($data['relationship'] ?? '') ? 'selected' : '' }}>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="{{ $data['email'] }}" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone:</label>
                <input type="text" id="phone" name="phone" value="{{ $data['phone'] }}" required>
            </div>
            <div class="form-group">
                <label for="location">Location:</label>
                <select id="location" name="location" required>
                    <option value="Mill Street" {{ $data['location'] === 'Mill Street' ? 'selected' : '' }}>Mill Street</option>
                    <option value="Third Street" {{ $data['location'] === 'Third Street' ? 'selected' : '' }}>Third Street</option>
                </select>
            </div>
            <div class="form-group">
                <label for="hearAboutUs">How did you hear about us?</label>
                <select id="hearAboutUs" name="hearAboutUs" required>
                    @foreach (['Referral from Another Parent', 'Referral from a Staff Member', 'Referral from Community Partner', 'Internet Search', 'Road Sign', 'Other'] as $option)
                        <option value="{{ $option }}" {{ $option === ($data['hearAboutUs'] ?? '') ? 'selected' : '' }}>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
            @if (isset($data['hearAboutUs']) && ($data['hearAboutUs'] === 'Referral from Community Partner' || $data['hearAboutUs'] === 'Other'))
                <div class="form-group">
                    <label for="additionalInfo">Please Specify:</label>
                    <input type="text" id="additionalInfo" name="additionalInfo" value="{{ $data['additionalInfo'] ?? '' }}">
                </div>
            @endif
            <h2>Children</h2>
            @foreach ($data['children'] as $index => $child)
                <div class="child-section">
                    <h3>Child {{ $index + 1 }}</h3>
                    <div class="form-group">
                        <label for="children[{{ $index }}][firstName]">First Name:</label>
                        <input type="text" id="children[{{ $index }}][firstName]" name="children[{{ $index }}][firstName]" value="{{ $child['firstName'] }}" required>
                    </div>
                    <div class="form-group">
                        <label for="children[{{ $index }}][lastName]">Last Name:</label>
                        <input type="text" id="children[{{ $index }}][lastName]" name="children[{{ $index }}][lastName]" value="{{ $child['lastName'] }}" required>
                    </div>
                    <div class="form-group">
                        <label for="children[{{ $index }}][dob]">Date of Birth:</label>
                        <input type="date" id="children[{{ $index }}][dob]" name="children[{{ $index }}][dob]" value="{{ $child['dob'] }}" required>
                    </div>
                    <div class="form-group">
                        <label for="children[{{ $index }}][gender]">Gender:</label>
                        <select id="children[{{ $index }}][gender]" name="children[{{ $index }}][gender]" required>
                            <option value="Male" {{ $child['gender'] === 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ $child['gender'] === 'Female' ? 'selected' : '' }}>Female</option>
                            <option value="Other" {{ $child['gender'] === 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="children[{{ $index }}][startDate]">Requested Start Date:</label>
                        <input type="date" id="children[{{ $index }}][startDate]" name="children[{{ $index }}][startDate]" value="{{ $child['startDate'] }}" required>
                    </div>
                </div>
            @endforeach
            <div class="form-group">
                <label for="comment">Comment:</label>
                <textarea id="comment" name="comment" rows="4" maxlength="500">{{ $data['comment'] ?? '' }}</textarea>
            </div>
            <button type="submit" class="btn-primary">Save Changes</button>
        </form>
    </div>
</body>
</html>
