namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WaitlistUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    public $opportunityId;

    public function __construct($opportunityId)
    {
        $this->opportunityId = $opportunityId;
    }

    public function build()
    {
        $url = url('/update-waitlist?opportunity_id=' . $this->opportunityId);

        return $this->view('emails.waitlist_update')
            ->with([
                'updateLink' => $url,
            ]);
    }
}
