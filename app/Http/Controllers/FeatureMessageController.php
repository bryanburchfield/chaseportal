<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\ReadFeatureMessage;
use Auth;

class FeatureMessageController extends Controller
{
    public function index()
    {
    	$data= [
    		'feature_messages' => Auth()->User()->getFeatureMessages()
    	];
    	return view('shared.notifications_bar')->with($data);
    }

    public function readMessage(Request $request)
    {

    	ReadFeatureMessage::create([
    		'feature_message_id'=> $request->id,
    		'user_id' => Auth::user()->id,
    		'created_at' => now(),
    	]);

    	return $request->id;
    }
}
