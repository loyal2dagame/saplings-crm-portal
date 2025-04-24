document.querySelector("#confirm-removal-final").addEventListener("click", async () => {
    const selectedOpportunities = getSelectedOpportunityIds(); // Assume this function retrieves selected IDs
    if (selectedOpportunities.length === 0) {
        alert("No opportunities selected.");
        return;
    }

    try {
        const response = await fetch("/waitlist/opt-out", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({ opportunityIds: selectedOpportunities }),
        });

        const result = await response.json();
        if (response.ok) {
            alert(result.message);
        } else {
            alert(`Error: ${result.message}`);
        }
    } catch (error) {
        console.error("Error during opt-out request:", error);
        alert("An error occurred while processing your request.");
    }
});

// Helper function to retrieve selected opportunity IDs
function getSelectedOpportunityIds() {
    // Replace this with the actual logic to get selected IDs from the UI
    const checkboxes = document.querySelectorAll(".opportunity-checkbox:checked");
    return Array.from(checkboxes).map((checkbox) => checkbox.value);
}
