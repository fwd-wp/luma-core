<?php

namespace Luma\Core\Patterns;

class RegisterPatterns {

    public function __invoke(): void {
        add_action( 'init', [$this, 'register_patterns'] );
    }

    public function register_patterns(): void {
        ClassicFooter::register();
        ModernFooter::register();
    }
}