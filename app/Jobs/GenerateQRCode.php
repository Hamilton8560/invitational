<?php

namespace App\Jobs;

use App\Models\Sale;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class GenerateQRCode implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Sale $sale
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Create encrypted payload
        $payload = encrypt([
            'sale_id' => $this->sale->id,
            'user_id' => $this->sale->user_id,
            'event_id' => $this->sale->event_id,
            'type' => $this->determineType(),
            'team_id' => $this->sale->team_id,
            'individual_player_id' => $this->sale->individual_player_id,
            'booth_id' => $this->sale->booth_id,
            'banner_id' => $this->sale->banner_id,
        ]);

        // Generate check-in URL
        $url = route('checkin.scan', ['token' => $payload]);

        // Create QR code renderer
        $renderer = new ImageRenderer(
            new RendererStyle(300),
            new SvgImageBackEnd
        );

        $writer = new Writer($renderer);
        $qrCode = $writer->writeString($url);

        // Store QR code
        $directory = "qrcodes/event_{$this->sale->event_id}";
        $filename = "sale_{$this->sale->id}.svg";
        $path = "{$directory}/{$filename}";

        Storage::put($path, $qrCode);

        // Update sale with QR code path
        $this->sale->update([
            'qr_code_path' => $path,
        ]);
    }

    /**
     * Determine the check-in type based on sale relationships
     */
    protected function determineType(): string
    {
        if ($this->sale->team_id) {
            return 'team';
        }

        if ($this->sale->individual_player_id) {
            return 'individual';
        }

        if ($this->sale->booth_id) {
            return 'vendor';
        }

        if ($this->sale->banner_id) {
            return 'vendor';
        }

        return 'spectator';
    }
}
