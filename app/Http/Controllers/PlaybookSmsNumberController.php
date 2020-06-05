<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidPlaybookSmsNumber;
use App\Models\SmsFromNumber;
use Illuminate\Http\Request;

class PlaybookSmsNumberController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $playbook_sms_numbers = SmsFromNumber::orderBy('group_id')
            ->orderBy('from_number')
            ->get();

        $page = [
            'menuitem' => 'tools',
            'type' => 'other',
        ];

        $data =
            [
                'page' => $page,
                'jsfile' => [],
                'cssfile' => ['https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css'],
                'playbook_sms_numbers' => $playbook_sms_numbers,
            ];

        return view('tools.playbook.from_number.index')->with($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ValidPlaybookSmsNumber $request)
    {
        $playbook_sms_number = new SmsFromNumber($request->all());
        $playbook_sms_number->save();

        return ['status' => 'success'];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ValidPlaybookSmsNumber $request, $id)
    {
        $playbook_sms_number = SmsFromNumber::findOrFail($id);
        $playbook_sms_number->update($request->all());
        $playbook_sms_number->save();

        return ['status' => 'success'];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $playbook_sms_number = SmsFromNumber::findOrFail($id);
        $playbook_sms_number->delete();

        return ['status' => 'success'];
    }

    /**
     * Return a sms record by id
     * 
     * @param Request $request 
     * @return mixed 
     */
    public function getPlaybookSmsNumber(Request $request)
    {
        return SmsFromNumber::findOrFail($request->id);
    }
}
