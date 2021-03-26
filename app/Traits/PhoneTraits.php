<?php

namespace App\Traits;

trait PhoneTraits
{
    public function formatPhoneTenDigits($phone)
    {
        return $this->formatPhone($phone, true);
    }

    public function formatPhoneElevenDigits($phone)
    {
        return $this->formatPhone($phone, false);
    }

    private function formatPhone($phone, $strip1 = false)
    {
        // Strip non-digits
        $phone = preg_replace("/[^0-9]/", '', $phone);

        if ($strip1) {
            // Strip leading '1' if 11 digits
            if (strlen($phone) == 11 && substr($phone, 0, 1) == '1') {
                $phone = substr($phone, 1);
            }
        } else {
            // Add leading '1' if 10 digits
            if (strlen($phone) == 10) {
                $phone = '1' . $phone;
            }
        }

        return $phone;
    }
}
