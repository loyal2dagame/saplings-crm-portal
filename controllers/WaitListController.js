class WaitListController {
    async optOut(req, res) {
        try {
            const { opportunityIds } = req.body; // Capture selected opportunity IDs from the request body
            const phaseIdClosedLost = 6; // Phase ID for "Closed Lost"

            if (!Array.isArray(opportunityIds) || opportunityIds.length === 0) {
                return res.status(400).json({ message: "No opportunities selected for opt-out." });
            }

            const updatePromises = opportunityIds.map(async (id) => {
                try {
                    // Make an API call to update the opportunity phase
                    await someApiService.editOpportunity(id, { phase_id: phaseIdClosedLost });
                } catch (error) {
                    console.error(`Failed to update opportunity ID ${id}:`, error);
                }
            });

            await Promise.all(updatePromises); // Wait for all updates to complete

            return res.status(200).json({ message: "Selected opportunities have been opted out successfully." });
        } catch (error) {
            console.error("Error in optOut:", error);
            return res.status(500).json({ message: "An error occurred while processing the opt-out request." });
        }
    }
}

module.exports = WaitListController;