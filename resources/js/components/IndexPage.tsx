<select id="location" name="location" value={location} onChange={(e) => setLocation(e.target.value)}>
    <option value="Mill Street">Mill Street, Acton</option>
    <option value="Third Street">Third Street, Orangeville</option>
</select>

const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    // Ensure phone number has the US country code
    const formattedPhone = phone.startsWith('+') ? phone : `+1${phone}`;

    const formData = {
        first_name,
        last_name,
        relationship,
        email,
        phone: formattedPhone, // Use the formatted phone number
        comment,
        location,
        hear_about_us,
    };

    console.log('Submitting form data:', formData);

    // ...existing code for form submission...
};