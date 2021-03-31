<?php

namespace App\Services;

use App\Includes\ChaseDataDidApi;
use App\Models\AreaCode;
use App\Traits\PhoneTraits;

class DidSwapService
{
    use PhoneTraits;

    private $chaseDataDidApi;
    public $error;

    /**
     * 
     * @return void 
     */
    public function __construct()
    {
        $this->chaseDataDidApi = new ChaseDataDidApi();
        $this->error = null;
    }

    /**
     * 
     * @param mixed $phone 
     * @param mixed $dialer_numb 
     * @param mixed $group_id 
     * @return array 
     */
    public function swapNumber($phone, $dialer_numb, $group_id)
    {
        $this->error = null;

        // try to replace with same NPA
        $replaced_by = $this->chaseDataDidApi->swapCallerId($phone, $dialer_numb, $group_id);

        if ($replaced_by === false) {
            $this->error = $this->chaseDataDidApi->error;
            return false;
        }

        if (empty($replaced_by)) {

            // Find area code record
            $npa = substr($this->formatPhoneTenDigits($phone), 0, 3);
            $areaCode = AreaCode::find($npa);

            if ($areaCode) {
                // get list of nearby same state npas
                $alternates = $areaCode->alternateNpas();

                // loop through till swap succeeds or errors
                foreach ($alternates as $alternate) {
                    $replaced_by = $this->chaseDataDidApi->swapCallerId($phone, $dialer_numb, $group_id, $alternate->npa);

                    if ($replaced_by === false) {
                        $this->error = $this->chaseDataDidApi->error;
                        return false;
                    }

                    if (!empty($replaced_by)) {
                        break;
                    }
                }
            }
        }

        return $replaced_by;
    }
}
