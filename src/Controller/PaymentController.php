<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends AbstractController
{
    public function payment()
    {
        \Stripe\Stripe::setApiKey('sk_test_slLLiq3g14CyTcksAsrHfJl300x94MIOOM');

        $payload = @file_get_contents('php://input');
        $event = null;

        try {
            $event = StripeEvent::constructFrom(
                json_decode($payload, true)
            );
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            exit();
        }

// Handle the event
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object; // contains a StripePaymentIntent
                handlePaymentIntentSucceeded($paymentIntent);
                break;
            case 'payment_method.attached':
                $paymentMethod = $event->data->object; // contains a StripePaymentMethod
                handlePaymentMethodAttached($paymentMethod);
                break;
            // ... handle other event types
            default:
                // Unexpected event type
                http_response_code(400);
                exit();
        }

        http_response_code(200);
    }
}
