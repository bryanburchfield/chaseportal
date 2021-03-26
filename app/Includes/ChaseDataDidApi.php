<?php

namespace App\Includes;

use App\Traits\PhoneTraits;
use Exception;
use GuzzleHttp\Client;

class ChaseDataDidApi
{
    use PhoneTraits;

    private $client;
    private $url;
    private $token;
    public $error;

    public function __construct()
    {
        $this->client = new Client();
        $this->url = 'https://billing.chasedatacorp.com/DID.aspx';
        $this->token = config('chasedata.did_token');
        $this->error = null;
    }

    public function addCallerId($dialer_numb, $group_id, $phone, $campaign)
    {
        echo "Adding CallerId $phone\n";

        $this->error = null;

        try {
            $response = $this->client->get(
                $this->url,
                [
                    'query' => [
                        'Token' => $this->token,
                        'Server' => 'dialer-' . sprintf('%02d', $dialer_numb, 2),
                        'Action' => 'callerid-add',
                        'Number' => $this->formatPhoneTenDigits($phone),
                        'GroupId' => $group_id,
                        'CallerIdCampaign' => $campaign,
                        'SpamCheck' => 1,
                        'DeterSpam' => 1
                    ]
                ]
            );
        } catch (Exception $e) {
            $this->error = 'Add CallerId API failed: ' . $e->getMessage();
        }

        if (empty($this->error)) {
            try {
                $body = json_decode($response->getBody()->getContents());

                if (!empty($body->Error)) {
                    $this->error = $body->Error;
                }
            } catch (Exception $e) {
                $this->error = 'Could not add CallerId: ' . $e->getMessage();
            }
        }

        if (!empty($this->error)) {
            return false;
        }

        return true;
    }

    public function deleteCallerId($dialer_numb, $id)
    {
        echo "Deleting CallerId $id\n";

        $this->error = null;

        try {
            $response = $this->client->get(
                $this->url,
                [
                    'query' => [
                        'Token' => $this->token,
                        'Server' => 'dialer-' . sprintf('%02d', $dialer_numb, 2),
                        'Action' => 'callerid-delete',
                        'CallerIdIdentifier' => $id
                    ]
                ]
            );
        } catch (Exception $e) {
            $this->error = 'Delete CallerId API failed: ' . $e->getMessage();
        }

        if (empty($this->error)) {
            try {
                $body = json_decode($response->getBody()->getContents());

                if (!empty($body->Error)) {
                    $this->error = $body->Error;
                }
            } catch (Exception $e) {
                $this->error = 'Could not delete CallerId: ' . $e->getMessage();
            }
        }

        if (!empty($this->error)) {
            return false;
        }

        return true;
    }

    public function swapCallerId($phone, $dialer_numb, $group_id, $npa = null)
    {
        echo "Swapping $phone $npa\n";

        $this->error = null;
        $replaced_by = null;

        try {
            $response = $this->client->get(
                $this->url,
                [
                    'query' => [
                        'Token' => $this->token,
                        'Server' => 'dialer-' . sprintf('%02d', $dialer_numb, 2),
                        'Action' => 'swap',
                        'Number' => $this->formatPhoneTenDigits($phone),
                        'GroupId' => $group_id,
                        'NPA' => $npa
                    ]
                ]
            );
        } catch (Exception $e) {
            $this->error = 'Swap API failed: ' . $e->getMessage();
        }

        if (empty($this->error)) {
            try {
                $body = json_decode($response->getBody()->getContents());

                if (isset($body->NewDID)) {
                    if (!empty($body->NewDID)) {
                        $replaced_by = $this->formatPhoneElevenDigits($body->NewDID);
                    } else {
                        $this->error = 'No replacement available';
                    }
                }
                if (!empty($body->Error)) {
                    $this->error = $body->Error;
                }
            } catch (Exception $e) {
                $this->error = 'Could not swap number: ' . $e->getMessage();
            }
        }

        if (!empty($this->error)) {
            return false;
        }

        return $replaced_by;
    }
}
