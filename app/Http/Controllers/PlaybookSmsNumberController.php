<?php

namespace App\Http\Controllers;

use App\Models\PlaybookSmsNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlaybookSmsNumberController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $playbook_sms_numbers = PlaybookSmsNumber::orderBy('group_id')
            ->orderBy('from_number')
            ->get();

        $data = ['playbook_sms_numbers' => $playbook_sms_numbers];

        return $this->returnView('index', $data);
    }

    private function returnView($view, $data = [])
    {
        $page = [
            'menuitem' => 'tools',
            'type' => 'other',
        ];

        $data = $data +
            [
                'page' => $page,
                'jsfile' => [],
                'cssfile' => ['https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css'],
            ];

        return view('tools.playbook.from_number.' . $view)->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return $this->returnView('create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'group_id' => 'required',
            'from_number' => 'required',
        ]);

        $playbook_sms_number = new PlaybookSmsNumber([
            'group_id' =>  $request->get('group_id'),
            'from_number' => $request->get('from_number'),
        ]);
        $playbook_sms_number->save();

        return redirect()->action('PlaybookSmsNumberController@index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $playbook_sms_number = PlaybookSmsNumber::findOrFail($id);

        $data = ['playbook_sms_number' => $playbook_sms_number];

        return $this->returnView('edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'group_id' => 'required',
            'from_number' => 'required',
        ]);

        $playbook_sms_number = PlaybookSmsNumber::findOrFail($id);
        $playbook_sms_number->group_id =  $request->get('group_id');
        $playbook_sms_number->from_number = $request->get('from_number');
        $playbook_sms_number->save();

        return redirect()->action('PlaybookSmsNumberController@index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $playbook_sms_number = PlaybookSmsNumber::findOrFail($id);
        $playbook_sms_number->delete();

        return redirect()->action('PlaybookSmsNumberController@index');
    }
}
