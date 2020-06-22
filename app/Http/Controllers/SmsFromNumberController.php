<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidSmsFromNumber;
use App\Models\SmsFromNumber;
use Illuminate\Http\Request;

class SmsFromNumberController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sms_from_numbers = SmsFromNumber::orderBy('group_id')
            ->orderBy('from_number')
            ->get();

        $page = [
            'menuitem' => 'playbook',
            'menu' => 'tools',
            'type' => 'other',
        ];

        $data = [
            'page' => $page,
            'jsfile' => [],
            'cssfile' => ['https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css'],
            'sms_from_numbers' => $sms_from_numbers,
        ];

        return view('tools.playbook.from_number.index')->with($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ValidSmsFromNumber $request)
    {
        $sms_from_number = new SmsFromNumber($request->all());
        $sms_from_number->save();

        return ['status' => 'success'];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ValidSmsFromNumber $request, $id)
    {
        $sms_from_number = SmsFromNumber::findOrFail($id);
        $sms_from_number->update($request->all());
        $sms_from_number->save();

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
        $sms_from_number = SmsFromNumber::findOrFail($id);
        $sms_from_number->delete();

        return ['status' => 'success'];
    }

    /**
     * Return a sms record by id
     * 
     * @param Request $request 
     * @return mixed 
     */
    public function getSmsFromNumber(Request $request)
    {
        return SmsFromNumber::findOrFail($request->id);
    }
}
