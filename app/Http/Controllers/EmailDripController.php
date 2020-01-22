<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidSmtpServer;
use App\Models\SmtpServer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Swift_Mailer;
use Swift_SmtpTransport;
use Swift_TransportException;

class EmailDripController extends Controller
{
    /**
     * Email Drip Campaign index
     * 
     * @return Illuminate\View\View|Illuminate\Contracts\View\Factory 
     */
    public function index()
    {
        $page = [
            'menuitem' => 'tools',
            'type' => 'other',
        ];

        $data = [
            'page' => $page,
            'group_id' => Auth::user()->group_id,
            'smtp_servers' => $this->getSmtpServers(),
        ];

        return view('tools.email_drip.index')->with($data);
    }

    public function addSmtpServer(ValidSmtpServer $request)
    {
        $smtp_server = new SmtpServer($request->all());
        $smtp_server->user_id = Auth::User()->id;
        $smtp_server->group_id = Auth::User()->group_id;

        $smtp_server->save();

        return ['status' => 'success'];
    }

    public function deleteSmtpServer($id)
    {
        $smtp_server = $this->getSmtpServer($id);

        // check for campaigns
        // ????????

        $smtp_server->delete();

        return ['status' => 'success'];
    }

    private function getSmtpServer($id)
    {
        return SmtpServer::where('id', $id)
            ->where('group_id', Auth::User()->group_id)
            ->firstOrFail();
    }

    /**
     * Servers configured for this group
     * 
     * @return mixed 
     */
    private function getSmtpServers()
    {
        return SmtpServer::where('group_id', Auth::User()->group_id)
            ->orderby('name')
            ->get();
    }

    public function testConnection(ValidSmtpServer $request)
    {
        // see if we can connect to server
        try {
            $transport = (new Swift_SmtpTransport($request->host, $request->port))
                ->setUsername($request->username)
                ->setPassword($request->password);

            $mailer = Swift_Mailer::newInstance($transport);
            $mailer->getTransport()->start();
            return 'ok';
        } catch (Swift_TransportException $e) {
            return $e->getMessage();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
