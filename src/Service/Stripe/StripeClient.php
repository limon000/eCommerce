<?php

namespace App\Service\Stripe;


use App\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;

class StripeClient
{
    protected $manager;
    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    public function createCustomer(User $user , $paymentToken)
    {
        $customer = \Stripe\Customer::create([
            'email' => $user->getEmail(),
            'source' => $paymentToken,
        ]);
        $user->setStripeCustomerId($customer->id);
        $this->manager->persist($user);
        $this->manager->flush();

        return $customer;
    }
    public function updateCustomerCard(User $user, $paymentToken)
    {
        $customer = \Stripe\Customer::retrieve($user->getStripeCustomerId());
        $customer->source = $paymentToken;
        $customer->save();
    }
    public function createInvoiceItem($amount, User $user , $description)
    {
        return \Stripe\InvoiceItem::create([
            'amount' => $amount,
            'currency' => 'usd',
            'customer' => $user->getStripeCustomerId(),
            'description' => $description,
        ]);
    }
    public function createInvoice(User $user, $payImmediately =true)
    {
        $invoice = \Stripe\Invoice::create(array(
            'customer' => $user->getStripeCustomerId(),
        ));
        if($payImmediately)
        {
            $invoice->pay();
        }
        return $invoice;

    }

}