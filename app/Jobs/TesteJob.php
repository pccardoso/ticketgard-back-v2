<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use App\Services\OCRService;


class TesteJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $path
    ){}

    /**
     * Execute the job.
     */
    public function handle(OCRService $ocrService): void
    {

        $extract = $ocrService->extractPath($this->path);

        Log::info(">>>>> OCR: ".$extract);
    }
}
