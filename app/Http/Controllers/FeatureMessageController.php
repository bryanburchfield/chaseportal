<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FeatureMessage;
use App\Models\ReadFeatureMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class FeatureMessageController extends Controller
{
	public function index($feature_message_id = null)
	{
		// id will be passed in if editing
		if ($feature_message_id !== null) {
			$feature_message = FeatureMessage::findOrFail($feature_message_id);
		} else {
			$feature_message = new FeatureMessage();
		}

		$page['menuitem'] = 'notifications';
		$page['type'] = 'page';
		$data = [
			'page' => $page,
			'feature_messages' => Auth()->User()->getFeatureMessages(),
			'feature_message' => $feature_message,
		];

		return view('admin.notifications')->with($data);
	}

	public function viewMessage(Request $request)
	{
		// if(not read){
		// 	$this->readMessage($request->id);
		// }
		
		$feature_message = FeatureMessage::findOrFail($request->id);
		$page['menuitem'] = 'notifications';
		$page['type'] = 'page';
		$data = [
			'page' => $page,
			'feature_messages' => Auth()->User()->getFeatureMessages(),
			'feature_message' => $feature_message,
		];
		return view('dashboards.message')->with($data);
	}

	public function readMessage(Request $request)
	{
		ReadFeatureMessage::firstOrCreate([
			'feature_message_id' => $request->id,
			'user_id' => Auth::user()->id,
		]);

		return $request->id;
	}

	public function editMessage(Request $request)
	{
		return $this->index($request->id);
	}

	public function saveMessage(Request $request)
	{
		// if 'active' checkbox wasn't checked, there will be no var for it
		if ($request->missing('active')) {
			$request->merge(['active' => 0]);
		}

		// If id is empty, we're adding a new record
		if (empty($request->id)) {
			$feature_message = FeatureMessage::create($request->all());
			$feature_message->expires_at = Carbon::parse('+1 year');
		} else {
			$feature_message = FeatureMessage::findOrFail($request->id);
			$feature_message->fill($request->all());
		}

		$feature_message->save();

		return redirect()->action('FeatureMessageController@index');
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

	public function toggleNotifications(Request $request)
	{
		Auth()->User()->toggleUserNotifications();

		return redirect()->back();
	}
}
