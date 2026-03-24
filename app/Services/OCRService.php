<?php

    namespace App\Services;

    
use thiagoalessio\TesseractOCR\TesseractOCR;

    class OCRService {

        public function extractPath(string $path){

            return (new TesseractOCR($path))
            ->lang('por') // português
            ->run(); 

        }

    }