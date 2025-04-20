namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Mail\WaitlistUpdateMail;
use Illuminate\Support\Facades\Mail;

class SendWaitlistReminders extends Command
{
    protected $signature = 'waitlist:send_reminders';
    protected $description = 'Send waitlist reminder emails';

    public function handle()
    {
        // Fetch waitlist opportunities that need reminders
        $opportunities = $this->getWaitlistOpportunities();

        foreach ($opportunities as $opportunity) {
            // Send the email with the opportunity ID
            Mail::to($opportunity['email'])->send(new WaitlistUpdateMail($opportunity['id']));
        }

        $this->info('Waitlist reminder emails sent successfully.');
    }

    private function getWaitlistOpportunities()
    {
        // Replace this with the actual logic to fetch waitlist opportunities
        return [
            ['id' => 123, 'email' => 'user1@example.com'],
            ['id' => 456, 'email' => 'user2@example.com'],
        ];
    }
}
