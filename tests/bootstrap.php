<?php
require_once 'vendor/autoload.php';

VCR\VCR::turnOn();
VCR\VCR::configure()->enableRequestMatchers(['method', 'url']);
VCR\VCR::configure()->setMode('none');
