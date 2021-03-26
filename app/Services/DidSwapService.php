<?php

namespace App\Services;

use App\Includes\ChaseDataDidApi;
use App\Models\AreaCode;
use App\Traits\PhoneTraits;

class DidSwapService
{
    use PhoneTraits;

    private $chaseDataDidApi;

    public function __construct()
    {
        $this->chaseDataDidApi = new ChaseDataDidApi();
    }

    public function swapNumber($phone, $dialer_numb, $group_id)
    {
        // try to replace with same NPA
        $replaced_by = $this->chaseDataDidApi->swapCallerId($phone, $dialer_numb, $group_id);

        if (empty($replaced_by)) {

            // Find area code record
            $npa = substr($this->formatPhoneTenDigits($phone), 0, 3);
            $areaCode = AreaCode::find($npa);

            if ($areaCode) {
                // get list of nearby same state npas
                $alternates = $areaCode->alternateNpas();

                // loop through till swap succeeds or errors
                foreach ($alternates as $alternate) {
                    list($replaced_by, $swap_error) = $this->chaseDataDidApi->swapCallerId($phone, $dialer_numb, $group_id, $alternate->npa);
                    if (!empty($replaced_by)) {
                        break;
                    }
                }
            }
        }

        // truncate error just in case
        $error = substr($this->chaseDataDidApi->error, 0, 190);

        return [$replaced_by, $error];
    }
}
