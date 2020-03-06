<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FeatureMessage;
use App\Models\ReadFeatureMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class FeatureMessageController extends Controller
{
	public function index()
	{
		$page['menuitem'] = 'notifications';
		$page['type'] = 'page';
		$data = [
			'page' => $page,
			'feature_messages' => Auth()->User()->getFeatureMessages()
		];
		return view('admin.notifications')->with($data);
	}

	public function readMessage(Request $request)
	{
		ReadFeatureMessage::firstOrCreate([
			'feature_message_id' => $request->id,
			'user_id' => Auth::user()->id,
		]);

		return $request->id;
	}

	public function createMessage(Request $request)
	{
		FeatureMessage::create($request->all());

		return redirect()->back();
	}

	public function publishMessage(Request $request)
	{
		$msg = FeatureMessage::findOrFail($request->id);

		$msg->active = $request->active;
		$msg->save();

		return ['publish_msg' => 1];
	}

	public function deleteMsg(Request $request)
	{
		FeatureMessage::findOrFail($request->id)->delete();

		return ['delete_msg' => 1];
	}
}
